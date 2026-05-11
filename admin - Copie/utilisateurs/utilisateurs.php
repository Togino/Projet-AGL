<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$users = $pdo->query("
    SELECT u.MAT, u.nom, u.prenom, u.email, u.telephone, u.statut, u.created_at, r.name AS role
    FROM utilisateur u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE u.deleted_at IS NULL
    AND r.name IN ('SUPER_ADMIN', 'ADMIN')
    ORDER BY u.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administrateurs - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Gestion des administrateurs</h1>
                <p>Creer, consulter, modifier et desactiver les comptes administrateurs.</p>
            </div>

            <a href="ajouter-utilisateur.php?role=ADMIN" class="year-btn">+ Nouvel administrateur</a>
        </div>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "deleted"): ?>
            <div class="alert-success">Administrateur desactive avec succes.</div>
        <?php endif; ?>
        <?php if (isset($_GET["success"]) && $_GET["success"] === "pending_approval"): ?>
            <div class="alert-success">Demande envoyee au centre d'attente pour validation.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "self_delete"): ?>
            <div class="alert-error">Vous ne pouvez pas supprimer votre propre compte.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "delete_failed"): ?>
            <div class="alert-error">Erreur lors de la desactivation de l'administrateur.</div>
        <?php endif; ?>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Role</th>
                        <th>Statut</th>
                        <th>Date creation</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user["MAT"]) ?></td>
                                <td><?= htmlspecialchars($user["prenom"] . " " . $user["nom"]) ?></td>
                                <td><?= htmlspecialchars($user["email"]) ?></td>
                                <td><?= htmlspecialchars($user["telephone"] ?: "-") ?></td>
                                <td>
                                    <span class="badge"><?= htmlspecialchars($user["role"]) ?></span>
                                </td>
                                <td>
                                    <?php if ($user["statut"]): ?>
                                        <span class="status active">Actif</span>
                                    <?php else: ?>
                                        <span class="status inactive">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date("d/m/Y", strtotime($user["created_at"])) ?></td>
                                <td class="actions">
                                    <a href="ajouter-utilisateur.php?mat=<?= urlencode($user["MAT"]) ?>" class="edit-btn">Modifier</a>
                                    <a href="supprimer-utilisateur.php?mat=<?= $user["MAT"] ?>" class="delete-btn" onclick="return confirm('Voulez-vous vraiment desactiver cet administrateur ?')">Desactiver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <h3>Aucun administrateur trouve</h3>
                                    <p>Les comptes administrateurs apparaitront ici apres leur creation.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>
