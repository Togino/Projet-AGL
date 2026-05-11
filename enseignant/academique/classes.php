<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["ENSEIGNANT"]);
$enseignantNavPrefix = '../';
$navPrefix = '../';

$mat = $_SESSION["user"]["MAT"];
$classeId = $_GET["id"] ?? "";

$classesStmt = $pdo->prepare("
    SELECT DISTINCT c.ID, c.nom, c.niveau
    FROM enseignement_affectation a
    INNER JOIN classe c ON c.ID = a.classe_id
    WHERE a.MAT_enseignant = ?
    ORDER BY c.niveau ASC, c.nom ASC
");
$classesStmt->execute([$mat]);
$classes = $classesStmt->fetchAll();

$selectedClasse = null;
$students = [];

if ($classeId !== "") {
    $classeStmt = $pdo->prepare("
        SELECT DISTINCT c.ID, c.nom, c.niveau
        FROM enseignement_affectation a
        INNER JOIN classe c ON c.ID = a.classe_id
        WHERE a.MAT_enseignant = ? AND c.ID = ?
        LIMIT 1
    ");
    $classeStmt->execute([$mat, $classeId]);
    $selectedClasse = $classeStmt->fetch();

    if ($selectedClasse) {
        $studentsStmt = $pdo->prepare("
            SELECT e.MAT, u.nom, u.prenom, u.email, e.annee_etude
            FROM etudiant e
            INNER JOIN utilisateur u ON u.MAT = e.MAT
            WHERE e.classe_id = ?
            AND u.deleted_at IS NULL
            ORDER BY u.prenom ASC, u.nom ASC
        ");
        $studentsStmt->execute([$classeId]);
        $students = $studentsStmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes classes - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-enseignant.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Mes classes</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Mes classes</strong>
            </div>
        </div>
    </div>

    <div class="classes-card">
        <div class="classes-card-head">
            <div>
                <h2>Classes assignees</h2>
                <p>Liste des classes rattachees a vos affectations.</p>
            </div>
        </div>

        <div class="classes-table-wrap">
            <table class="classes-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Classe</th>
                        <th>Niveau</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($classes) === 0): ?>
                        <tr><td colspan="4">Aucune classe assignee.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($classes as $classe): ?>
                        <tr>
                            <td><?= "CLS-" . str_pad($classe["ID"], 3, "0", STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($classe["nom"]) ?></td>
                            <td><?= htmlspecialchars($classe["niveau"]) ?></td>
                            <td><a class="edit-btn" href="classes.php?id=<?= urlencode($classe["ID"]) ?>">Voir etudiants</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($selectedClasse): ?>
        <div class="students-table-card">
            <div class="classes-card-head">
                <div>
                    <h2>Etudiants - <?= htmlspecialchars($selectedClasse["nom"] . " " . $selectedClasse["niveau"]) ?></h2>
                    <p><?= count($students) ?> etudiant(s) dans cette classe.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Matricule</th>
                            <th>Etudiant</th>
                            <th>Email</th>
                            <th>Annee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) === 0): ?>
                            <tr><td colspan="5">Aucun etudiant dans cette classe.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student["MAT"]) ?></td>
                                <td><strong><?= htmlspecialchars($student["prenom"] . " " . $student["nom"]) ?></strong></td>
                                <td><?= htmlspecialchars($student["email"]) ?></td>
                                <td><?= htmlspecialchars($student["annee_etude"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
</main>

<script>
lucide.createIcons();
</script>
</body>
</html>
