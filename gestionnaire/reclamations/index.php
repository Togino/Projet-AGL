<?php
require_once __DIR__ . '/../../app/includes/auth.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/helpers/reclamations.php';

require_auth(['GESTIONNAIRE']);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
ensure_reclamations_schema($pdo);

$matGestionnaire = $_SESSION["user"]["MAT"];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int) ($_POST["id"] ?? 0);
    $action = $_POST["action"] ?? "";
    $reponse = trim($_POST["reponse"] ?? "");
    $statut = $action === "approve" ? "APPROUVEE" : ($action === "reject" ? "REJETEE" : "");

    if ($id <= 0 || $statut === "") {
        $error = "Action invalide.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE reclamations
            SET statut = ?, reponse = ?, traite_par = ?
            WHERE id = ?
        ");
        $stmt->execute([$statut, $reponse ?: null, $matGestionnaire, $id]);

        header("Location: index.php?success=updated");
        exit;
    }
}

$stmt = $pdo->query("
    SELECT r.*, u.nom, u.prenom, c.nom AS classe_nom, c.niveau
    FROM reclamations r
    INNER JOIN utilisateur u ON u.MAT = r.MAT_etudiant
    LEFT JOIN etudiant e ON e.MAT = r.MAT_etudiant
    LEFT JOIN classe c ON c.ID = e.classe_id
    ORDER BY FIELD(r.statut, 'EN_ATTENTE','APPROUVEE','REJETEE'), r.created_at DESC
");
$reclamations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reclamations - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Reclamations</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Reclamations</strong>
            </div>
        </div>
    </div>

    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_GET["success"]) && $_GET["success"] === "updated"): ?><div class="alert-success">Reclamation mise a jour.</div><?php endif; ?>

    <div class="students-table-card">
        <div class="table-responsive">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Etudiant</th>
                        <th>Classe</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reclamations) === 0): ?>
                        <tr><td colspan="7">Aucune reclamation recue.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($reclamations as $index => $reclamation): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($reclamation["prenom"] . " " . $reclamation["nom"]) ?></strong><br><small><?= htmlspecialchars($reclamation["MAT_etudiant"]) ?></small></td>
                            <td><?= htmlspecialchars(trim(($reclamation["classe_nom"] ?? "-") . " " . ($reclamation["niveau"] ?? ""))) ?></td>
                            <td><?= htmlspecialchars($reclamation["sujet"]) ?></td>
                            <td><?= nl2br(htmlspecialchars($reclamation["message"])) ?></td>
                            <td><span class="<?= htmlspecialchars(reclamation_status_class($reclamation["statut"])) ?>"><?= htmlspecialchars(reclamation_status_label($reclamation["statut"])) ?></span></td>
                            <td>
                                <form method="POST" class="inline-decision-form">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($reclamation["id"]) ?>">
                                    <textarea name="reponse" rows="2" placeholder="Reponse optionnelle"><?= htmlspecialchars((string) ($reclamation["reponse"] ?? "")) ?></textarea>
                                    <div class="actions">
                                        <button type="submit" name="action" value="approve" class="edit-btn">Approuver</button>
                                        <button type="submit" name="action" value="reject" class="delete-btn">Rejeter</button>
                                    </div>
                                </form>
                            </td>
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
