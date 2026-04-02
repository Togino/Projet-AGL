<?php

namespace App\Services;

use App\Models\SecurityLog;

class SecurityLogService
{
    public function __construct(private SecurityLog $securityLogModel)
    {
    }

    public function log(?string $matricule, string $action, string $description): void
    {
        $this->securityLogModel->create(
            $matricule,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
    }

    public function listRecent(int $limit = 50): array
    {
        return $this->securityLogModel->listRecent($limit);
    }
}
