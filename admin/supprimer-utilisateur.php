<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

$mat = $_GET["mat"] ?? "";

if (empty($mat)) {
    header("Location: utilisateurs.php");
    exit;
}

if ($mat === $_SESSION["user"]["MAT"]) {
    header("Location: utilisateurs.php?error=self_delete");
    exit;
}

$stmt = $pdo->prepare("
    SELECT MAT, nom, prenom, email
    FROM utilisateur
    WHERE MAT = ?
    AND deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: utilisateurs.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtDelete = $pdo->prepare("
        UPDATE utilisateur
        SET 
            statut = 0,
            deleted_at = NOW(),
            deactivated_at = NOW(),
            deactivated_by = ?,
            deactivation_reason = ?,
            deletion_requested = 1,
            deletion_requested_at = NOW(),
            deletion_requested_by = ?
        WHERE MAT = ?
    ");

    $stmtDelete->execute([
        $_SESSION["user"]["MAT"],
        "Compte désactivé par l'administrateur",
        $_SESSION["user"]["MAT"],
        $mat
    ]);

    $stmtLog = $pdo->prepare("
        INSERT INTO security_logs (mat_user, action, description)
        VALUES (?, 'SOFT_DELETE_USER', ?)
    ");

    $stmtLog->execute([
        $_SESSION["user"]["MAT"],
        "Suppression logique de l'utilisateur " . $mat
    ]);

    $payload = json_encode([
        "MAT" => $user["MAT"],
        "nom" => $user["nom"],
        "prenom" => $user["prenom"],
        "email" => $user["email"],
        "deleted_at" => date("Y-m-d H:i:s"),
        "deleted_by" => $_SESSION["user"]["MAT"]
    ], JSON_UNESCAPED_UNICODE);

    $stmtBackup = $pdo->prepare("
        INSERT INTO backup_jobs (entity_type, entity_id, action, payload, scheduled_for)
        VALUES ('utilisateur', ?, 'soft_delete', ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
    ");

    $stmtBackup->execute([$mat, $payload]);

    $pdo->commit();

    header("Location: utilisateurs.php?success=deleted");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: utilisateurs.php?error=delete_failed");
    exit;
}