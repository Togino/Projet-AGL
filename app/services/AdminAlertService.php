<?php

namespace App\Services;

use App\Models\AdminAlert;

class AdminAlertService
{
    public function __construct(private AdminAlert $adminAlertModel)
    {
    }

    public function create(string $type, string $severity, string $title, string $message, ?string $targetMatricule = null, ?string $createdBy = null): void
    {
        $this->adminAlertModel->create([
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'target_mat_user' => $targetMatricule,
            'created_by' => $createdBy,
            'is_read' => 0,
        ]);
    }

    public function listRecent(int $limit = 50): array
    {
        return $this->adminAlertModel->listRecent($limit);
    }
}
