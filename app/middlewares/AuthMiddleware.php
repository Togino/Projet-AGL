<?php

namespace App\Middlewares;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (empty($_SESSION['matricule'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Authentification requise.']);
            exit;
        }
    }
}
