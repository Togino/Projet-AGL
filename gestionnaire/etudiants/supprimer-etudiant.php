<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
ensure_sensitive_action_tables($pdo);

$mat = $_GET["mat"] ?? "";
if ($mat === "") {
    header("Location: etudiants.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.MAT, u.nom, u.prenom, u.email
    FROM utilisateur u
    INNER JOIN etudiant e ON e.MAT = u.MAT
    WHERE u.MAT = ? AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$etu = $stmt->fetch();

if (!$etu) {
    header("Location: etudiants.php?error=not_found");
    exit;
}

try {
    $actionId = enqueue_sensitive_action(
        $pdo,
        "SOFT_DELETE_USER",
        "etudiant",
        $mat,
        ["mat" => $mat, "nom" => $etu["nom"], "prenom" => $etu["prenom"], "email" => $etu["email"]],
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["role"]
    );

    notify_admin_and_gestionnaires(
        $pdo,
        "SENSITIVE_ACTION_PENDING",
        "high",
        "Validation requise: desactivation etudiant",
        "La demande #" . $actionId . " pour desactiver l'etudiant " . $mat . " attend des confirmations.",
        $_SESSION["user"]["MAT"],
        $_SESSION["user"]["MAT"]
    );

    header("Location: etudiants.php?success=pending_approval");
    exit;
} catch (Throwable $e) {
    header("Location: etudiants.php?error=delete_failed");
    exit;
}
