<?php

namespace App\Controllers;

use App\Services\AdminAlertService;
use App\Services\RoleService;

class AdminController
{
    public function __construct(private RoleService $roleService, private AdminAlertService $adminAlertService)
    {
    }

    public function roles(): void
    {
        $this->jsonResponse(['data' => $this->roleService->getAllRoles()]);
    }

    public function permissions(): void
    {
        $this->jsonResponse(['data' => $this->roleService->getAllPermissions()]);
    }

    public function rolePermissions(int $roleId): void
    {
        try {
            $this->jsonResponse(['data' => $this->roleService->getRolePermissions($roleId)]);
        } catch (\Throwable $exception) {
            $this->jsonResponse(['message' => $exception->getMessage()], 404);
        }
    }

    public function updateRolePermissions(int $roleId): void
    {
        try {
            $payload = $this->getInput();
            $permissionIds = $payload['permission_ids'] ?? [];
            $data = $this->roleService->updateRolePermissions($roleId, (array) $permissionIds);

            if (isset($_SESSION['matricule'])) {
                $_SESSION['permissions'] = $this->roleService->getPermissionsForUser($_SESSION['matricule']);
            }

            $this->jsonResponse(['message' => 'Permissions du role mises a jour.', 'data' => $data]);
        } catch (\Throwable $exception) {
            $this->jsonResponse(['message' => $exception->getMessage()], 422);
        }
    }

    public function securityLogs(): void
    {
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
        $this->jsonResponse(['data' => $this->roleService->listRecentSecurityLogs($limit)]);
    }

    public function alerts(): void
    {
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
        $this->jsonResponse(['data' => $this->adminAlertService->listRecent($limit)]);
    }

    public function classes(array $classes): void
    {
        $this->jsonResponse(['data' => $classes]);
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
