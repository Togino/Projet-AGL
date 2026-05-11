<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$gestionnaires = $pdo->query("
    SELECT u.MAT, u.nom, u.prenom, u.email, u.telephone, u.statut, u.created_at, r.name AS role
    FROM utilisateur u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE u.deleted_at IS NULL
    AND r.name = 'GESTIONNAIRE'
    ORDER BY u.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestionnaires - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Gestion des gestionnaires</h1>
                <p>Créer, consulter, modifier et désactiver les comptes gestionnaires.</p>
            </div>

            <a href="ajouter-utilisateur.php?role=GESTIONNAIRE" class="year-btn">+ Nouveau gestionnaire</a>
        </div>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "deleted"): ?>
            <div class="alert-success">Gestionnaire désactivé avec succès.</div>
        <?php endif; ?>
        <?php if (isset($_GET["success"]) && $_GET["success"] === "pending_approval"): ?>
            <div class="alert-success">Demande envoyee au centre d'attente pour validation.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "self_delete"): ?>
            <div class="alert-error">Vous ne pouvez pas supprimer votre propre compte.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "delete_failed"): ?>
            <div class="alert-error">Erreur lors de la désactivation du gestionnaire.</div>
        <?php endif; ?>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Téléphone</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($gestionnaires) > 0): ?>
                        <?php foreach ($gestionnaires as $gestionnaire): ?>
                            <tr>
                                <td><?= htmlspecialchars($gestionnaire["MAT"]) ?></td>
                                <td><?= htmlspecialchars($gestionnaire["telephone"] ?: "-") ?></td>
                                <td><?= htmlspecialchars($gestionnaire["prenom"] . " " . $gestionnaire["nom"]) ?></td>
                                <td><?= htmlspecialchars($gestionnaire["email"]) ?></td>
                                <td>
                                    <span class="badge"><?= htmlspecialchars($gestionnaire["role"]) ?></span>
                                </td>
                                <td>
                                    <?php if ($gestionnaire["statut"]): ?>
                                        <span class="status active">Actif</span>
                                    <?php else: ?>
                                        <span class="status inactive">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date("d/m/Y", strtotime($gestionnaire["created_at"])) ?></td>
                                <td class="actions">
                                    <a href="ajouter-utilisateur.php?mat=<?= urlencode($gestionnaire["MAT"]) ?>" class="edit-btn">Modifier</a>
                                    <a href="supprimer-utilisateur.php?mat=<?= urlencode($gestionnaire["MAT"]) ?>" class="delete-btn" onclick="return confirm('Voulez-vous vraiment désactiver ce gestionnaire ?')">Désactiver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <h3>Aucun gestionnaire trouvé</h3>
                                    <p>Les comptes gestionnaires apparaîtront ici après leur création.</p>
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
