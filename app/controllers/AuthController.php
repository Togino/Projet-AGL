<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(): void
    {
        $payload = $this->getInput();

        try {
            $user = $this->authService->login($payload['email'] ?? '', $payload['motdepasse'] ?? ($payload['password'] ?? ''));
            
            // Déterminer l'URL de redirection selon le rôle
            $redirectUrl = $this->getRedirectUrlForRole($user['role_name']);
            
            $this->jsonResponse([
                'message' => 'Connexion reussie.', 
                'data' => $user,
                'redirect' => $redirectUrl
            ]);
        } catch (\Throwable $exception) {
            $this->jsonResponse(['message' => $exception->getMessage()], 401);
        }
    }

    private function getRedirectUrlForRole(string $role): string
    {
        return match($role) {
            'SUPER_ADMIN', 'ADMIN' => 'admin/dashboard.php',
            'GESTIONNAIRE' => 'gestionnaire/dashboard.php',
            'ETUDIANT' => 'etudiant/dashboard.php',
            'ENSEIGNANT' => 'enseignant/dashboard.php',
            default => 'public/login.php'
        };
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->jsonResponse(['message' => 'Deconnexion reussie.']);
    }

    public function me(): void
    {
        $user = $this->authService->currentUser();
        if (!$user) {
            $this->jsonResponse(['message' => 'Aucune session active.'], 401);
            return;
        }

        $this->jsonResponse(['data' => $user]);
    }

    private function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input') ?: '';
            return json_decode($raw, true) ?: [];
        }

        return $_POST;
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
