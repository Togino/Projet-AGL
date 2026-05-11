<?php
require_once __DIR__ . '/../../app/includes/auth.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/helpers/reclamations.php';

require_auth(['ETUDIANT']);
$etudiantNavPrefix = '../';
$navPrefix = '../';
ensure_reclamations_schema($pdo);

$mat = $_SESSION["user"]["MAT"];
$stmt = $pdo->prepare("
    SELECT *
    FROM reclamations
    WHERE MAT_etudiant = ?
    ORDER BY created_at DESC
");
$stmt->execute([$mat]);
$reclamations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes reclamations - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-etudiant.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="dashboard-head">
        <div>
            <h1>Mes reclamations</h1>
            <p>Suivez le statut de vos demandes envoyees aux gestionnaires.</p>
        </div>
        <a href="nouvelle.php" class="year-btn">Nouvelle reclamation</a>
    </div>

    <?php if (isset($_GET["success"]) && $_GET["success"] === "created"): ?>
        <div class="alert-success">Reclamation envoyee avec succes.</div>
    <?php endif; ?>

    <div class="students-table-card">
        <div class="table-responsive">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Reponse</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reclamations) === 0): ?>
                        <tr><td colspan="6">Aucune reclamation envoyee.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($reclamations as $index => $reclamation): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($reclamation["sujet"]) ?></strong></td>
                            <td><?= nl2br(htmlspecialchars($reclamation["message"])) ?></td>
                            <td><span class="<?= htmlspecialchars(reclamation_status_class($reclamation["statut"])) ?>"><?= htmlspecialchars(reclamation_status_label($reclamation["statut"])) ?></span></td>
                            <td><?= $reclamation["reponse"] ? nl2br(htmlspecialchars($reclamation["reponse"])) : "-" ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($reclamation["created_at"])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
</main>

<script>lucide.createIcons();</script>
</body>
</html>
