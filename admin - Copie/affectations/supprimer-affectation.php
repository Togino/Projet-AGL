<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$mat = $_GET["mat"] ?? "";
$moduleId = $_GET["module_id"] ?? "";
$classeId = $_GET["classe_id"] ?? "";
$anneeScolaire = $_GET["annee_scolaire"] ?? "";

if (empty($mat) || empty($moduleId) || empty($classeId) || empty($anneeScolaire)) {
    header("Location: affectations.php?error=not_found");
    exit;
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM enseignement_affectation
        WHERE MAT_enseignant = ?
        AND module_id = ?
        AND classe_id = ?
        AND annee_scolaire = ?
    ");
    $stmt->execute([$mat, $moduleId, $classeId, $anneeScolaire]);

    if ($stmt->rowCount() === 0) {
        header("Location: affectations.php?error=not_found");
        exit;
    }

    header("Location: affectations.php?success=deleted");
    exit;
} catch (PDOException $e) {
    header("Location: affectations.php?error=delete_failed");
    exit;
}
