<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

class UserService
{
    public function __construct(
        private User $userModel,
        private Role $roleModel,
        private BackupService $backupService,
        private SecurityLogService $securityLogService
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

    public function createUser(array $payload, string $createdBy): string
    {
        $data = $this->validatePayload($payload, true);
        $data['motdepasse'] = password_hash($payload['motdepasse'], PASSWORD_DEFAULT);
        $data['created_by'] = $createdBy;
        $data['updated_by'] = $createdBy;

        $matricule = $this->userModel->create($data);
        $this->backupService->queue('utilisateur', $matricule, 'create', $data);
        $this->securityLogService->log($createdBy, 'users.create', "Creation de l'utilisateur {$data['email']}");

        return $matricule;
    }

    public function updateUser(string $matricule, array $payload, string $updatedBy): bool
    {
        $existingUser = $this->userModel->findByMatricule($matricule);
        if (!$existingUser) {
            throw new \InvalidArgumentException('Utilisateur introuvable.');
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

    public function softDeleteUser(string $matricule, string $updatedBy): bool
    {
        $deleted = $this->userModel->softDelete($matricule, $updatedBy);

        if ($deleted) {
            $this->backupService->queue('utilisateur', $matricule, 'soft_delete', ['updated_by' => $updatedBy]);
            $this->securityLogService->log($updatedBy, 'users.delete', "Suppression logique de l'utilisateur {$matricule}");
        }

        return $deleted;
    }

    private function validatePayload(array $payload, bool $isCreate, ?string $currentMatricule = null): array
    {
        $requiredFields = ['matricule', 'nom', 'prenom', 'date_de_naissance', 'email', 'role_id'];
        if ($isCreate) {
            $requiredFields[] = 'motdepasse';
        }

        foreach ($requiredFields as $field) {
            if (empty($payload[$field])) {
                throw new \InvalidArgumentException("Le champ {$field} est obligatoire.");
            }
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Adresse email invalide.');
        }

        $matricule = strtoupper(trim($payload['matricule']));
        $email = strtolower(trim($payload['email']));
        $roleId = (int) $payload['role_id'];
        $statut = isset($payload['statut']) ? (int) (bool) $payload['statut'] : 1;

        if (!preg_match('/^(AD|GE|ES|ET)-[0-9A-Z]{3,}$/', $matricule)) {
            throw new \InvalidArgumentException('Matricule invalide. Exemple attendu: AD-0001');
        }

        if ($this->userModel->emailExists($email, $currentMatricule)) {
            throw new \InvalidArgumentException('Cet email existe deja.');
        }

        if ($isCreate && $this->userModel->matriculeExists($matricule)) {
            throw new \InvalidArgumentException('Ce matricule existe deja.');
        }

        if (!$this->roleModel->findById($roleId)) {
            throw new \InvalidArgumentException('Role invalide.');
        }

        if ($isCreate || !empty($payload['motdepasse'])) {
            $this->validatePassword((string) ($payload['motdepasse'] ?? ''));
        }

        return [
            'matricule' => $matricule,
            'nom' => trim($payload['nom']),
            'prenom' => trim($payload['prenom']),
            'date_de_naissance' => $payload['date_de_naissance'],
            'email' => $email,
            'role_id' => $roleId,
            'statut' => $statut,
        ];
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
}
