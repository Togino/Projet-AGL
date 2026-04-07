<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;

class RoleService
{
    public function __construct(
        private Role $roleModel,
        private Permission $permissionModel,
        private SecurityLogService $securityLogService
    ) {}
    public function getAllRoles(): array
    {
        return $this->roleModel->findAll();
    }

    public function getAllPermissions(): array
    {
        return $this->permissionModel->findAll();
    }

    public function getPermissionsForUser(string $matricule): array
    {
        return $this->roleModel->getPermissionsForUser($matricule);
    }

    public function getRolePermissions(int $roleId): array
    {
        $role = $this->roleModel->findById($roleId);
        if (!$role) {
            throw new \InvalidArgumentException('Role introuvable.');
        }
        return [
            'role' => $role,
            'permissions' => $this->roleModel->getRolePermissions($roleId),
        ];
    }

    public function updateRolePermissions(int $roleId, array $permissionIds): array
    {
        $role = $this->roleModel->findById($roleId);
        if (!$role) {
            throw new \InvalidArgumentException('Role introuvable.');
        }

        $normalizedIds = array_values(array_unique(array_map('intval', $permissionIds)));
        $permissions = $this->permissionModel->findByIds($normalizedIds);

        if (count($permissions) !== count($normalizedIds)) {
            throw new \InvalidArgumentException('Une ou plusieurs permissions sont invalides.');
        }

        $this->roleModel->syncPermissions($roleId, $normalizedIds);
        $this->securityLogService->log($_SESSION['matricule'] ?? null, 'role.permissions.updated', "Permissions mises a jour pour le role {$role['name']}");

        return $this->getRolePermissions($roleId);
    }

    public function listRecentSecurityLogs(int $limit = 50): array
    {
        return $this->securityLogService->listRecent($limit);
    }
}
