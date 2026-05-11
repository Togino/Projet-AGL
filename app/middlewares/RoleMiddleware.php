<?php

namespace App\Middlewares;

class RoleMiddleware
{
    public static function handle(string $permission): void
    {
        $permissions = $_SESSION['permissions'] ?? [];

        if (!in_array($permission, $permissions, true)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Permission insuffisante.']);
            exit;
        }
    }
}
