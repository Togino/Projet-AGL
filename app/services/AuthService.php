<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    public function __construct(
        private User $userModel,
        private RoleService $roleService,
        private SecurityLogService $securityLogService
    ) {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['motdepasse'])) {
            $this->securityLogService->log(null, 'login.failed', 'Tentative de connexion invalide');
            throw new \RuntimeException('Identifiants invalides.');
        }

        if (!(bool) $user['statut']) {
            throw new \RuntimeException('Ce compte est inactif.');
        }

        session_regenerate_id(true);

        $_SESSION['matricule'] = $user['MAT'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['permissions'] = $this->roleService->getPermissionsForUser($user['MAT']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->securityLogService->log($user['MAT'], 'login.success', 'Connexion reussie');

        return $this->sanitizeUser($user);
    }

    public function logout(): void
    {
        $matricule = $_SESSION['matricule'] ?? null;
        if ($matricule) {
            $this->securityLogService->log($matricule, 'logout', 'Deconnexion');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public function currentUser(): ?array
    {
        $matricule = $_SESSION['matricule'] ?? null;
        if (!$matricule) {
            return null;
        }

        $user = $this->userModel->findByMatricule($matricule);
        if (!$user) {
            return null;
        }

        $data = $this->sanitizeUser($user);
        $data['permissions'] = $_SESSION['permissions'] ?? [];
        $data['csrf_token'] = $_SESSION['csrf_token'] ?? null;

        return $data;
    }

    private function sanitizeUser(array $user): array
    {
        return [
            'matricule' => $user['MAT'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'statut' => (bool) $user['statut'],
            'role_name' => $user['role_name'],
            'permissions' => $_SESSION['permissions'] ?? [],
            'csrf_token' => $_SESSION['csrf_token'] ?? null,
        ];
    }
}
