<?php

namespace App\Models;

use PDO;

class Permission
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        return $this->pdo->query('SELECT id, code, description FROM permissions ORDER BY code')->fetchAll();
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $statement = $this->pdo->prepare(
            "SELECT id, code, description FROM permissions WHERE id IN ({$placeholders}) ORDER BY code"
        );
        $statement->execute(array_values($ids));

        return $statement->fetchAll();
    }
}
