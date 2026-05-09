<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

$enseignants = $pdo->query("
    SELECT e.MAT, u.nom, u.prenom
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL AND u.statut = 1
    ORDER BY u.prenom ASC, u.nom ASC
")->fetchAll();

$modules = $pdo->query("SELECT ID, nom FROM module ORDER BY nom ASC")->fetchAll();
$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();

$error = "";
$mat = "";
$moduleId = $_GET["module_id"] ?? "";
$classeId = "";
$anneeScolaire = date("Y") . "-" . ((int) date("Y") + 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mat = clean($_POST["MAT_enseignant"] ?? "");
    $moduleId = clean($_POST["module_id"] ?? "");
    $classeId = clean($_POST["classe_id"] ?? "");
    $anneeScolaire = clean($_POST["annee_scolaire"] ?? "");

    if (empty($mat) || empty($moduleId) || empty($classeId) || empty($anneeScolaire)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO enseignement_affectation (MAT_enseignant, module_id, classe_id, annee_scolaire)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$mat, $moduleId, $classeId, $anneeScolaire]);

            header("Location: affectations.php?success=created");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $error = "Cette classe a deja un professeur pour ce module sur cette annee scolaire.";
            } else {
                $error = "Erreur lors de la creation de l'affectation.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter affectation - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Nouvelle affectation</h1>
                <p>Affecter un professeur a un module pour une classe et une annee scolaire.</p>
            </div>

            <a href="affectations.php" class="year-btn">Retour</a>
        </div>

        <div class="form-card">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div>
                        <label>Professeur</label>
                        <select name="MAT_enseignant">
                            <option value="">Selectionner un professeur</option>
                            <?php foreach ($enseignants as $enseignant): ?>
                                <option value="<?= htmlspecialchars($enseignant["MAT"]) ?>" <?= $mat === $enseignant["MAT"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($enseignant["prenom"] . " " . $enseignant["nom"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Module</label>
                        <select name="module_id">
                            <option value="">Selectionner un module</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?= htmlspecialchars($module["ID"]) ?>" <?= (string) $moduleId === (string) $module["ID"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($module["nom"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Classe</label>
                        <select name="classe_id">
                            <option value="">Selectionner une classe</option>
                            <?php foreach ($classes as $classe): ?>
                                <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeId === (string) $classe["ID"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Annee scolaire</label>
                        <input type="text" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>" placeholder="Ex : 2025-2026">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Creer l'affectation</button>
            </form>
        </div>
    </section>
</main>

</body>
</html>
