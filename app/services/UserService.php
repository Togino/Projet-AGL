<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Role;
use App\Models\User;

class UserService
{
    public function __construct(
        private User $userModel,
        private Role $roleModel,
        private ClassRoom $classRoomModel,
        private BackupService $backupService,
        private SecurityLogService $securityLogService,
        private AdminAlertService $adminAlertService
    ) {
    }

    public function listUsers(): array
    {
        return $this->userModel->listAll();
    }

    public function getUser(string $matricule): ?array
    {
        return $this->userModel->findByMatricule($matricule);
    }

    public function createUser(array $payload, string $createdBy): array
    {
        $plainPassword = trim((string) ($payload['motdepasse'] ?? ''));
        $generatedPassword = false;

        if ($plainPassword === '') {
            $plainPassword = $this->generateDefaultPassword();
            $payload['motdepasse'] = $plainPassword;
            $generatedPassword = true;
        }

        $data = $this->validatePayload($payload, true);
        $data['motdepasse'] = password_hash($plainPassword, PASSWORD_DEFAULT);
        $data['created_by'] = $createdBy;
        $data['updated_by'] = $createdBy;

        $matricule = $this->userModel->create($data);
        $this->backupService->queue('utilisateur', $matricule, 'create', $data);
        $this->securityLogService->log($createdBy, 'users.create', "Creation de l'utilisateur {$data['email']}");

        return [
            'matricule' => $matricule,
            'motdepasse' => $plainPassword,
            'generated_password' => $generatedPassword,
        ];
    }

    public function previewNextMatricule(int $roleId): string
    {
        $role = $this->roleModel->findById($roleId);
        if (!$role) {
            throw new \InvalidArgumentException('Role invalide.');
        }

        return $this->generateUniqueMatriculeForRole($role);
    }

    public function listClasses(): array
    {
        return $this->classRoomModel->findAll();
    }

    public function updateUser(string $matricule, array $payload, string $updatedBy): bool
    {
        $existingUser = $this->userModel->findByMatricule($matricule);
        if (!$existingUser) {
            throw new \InvalidArgumentException('Utilisateur introuvable.');
        }

        if (!(bool) $existingUser['statut']) {
            throw new \InvalidArgumentException('Ce compte est desactive. Reactivation requise avant modification.');
        }

        $data = $this->validatePayload($payload, false, $matricule);
        $data['updated_by'] = $updatedBy;

        if (!empty($payload['motdepasse'])) {
            $data['motdepasse'] = password_hash($payload['motdepasse'], PASSWORD_DEFAULT);
        }

        $updated = $this->userModel->update($matricule, $data);

        if ($updated) {
            $this->backupService->queue('utilisateur', $matricule, 'update', $data);
            $this->securityLogService->log($updatedBy, 'users.update', "Modification de l'utilisateur {$matricule}");
        }

        return $updated;
    }

    public function softDeleteUser(string $matricule, string $updatedBy, array $payload = []): bool
    {
        $existingUser = $this->userModel->findByMatricule($matricule);
        if (!$existingUser) {
            return false;
        }

        if (!(bool) $existingUser['statut']) {
            throw new \InvalidArgumentException('Ce compte est deja desactive.');
        }

        $reason = trim((string) ($payload['motif'] ?? ''));
        $deletionRequested = (bool) ($payload['demande_suppression'] ?? false);

        if ($reason === '') {
            throw new \InvalidArgumentException('Le motif de desactivation est obligatoire.');
        }

        $deleted = $this->userModel->softDelete($matricule, $updatedBy, [
            'deactivation_reason' => $reason,
            'deletion_requested' => $deletionRequested,
        ]);

        if ($deleted) {
            $backupPayload = [
                'updated_by' => $updatedBy,
                'motif' => $reason,
                'demande_suppression' => $deletionRequested,
            ];
            $this->backupService->queue('utilisateur', $matricule, 'soft_delete', $backupPayload);
            $this->securityLogService->log($updatedBy, 'users.delete', "Desactivation de l'utilisateur {$matricule} pour le motif: {$reason}");
            $this->adminAlertService->create(
                'account.deactivated',
                $deletionRequested ? 'high' : 'medium',
                $deletionRequested ? 'Demande de suppression en attente' : 'Compte desactive',
                $deletionRequested
                    ? "Le compte {$matricule} a ete desactive avec une demande de suppression. Motif: {$reason}"
                    : "Le compte {$matricule} a ete desactive. Motif: {$reason}",
                $matricule,
                $updatedBy
            );
        }

        return $deleted;
    }

    private function validatePayload(array $payload, bool $isCreate, ?string $currentMatricule = null): array
    {
        $requiredFields = ['nom', 'prenom', 'date_de_naissance', 'email', 'role_id'];
        if ($isCreate) {
            $requiredFields[] = 'motdepasse';
        } else {
            $requiredFields[] = 'matricule';
        }

        foreach ($requiredFields as $field) {
            if (empty($payload[$field])) {
                throw new \InvalidArgumentException("Le champ {$field} est obligatoire.");
            }
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Adresse email invalide.');
        }

        $email = strtolower(trim($payload['email']));
        $roleId = (int) $payload['role_id'];
        $statut = isset($payload['statut']) ? (int) (bool) $payload['statut'] : 1;

        if ($this->userModel->emailExists($email, $currentMatricule)) {
            throw new \InvalidArgumentException('Cet email existe deja.');
        }

        $role = $this->roleModel->findById($roleId);
        if (!$role) {
            throw new \InvalidArgumentException('Role invalide.');
        }

        if ($isCreate) {
            $matricule = $this->generateUniqueMatriculeForRole($role);
        } else {
            $matricule = strtoupper(trim((string) $payload['matricule']));

            if (!preg_match('/^(AD|GE|ES|ET)-[0-9A-Z]{3,}$/', $matricule)) {
                throw new \InvalidArgumentException('Matricule invalide. Exemple attendu: AD-0001');
            }
        }

        if ($isCreate || !empty($payload['motdepasse'])) {
            $this->validatePassword((string) ($payload['motdepasse'] ?? ''));
        }

        $studentProfile = null;
        if (strtoupper($role['name'] ?? '') === 'ETUDIANT') {
            $classeNom = trim((string) ($payload['classe_nom'] ?? ''));
            $niveau = trim((string) ($payload['niveau'] ?? ''));

            if ($classeNom === '' || $niveau === '') {
                throw new \InvalidArgumentException('Les champs classe et niveau sont obligatoires pour un etudiant.');
            }

            $classRoom = $this->classRoomModel->findOrCreateByNameAndLevel($classeNom, $niveau);

            $studentProfile = [
                'classe_id' => (int) $classRoom['ID'],
                'annee_etude' => (string) date('Y'),
            ];
        }

        return [
            'matricule' => $matricule,
            'nom' => trim($payload['nom']),
            'prenom' => trim($payload['prenom']),
            'date_de_naissance' => $payload['date_de_naissance'],
            'email' => $email,
            'role_id' => $roleId,
            'statut' => $statut,
            'student_profile' => $studentProfile,
        ];
    }

    private function generateUniqueMatriculeForRole(array $role): string
    {
        $prefix = $this->resolveMatriculePrefix($role['name'] ?? '');
        $matricule = $this->userModel->getNextMatriculeForPrefix($prefix);

        if (!$this->userModel->matriculeExists($matricule, null)) {
            return $matricule;
        }

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $matricule = $this->userModel->getNextMatriculeForPrefix($prefix);
            if (!$this->userModel->matriculeExists($matricule, null)) {
                return $matricule;
            }
        }

        throw new \RuntimeException('Impossible de generer un matricule unique pour ce role.');
    }

    private function resolveMatriculePrefix(string $roleName): string
    {
        return match (strtoupper($roleName)) {
            'SUPER_ADMIN', 'ADMIN' => 'AD',
            'GESTIONNAIRE' => 'GE',
            'ENSEIGNANT' => 'ES',
            'ETUDIANT' => 'ET',
            default => throw new \InvalidArgumentException('Aucun prefixe de matricule defini pour ce role.'),
        };
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 8 caracteres.');
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir une majuscule, une minuscule et un chiffre.');
        }
    }

    private function generateDefaultPassword(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $symbols = '!@#$%';

        $password = [
            $letters[random_int(0, strlen($letters) - 1)],
            strtoupper($letters[random_int(0, strlen($letters) - 1)]),
            strtolower($letters[random_int(0, strlen($letters) - 1)]),
            $digits[random_int(0, strlen($digits) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        $pool = $letters . $digits . $symbols;
        for ($i = count($password); $i < 12; $i++) {
            $password[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }
}
