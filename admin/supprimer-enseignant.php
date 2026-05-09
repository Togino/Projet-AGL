<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

$mat = $_GET["mat"] ?? "";

if (empty($mat)) {
    header("Location: enseignants.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.MAT
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE e.MAT = ?
    AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);

if (!$stmt->fetch()) {
    header("Location: enseignants.php?error=not_found");
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
        "Compte enseignant desactive par l'administrateur",
        $_SESSION["user"]["MAT"],
        $mat
    ]);

    $stmtLog = $pdo->prepare("
        INSERT INTO security_logs (mat_user, action, description)
        VALUES (?, 'DISABLE_TEACHER', ?)
    ");
    $stmtLog->execute([
        $_SESSION["user"]["MAT"],
        "Desactivation de l'enseignant " . $mat
    ]);

    $pdo->commit();

    header("Location: enseignants.php?success=deleted");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: enseignants.php?error=delete_failed");
    exit;
}
