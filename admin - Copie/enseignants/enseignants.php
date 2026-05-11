<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$enseignants = $pdo->query("
    SELECT
        e.MAT,
        e.specialisation,
        u.nom,
        u.prenom,
        u.email,
        u.telephone,
        u.statut,
        u.created_at,
        COUNT(DISTINCT a.module_id) AS total_modules,
        COUNT(DISTINCT a.classe_id) AS total_classes
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    LEFT JOIN enseignement_affectation a ON a.MAT_enseignant = e.MAT
    WHERE u.deleted_at IS NULL
    GROUP BY e.MAT, e.specialisation, u.nom, u.prenom, u.email, u.telephone, u.statut, u.created_at
    ORDER BY u.prenom ASC, u.nom ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enseignants - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Enseignants</h1>
                <p>Consulter et modifier les profils enseignants.</p>
            </div>

            <a href="../utilisateurs/ajouter-utilisateur.php?role=ENSEIGNANT" class="year-btn">+ Nouvel enseignant</a>
        </div>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "created"): ?>
            <div class="alert-success">Enseignant cree avec succes.</div>
        <?php endif; ?>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "updated"): ?>
            <div class="alert-success">Enseignant modifie avec succes.</div>
        <?php endif; ?>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "deleted"): ?>
            <div class="alert-success">Enseignant desactive avec succes.</div>
        <?php endif; ?>
        <?php if (isset($_GET["success"]) && $_GET["success"] === "pending_approval"): ?>
            <div class="alert-success">Demande envoyee au centre d'attente pour validation.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "not_found"): ?>
            <div class="alert-error">Enseignant introuvable.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] === "delete_failed"): ?>
            <div class="alert-error">Erreur lors de la desactivation de l'enseignant.</div>
        <?php endif; ?>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Specialisation</th>
                        <th>Modules</th>
                        <th>Classes</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($enseignants) === 0): ?>
                        <tr>
                            <td colspan="9">Aucun enseignant enregistre.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($enseignants as $enseignant): ?>
                        <tr>
                            <td><?= htmlspecialchars($enseignant["MAT"]) ?></td>
                            <td><?= htmlspecialchars($enseignant["prenom"] . " " . $enseignant["nom"]) ?></td>
                            <td><?= htmlspecialchars($enseignant["email"]) ?></td>
                            <td><?= htmlspecialchars($enseignant["telephone"] ?: "-") ?></td>
                            <td><?= htmlspecialchars($enseignant["specialisation"] ?: "Non renseignee") ?></td>
                            <td><?= htmlspecialchars($enseignant["total_modules"]) ?></td>
                            <td><?= htmlspecialchars($enseignant["total_classes"]) ?></td>
                            <td>
                                <?php if ($enseignant["statut"]): ?>
                                    <span class="status active">Actif</span>
                                <?php else: ?>
                                    <span class="status inactive">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="modifier-enseignant.php?mat=<?= urlencode($enseignant["MAT"]) ?>" class="edit-btn">Modifier</a>
                                <a href="../affectations/affectations.php" class="edit-btn">Affectations</a>
                                <a href="supprimer-enseignant.php?mat=<?= urlencode($enseignant["MAT"]) ?>" class="delete-btn" onclick="return confirm('Voulez-vous vraiment desactiver cet enseignant ?')">Desactiver</a>
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
