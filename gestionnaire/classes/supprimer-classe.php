<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
ensure_sensitive_action_tables($pdo);

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: classes.php");
    exit;
}

// Verifier si la classe existe
$stmt = $pdo->prepare("SELECT * FROM classe WHERE ID=?");
$stmt->execute([$id]);
$classe = $stmt->fetch();

if (!$classe) {
    header("Location: classes.php");
    exit;
}

// Verifier si la classe a des etudiants
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM etudiant WHERE classe_id=?");
$stmt->execute([$id]);
$etudiants = $stmt->fetch();

if ($etudiants["count"] > 0) {
    // Rediriger avec un message d'erreur si la classe a des etudiants
    header("Location: classes.php?error=has_students");
    exit;
}

// Envoyer la suppression dans le centre d'attente
$actionId = enqueue_sensitive_action(
    $pdo,
    "DELETE_CLASSE",
    "classe",
    (string) $id,
    [
        "classe_id" => (int) $id,
        "nom" => $classe["nom"],
        "niveau" => $classe["niveau"],
    ],
    $_SESSION["user"]["MAT"],
    $_SESSION["user"]["role"]
);

notify_admin_and_gestionnaires(
    $pdo,
    "SENSITIVE_ACTION_PENDING",
    "high",
    "Validation requise: suppression de classe",
    "La demande #" . $actionId . " de suppression de la classe " . $classe["nom"] . " (" . $classe["niveau"] . ") attend vos confirmations.",
    $_SESSION["user"]["MAT"],
    $_SESSION["user"]["MAT"]
);

header("Location: classes.php?success=pending_approval&id=" . $actionId);
exit;
?>