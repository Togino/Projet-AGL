<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";
ensure_sensitive_action_tables($pdo);

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$id = $_GET["id"] ?? "";

if (empty($id) || !ctype_digit((string) $id)) {
    header("Location: modules.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("SELECT ID, nom FROM module WHERE ID = ? LIMIT 1");
$stmt->execute([$id]);
$module = $stmt->fetch();

if (!$module) {
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
    $actionId = enqueue_sensitive_action(
        $pdo,
        "DELETE_MODULE",
        "module",
        (string) $id,
        ["module_id" => (int) $id, "nom" => $module["nom"]],
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["role"]
    );

    notify_admin_and_gestionnaires(
        $pdo,
        "SENSITIVE_ACTION_PENDING",
        "high",
        "Validation requise: suppression module",
        "La demande #" . $actionId . " pour supprimer le module #" . $id . " attend des confirmations.",
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["MAT"]
    );

    header("Location: modules.php?success=pending_approval");
    exit;
} catch (Throwable $e) {
    header("Location: modules.php?error=delete_failed");
    exit;
}
