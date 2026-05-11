<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = "../";
$navPrefix = "../";

$stmt = $pdo->query("
    SELECT
        a.MAT_enseignant,
        a.module_id,
        a.classe_id,
        a.annee_scolaire,
        m.nom AS module_nom,
        c.nom AS classe_nom,
        c.niveau AS classe_niveau,
        u.nom AS enseignant_nom,
        u.prenom AS enseignant_prenom
    FROM enseignement_affectation a
    INNER JOIN module m ON m.ID = a.module_id
    INNER JOIN classe c ON c.ID = a.classe_id
    INNER JOIN enseignant e ON e.MAT = a.MAT_enseignant
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    ORDER BY a.annee_scolaire DESC, c.nom ASC, m.nom ASC
");
$affectations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Affectations - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="dashboard-head">
        <div>
            <h1>Affectations</h1>
            <p>Gerez les professeurs affectes aux modules des classes.</p>
        </div>
        <a href="ajouter.php" class="year-btn">+ Nouvelle affectation</a>
    </div>

    <?php if (isset($_GET["success"]) && $_GET["success"] === "created"): ?><div class="alert-success">Affectation creee avec succes.</div><?php endif; ?>
    <?php if (isset($_GET["success"]) && $_GET["success"] === "updated"): ?><div class="alert-success">Affectation modifiee avec succes.</div><?php endif; ?>
    <?php if (isset($_GET["success"]) && $_GET["success"] === "deleted"): ?><div class="alert-success">Affectation supprimee avec succes.</div><?php endif; ?>
    <?php if (isset($_GET["error"]) && $_GET["error"] === "not_found"): ?><div class="alert-error">Affectation introuvable.</div><?php endif; ?>
    <?php if (isset($_GET["error"]) && $_GET["error"] === "save_failed"): ?><div class="alert-error">Impossible d'enregistrer cette affectation.</div><?php endif; ?>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Professeur</th>
                    <th>Module</th>
                    <th>Classe</th>
                    <th>Annee scolaire</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($affectations) === 0): ?>
                    <tr><td colspan="5">Aucune affectation enregistree.</td></tr>
                <?php endif; ?>

                <?php foreach ($affectations as $affectation): ?>
                    <tr>
                        <td><?= htmlspecialchars($affectation["enseignant_prenom"] . " " . $affectation["enseignant_nom"]) ?></td>
                        <td><?= htmlspecialchars($affectation["module_nom"]) ?></td>
                        <td><?= htmlspecialchars($affectation["classe_nom"] . " - " . $affectation["classe_niveau"]) ?></td>
                        <td><span class="badge"><?= htmlspecialchars($affectation["annee_scolaire"]) ?></span></td>
                        <td class="actions">
                            <a class="edit-btn" href="modifier.php?mat=<?= urlencode($affectation["MAT_enseignant"]) ?>&module_id=<?= urlencode($affectation["module_id"]) ?>&classe_id=<?= urlencode($affectation["classe_id"]) ?>&annee_scolaire=<?= urlencode($affectation["annee_scolaire"]) ?>">Modifier</a>
                            <a class="delete-btn" href="supprimer.php?mat=<?= urlencode($affectation["MAT_enseignant"]) ?>&module_id=<?= urlencode($affectation["module_id"]) ?>&classe_id=<?= urlencode($affectation["classe_id"]) ?>&annee_scolaire=<?= urlencode($affectation["annee_scolaire"]) ?>" onclick="return confirm('Supprimer cette affectation ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
</main>
</body>
</html>
