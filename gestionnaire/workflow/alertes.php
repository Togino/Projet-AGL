<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
$currentMat = $_SESSION["user"]["MAT"] ?? null;

$stmt = $pdo->prepare("
    SELECT id, type, severity, title, message, is_read, created_at
    FROM admin_alerts
    WHERE target_mat_user IS NULL OR target_mat_user = ?
    ORDER BY created_at DESC
");
$stmt->execute([$currentMat]);
$alerts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Alertes - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>
<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>
    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Alertes</h1>
                <p>Notifications des actions du systeme.</p>
            </div>
        </div>
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Severite</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($alerts) === 0): ?>
                    <tr><td colspan="5">Aucune alerte.</td></tr>
                <?php endif; ?>
                <?php foreach ($alerts as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a["title"]) ?></td>
                        <td><?= htmlspecialchars($a["type"]) ?></td>
                        <td><?= htmlspecialchars($a["severity"]) ?></td>
                        <td><?= htmlspecialchars($a["message"]) ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($a["created_at"])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
