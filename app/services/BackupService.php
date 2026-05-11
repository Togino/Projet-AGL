<?php

namespace App\Services;

use PDO;

class BackupService
{
    public function __construct(private PDO $pdo, private array $appConfig)
    {
    }

    public function queue(string $entityType, string $entityId, string $action, array $payload): void
    {
        $sql = "INSERT INTO backup_jobs (entity_type, entity_id, action, payload, scheduled_for)
                VALUES (:entity_type, :entity_id, :action, :payload, DATE_ADD(NOW(), INTERVAL :delay DAY))";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':entity_type', $entityType);
        $statement->bindValue(':entity_id', $entityId);
        $statement->bindValue(':action', $action);
        $statement->bindValue(':payload', json_encode($payload, JSON_UNESCAPED_UNICODE));
        $statement->bindValue(':delay', (int) ($this->appConfig['backup_delay_days'] ?? 10), PDO::PARAM_INT);
        $statement->execute();
    }
}
