<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

$modules = $pdo->query("
    SELECT
        m.ID,
        m.nom,
        COUNT(DISTINCT CONCAT(a.classe_id, ':', a.annee_scolaire)) AS total_affectations,
        COUNT(DISTINCT a.MAT_enseignant) AS total_enseignants,
        COUNT(DISTINCT n.ID) AS total_notes
    FROM module m
    LEFT JOIN enseignement_affectation a ON a.module_id = m.ID
    LEFT JOIN note n ON n.module_id = m.ID
    GROUP BY m.ID, m.nom
    ORDER BY m.nom ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modules - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Gestion des modules</h1>
                <p>Les modules sont affectes aux classes avec un seul professeur par classe et par annee.</p>
            </div>

            <a href="ajouter-module.php" class="year-btn">+ Nouveau module</a>
        </div>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "created"): ?>
            <div class="alert-success">Module cree avec succes.</div>
        <?php endif; ?>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "deleted"): ?>
            <div class="alert-success">Module supprime avec succes.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "not_found"): ?>
            <div class="alert-error">Module introuvable.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "in_use"): ?>
            <div class="alert-error">Impossible de supprimer ce module, des notes ou affectations y sont rattachees.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "delete_failed"): ?>
            <div class="alert-error">Erreur lors de la suppression du module.</div>
        <?php endif; ?>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du module</th>
                        <th>Affectations</th>
                        <th>Professeurs</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($modules) === 0): ?>
                        <tr>
                            <td colspan="6">Aucun module enregistre.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td><?= htmlspecialchars($module["ID"]) ?></td>
                            <td><?= htmlspecialchars($module["nom"]) ?></td>
                            <td><?= htmlspecialchars($module["total_affectations"]) ?></td>
                            <td><?= htmlspecialchars($module["total_enseignants"]) ?></td>
                            <td><?= htmlspecialchars($module["total_notes"]) ?></td>
                            <td class="actions">
                                <a href="affectations.php?module_id=<?= $module["ID"] ?>" class="edit-btn">Affectations</a>
                                <a href="supprimer-module.php?id=<?= $module["ID"] ?>" class="delete-btn" onclick="return confirm('Supprimer ce module ?')">Supprimer</a>
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
