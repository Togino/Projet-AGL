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
    $actionId = enqueue_sensitive_action(
        $pdo,
        "DISABLE_TEACHER",
        "enseignant",
        $mat,
        ["mat" => $mat],
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["role"]
    );

    notify_admin_and_gestionnaires(
        $pdo,
        "SENSITIVE_ACTION_PENDING",
        "high",
        "Validation requise: desactivation enseignant",
        "La demande #" . $actionId . " pour desactiver l'enseignant " . $mat . " attend des confirmations.",
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["MAT"]
    );

    header("Location: enseignants.php?success=pending_approval");
    exit;
} catch (Throwable $e) {
    header("Location: enseignants.php?error=delete_failed");
    exit;
}
