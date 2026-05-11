<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

$adminNavPrefix = '../';
$navPrefix = '../';
require_auth(["SUPER_ADMIN", "ADMIN"]);
ensure_sensitive_action_tables($pdo);

$id = $_GET["id"] ?? "";
if (empty($id) || !ctype_digit((string) $id)) {
    header("Location: classes.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("SELECT ID, nom, niveau FROM classe WHERE ID = ? LIMIT 1");
$stmt->execute([$id]);
$classe = $stmt->fetch();
if (!$classe) {
    header("Location: classes.php?error=not_found");
    exit;
}

try {
    $actionId = enqueue_sensitive_action(
        $pdo,
        "DELETE_CLASSE",
        "classe",
        (string) $id,
        ["classe_id" => (int) $id, "nom" => $classe["nom"], "niveau" => $classe["niveau"]],
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["role"]
    );

    notify_admin_and_gestionnaires(
        $pdo,
        "SENSITIVE_ACTION_PENDING",
        "high",
        "Validation requise: suppression classe",
        "La demande #" . $actionId . " pour supprimer la classe " . $classe["nom"] . " attend des confirmations.",
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["MAT"]
    );

    header("Location: classes.php?success=pending_approval");
    exit;
} catch (Throwable $e) {
    header("Location: classes.php?error=delete_failed");
    exit;
}