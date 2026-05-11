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
    header("Location: utilisateurs.php");
    exit;
}

if ($mat === $_SESSION["user"]["MAT"]) {
    header("Location: utilisateurs.php?error=self_delete");
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.MAT, u.nom, u.prenom, u.email, r.name AS role_name
    FROM utilisateur u
    INNER JOIN roles r ON r.id = u.role_id
    WHERE u.MAT = ?
    AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: utilisateurs.php");
    exit;
}

$redirectPage = $user["role_name"] === "GESTIONNAIRE" ? "gestionnaires.php" : "utilisateurs.php";

try {
    $actionId = enqueue_sensitive_action(
        $pdo,
        "SOFT_DELETE_USER",
        "utilisateur",
        $mat,
        [
            "mat" => $mat,
            "nom" => $user["nom"],
            "prenom" => $user["prenom"],
            "email" => $user["email"]
        ],
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["role"]
    );

    notify_admin_and_gestionnaires(
        $pdo,
        "SENSITIVE_ACTION_PENDING",
        "high",
        "Validation requise: desactivation utilisateur",
        "La demande #" . $actionId . " pour desactiver " . $mat . " attend des confirmations.",
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["MAT"]
    );

    header("Location: " . $redirectPage . "?success=pending_approval");
    exit;
} catch (Throwable $e) {
    header("Location: " . $redirectPage . "?error=delete_failed");
    exit;
}