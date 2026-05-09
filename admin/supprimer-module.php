<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

$id = $_GET["id"] ?? "";

if (empty($id) || !ctype_digit((string) $id)) {
    header("Location: modules.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("SELECT ID FROM module WHERE ID = ? LIMIT 1");
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    header("Location: modules.php?error=not_found");
    exit;
}

$stmtUsage = $pdo->prepare("
    SELECT
        (SELECT COUNT(*) FROM note WHERE module_id = ?) AS total_notes,
        (SELECT COUNT(*) FROM enseignement_affectation WHERE module_id = ?) AS total_affectations
");
$stmtUsage->execute([$id, $id]);
$usage = $stmtUsage->fetch();

if ((int) $usage["total_notes"] > 0 || (int) $usage["total_affectations"] > 0) {
    header("Location: modules.php?error=in_use");
    exit;
}

try {
    $stmtDelete = $pdo->prepare("DELETE FROM module WHERE ID = ?");
    $stmtDelete->execute([$id]);

    header("Location: modules.php?success=deleted");
    exit;
} catch (PDOException $e) {
    header("Location: modules.php?error=delete_failed");
    exit;
}
