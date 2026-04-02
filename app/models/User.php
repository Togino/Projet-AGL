<?php

namespace App\Models;

use PDO;

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.*, r.name AS role_name
                FROM utilisateur u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.email = :email AND u.deleted_at IS NULL
                LIMIT 1";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByMatricule(string $matricule): ?array
    {
        $sql = "SELECT u.*, r.name AS role_name
                FROM utilisateur u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.MAT = :matricule AND u.deleted_at IS NULL
                LIMIT 1";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['matricule' => $matricule]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function listAll(): array
    {
        $sql = "SELECT u.MAT, u.nom, u.prenom, u.date_de_naissance, u.email, u.statut,
                       u.created_at, r.name AS role_name
                FROM utilisateur u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.deleted_at IS NULL
                ORDER BY u.created_at DESC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO utilisateur (
                    MAT, nom, prenom, date_de_naissance, email, motdepasse,
                    role_id, statut, created_by, updated_by
                ) VALUES (
                    :matricule, :nom, :prenom, :date_de_naissance, :email, :motdepasse,
                    :role_id, :statut, :created_by, :updated_by
                )";

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'matricule' => $data['matricule'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_de_naissance' => $data['date_de_naissance'],
            'email' => $data['email'],
            'motdepasse' => $data['motdepasse'],
            'role_id' => $data['role_id'],
            'statut' => $data['statut'],
            'created_by' => $data['created_by'],
            'updated_by' => $data['updated_by'],
        ]);

        return $data['matricule'];
    }

    public function update(string $matricule, array $data): bool
    {
        $fields = [
            'nom = :nom',
            'prenom = :prenom',
            'date_de_naissance = :date_de_naissance',
            'email = :email',
            'role_id = :role_id',
            'statut = :statut',
            'updated_by = :updated_by',
        ];

        if (!empty($data['motdepasse'])) {
            $fields[] = 'motdepasse = :motdepasse';
        }

        $sql = 'UPDATE utilisateur SET ' . implode(', ', $fields) . ' WHERE MAT = :matricule AND deleted_at IS NULL';
        $params = [
            'matricule' => $matricule,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_de_naissance' => $data['date_de_naissance'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'statut' => $data['statut'],
            'updated_by' => $data['updated_by'],
        ];

        if (!empty($data['motdepasse'])) {
            $params['motdepasse'] = $data['motdepasse'];
        }

        $statement = $this->pdo->prepare($sql);
        return $statement->execute($params);
    }

    public function softDelete(string $matricule, ?string $updatedBy): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE utilisateur
             SET statut = FALSE, deleted_at = NOW(), updated_by = :updated_by
             WHERE MAT = :matricule AND deleted_at IS NULL"
        );

        return $statement->execute([
            'matricule' => $matricule,
            'updated_by' => $updatedBy,
        ]);
    }

    public function emailExists(string $email, ?string $exceptMatricule = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM utilisateur WHERE email = :email AND deleted_at IS NULL';
        $params = ['email' => $email];

        if ($exceptMatricule !== null) {
            $sql .= ' AND MAT <> :matricule';
            $params['matricule'] = $exceptMatricule;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function matriculeExists(string $matricule, ?string $exceptMatricule = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM utilisateur WHERE MAT = :matricule AND deleted_at IS NULL';
        $params = ['matricule' => $matricule];

        if ($exceptMatricule !== null) {
            $sql .= ' AND MAT <> :except_matricule';
            $params['except_matricule'] = $exceptMatricule;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }
}
