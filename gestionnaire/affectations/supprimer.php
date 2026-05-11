<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

require_auth(["GESTIONNAIRE"]);

$mat = $_GET["mat"] ?? "";
$moduleId = $_GET["module_id"] ?? "";
$classeId = $_GET["classe_id"] ?? "";
$anneeScolaire = $_GET["annee_scolaire"] ?? "";

if ($mat === "" || $moduleId === "" || $classeId === "" || $anneeScolaire === "") {
    header("Location: index.php?error=not_found");
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
        header("Location: index.php?error=not_found");
        exit;
    }

    header("Location: index.php?success=deleted");
    exit;
} catch (PDOException $e) {
    header("Location: index.php?error=save_failed");
    exit;
}
