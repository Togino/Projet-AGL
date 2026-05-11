<?php

namespace App\Models;

use PDO;

class SecurityLog
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(?string $matricule, string $action, string $description, ?string $ipAddress, ?string $userAgent): void
    {
        $sql = "INSERT INTO security_logs (mat_user, action, description, ip_address, user_agent)
                VALUES (:mat_user, :action, :description, :ip_address, :user_agent)";

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'mat_user' => $matricule,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function listRecent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $sql = "SELECT sl.id, sl.mat_user, sl.action, sl.description, sl.ip_address, sl.user_agent, sl.created_at,
                       u.email, u.nom, u.prenom
                FROM security_logs sl
                LEFT JOIN utilisateur u ON u.MAT = sl.mat_user
                ORDER BY sl.created_at DESC, sl.id DESC
                LIMIT :limit";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
