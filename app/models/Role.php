<?php

namespace App\Models;

use PDO;

class Role
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        return $this->pdo->query('SELECT id, name, description FROM roles ORDER BY name')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, description FROM roles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $role = $statement->fetch();

        return $role ?: null;
    }

    public function getPermissionsForUser(string $matricule): array
    {
        $sql = "SELECT p.code
                FROM utilisateur u
                INNER JOIN role_permissions rp ON rp.role_id = u.role_id
                INNER JOIN permissions p ON p.id = rp.permission_id
                WHERE u.MAT = :matricule AND u.deleted_at IS NULL";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['matricule' => $matricule]);

        return array_column($statement->fetchAll(), 'code');
    }

    public function getRolePermissions(int $roleId): array
    {
        $sql = "SELECT p.id, p.code, p.description
                FROM role_permissions rp
                INNER JOIN permissions p ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id
                ORDER BY p.code";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['role_id' => $roleId]);

        return $statement->fetchAll();
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->pdo->beginTransaction();

        $deleteStatement = $this->pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
        $deleteStatement->execute(['role_id' => $roleId]);

        if ($permissionIds !== []) {
            $insertStatement = $this->pdo->prepare(
                'INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)'
            );

            foreach ($permissionIds as $permissionId) {
                $insertStatement->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        $this->pdo->commit();
    }
}
