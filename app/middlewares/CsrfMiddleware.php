<?php

namespace App\Middlewares;

class CsrfMiddleware
{
    public static function handle(string $headerName): void
    {
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $requestToken = $_SERVER[$headerName] ?? null;

        if (!$sessionToken || !$requestToken || !hash_equals($sessionToken, $requestToken)) {
            http_response_code(419);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Jeton CSRF invalide ou manquant.']);
            exit;
        }
    }
}
