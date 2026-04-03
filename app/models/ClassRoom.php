<?php

namespace App\Models;

use PDO;

class ClassRoom
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        return $this->pdo->query('SELECT ID, nom, niveau FROM classe ORDER BY niveau, nom')->fetchAll();
    }

    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM classe WHERE ID = :id');
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function findByNameAndLevel(string $name, string $level): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT ID, nom, niveau FROM classe WHERE UPPER(nom) = :nom AND UPPER(niveau) = :niveau LIMIT 1'
        );
        $statement->execute([
            'nom' => strtoupper($name),
            'niveau' => strtoupper($level),
        ]);

        $classRoom = $statement->fetch();
        return $classRoom ?: null;
    }

    public function findOrCreateByNameAndLevel(string $name, string $level): array
    {
        $existing = $this->findByNameAndLevel($name, $level);
        if ($existing) {
            return $existing;
        }

        $statement = $this->pdo->prepare('INSERT INTO classe (nom, niveau) VALUES (:nom, :niveau)');
        $statement->execute([
            'nom' => strtoupper(trim($name)),
            'niveau' => strtoupper(trim($level)),
        ]);

        return [
            'ID' => (int) $this->pdo->lastInsertId(),
            'nom' => strtoupper(trim($name)),
            'niveau' => strtoupper(trim($level)),
        ];
    }
}
