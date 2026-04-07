<?php

namespace App\Models;

use PDO;

class AdminAlert
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_alerts (
                type, severity, title, message, target_mat_user, created_by, is_read
            ) VALUES (
                :type, :severity, :title, :message, :target_mat_user, :created_by, :is_read
            )'
        );

        $statement->execute([
            'type' => $data['type'],
            'severity' => $data['severity'],
            'title' => $data['title'],
            'message' => $data['message'],
            'target_mat_user' => $data['target_mat_user'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'is_read' => (int) ($data['is_read'] ?? 0),
        ]);
    }

    public function listRecent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $statement = $this->pdo->prepare(
            "SELECT aa.id, aa.type, aa.severity, aa.title, aa.message, aa.target_mat_user, aa.created_by, aa.is_read, aa.created_at,
                    target.nom AS target_nom, target.prenom AS target_prenom,
                    creator.nom AS creator_nom, creator.prenom AS creator_prenom
             FROM admin_alerts aa
             LEFT JOIN utilisateur target ON target.MAT = aa.target_mat_user
             LEFT JOIN utilisateur creator ON creator.MAT = aa.created_by
             ORDER BY aa.created_at DESC, aa.id DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
