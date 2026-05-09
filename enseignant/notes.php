<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

require_auth(["ENSEIGNANT"]);

$mat = $_SESSION["user"]["MAT"];

$stmt = $pdo->prepare("
    SELECT
        n.valeur,
        n.poids,
        n.penalite,
        n.MAT_ET,
        u.nom,
        u.prenom,
        m.nom AS module_nom,
        c.nom AS classe_nom,
        c.niveau
    FROM note n
    INNER JOIN etudiant e ON e.MAT = n.MAT_ET
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    INNER JOIN module m ON m.ID = n.module_id
    INNER JOIN enseignement_affectation a ON a.module_id = n.module_id AND a.classe_id = e.classe_id
    INNER JOIN classe c ON c.ID = e.classe_id
    WHERE a.MAT_enseignant = ?
    AND u.deleted_at IS NULL
    ORDER BY u.prenom ASC, u.nom ASC, m.nom ASC
");
$stmt->execute([$mat]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../app/includes/sidebar-enseignant.php"; ?>

<main class="main-content">
<?php include "../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Notes</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Notes</strong>
            </div>
        </div>
    </div>

    <div class="students-table-card">
        <div class="table-responsive">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Matricule</th>
                        <th>Etudiant</th>
                        <th>Classe</th>
                        <th>Module</th>
                        <th>Note</th>
                        <th>Poids</th>
                        <th>Penalite</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($notes) === 0): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i data-lucide="chart-no-axes-column"></i>
                                    <h3>Aucune note trouvee</h3>
                                    <p>Aucune note n'est rattachee a vos affectations.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($notes as $index => $note): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($note["MAT_ET"]) ?></td>
                            <td><strong><?= htmlspecialchars($note["prenom"] . " " . $note["nom"]) ?></strong></td>
                            <td><?= htmlspecialchars($note["classe_nom"] . " - " . $note["niveau"]) ?></td>
                            <td><?= htmlspecialchars($note["module_nom"]) ?></td>
                            <td><span class="status active"><?= htmlspecialchars($note["valeur"]) ?>/20</span></td>
                            <td><?= $note["poids"] !== null ? htmlspecialchars($note["poids"]) . "%" : "-" ?></td>
                            <td><?= $note["penalite"] ? "Oui" : "Non" ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
</main>

<script>
lucide.createIcons();
</script>
</body>
</html>
