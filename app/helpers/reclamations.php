<?php

function ensure_reclamations_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reclamations (
            id INT NOT NULL AUTO_INCREMENT,
            MAT_etudiant VARCHAR(10) NOT NULL,
            sujet VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            statut ENUM('EN_ATTENTE','APPROUVEE','REJETEE') NOT NULL DEFAULT 'EN_ATTENTE',
            reponse TEXT NULL,
            traite_par VARCHAR(10) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_reclamations_etudiant (MAT_etudiant),
            KEY idx_reclamations_statut (statut)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function reclamation_status_label(string $statut): string
{
    $labels = [
        "EN_ATTENTE" => "En attente",
        "APPROUVEE" => "Approuvee",
        "REJETEE" => "Rejetee",
    ];

    return $labels[$statut] ?? $statut;
}

function reclamation_status_class(string $statut): string
{
    if ($statut === "APPROUVEE") {
        return "status active";
    }

    if ($statut === "REJETEE") {
        return "status inactive";
    }

    return "badge";
}
