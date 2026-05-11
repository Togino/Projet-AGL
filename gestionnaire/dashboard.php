<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "GESTIONNAIRE") {
    header("Location: ../public/login.php");
    exit;
}

$totalEtudiants = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
$totalEnseignants = $pdo->query("SELECT COUNT(*) FROM enseignant")->fetchColumn();
$totalClasses = $pdo->query("SELECT COUNT(*) FROM classe")->fetchColumn();
$totalNotes = $pdo->query("SELECT COUNT(*) FROM note")->fetchColumn();

$recentEtudiants = $pdo->query("
    SELECT u.MAT, u.nom, u.prenom, u.email, c.nom AS classe_nom, c.niveau
    FROM etudiant e
    INNER JOIN utilisateur u ON e.MAT = u.MAT
    INNER JOIN classe c ON e.classe_id = c.ID
    WHERE u.deleted_at IS NULL
    ORDER BY u.created_at DESC
    LIMIT 5
")->fetchAll();

$recentNotes = $pdo->query("
    SELECT n.valeur, u.nom, u.prenom, m.nom AS module_nom
    FROM note n
    INNER JOIN etudiant e ON n.MAT_ET = e.MAT
    INNER JOIN utilisateur u ON e.MAT = u.MAT
    INNER JOIN module m ON n.module_id = m.ID
    ORDER BY n.ID DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gestionnaire - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
<?php include "../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="students-header">
        <div>
            <h1>Tableau de bord</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Espace gestionnaire</strong>
            </div>
        </div>
    </div>

    <div class="classes-stats-grid">
        <div class="class-stat-box green">
            <div><i data-lucide="users"></i></div>
            <article>
                <h2><?= $totalEtudiants ?></h2>
                <p>Étudiants</p>
                <small>Inscrits dans le système</small>
            </article>
        </div>

        <div class="class-stat-box blue">
            <div><i data-lucide="graduation-cap"></i></div>
            <article>
                <h2><?= $totalEnseignants ?></h2>
                <p>Enseignants</p>
                <small>Personnel pédagogique</small>
            </article>
        </div>

        <div class="class-stat-box orange">
            <div><i data-lucide="school"></i></div>
            <article>
                <h2><?= $totalClasses ?></h2>
                <p>Classes</p>
                <small>Classes disponibles</small>
            </article>
        </div>

        <div class="class-stat-box purple">
            <div><i data-lucide="file-pen-line"></i></div>
            <article>
                <h2><?= $totalNotes ?></h2>
                <p>Notes</p>
                <small>Notes enregistrées</small>
            </article>
        </div>
    </div>

    <div class="settings-layout">

        <div class="settings-main">

            <div class="settings-overview-card">
                <h2>Actions rapides</h2>
                <p>Accédez rapidement aux fonctionnalités principales du gestionnaire.</p>

                <div class="settings-grid">
                    <a href="etudiants/etudiants.php" class="settings-box green">
                        <div><i data-lucide="users"></i></div>
                        <h3>Gérer les étudiants</h3>
                        <p>Consulter, ajouter et modifier les étudiants.</p>
                        <span><i data-lucide="arrow-right"></i> Accéder</span>
                    </a>

                    <a href="enseignants/enseignants.php" class="settings-box blue">
                        <div><i data-lucide="graduation-cap"></i></div>
                        <h3>Gérer les enseignants</h3>
                        <p>Consulter les enseignants et leurs informations.</p>
                        <span><i data-lucide="arrow-right"></i> Accéder</span>
                    </a>

                    <a href="classes/classes.php" class="settings-box orange">
                        <div><i data-lucide="school"></i></div>
                        <h3>Gérer les classes</h3>
                        <p>Voir les classes et les effectifs.</p>
                        <span><i data-lucide="arrow-right"></i> Accéder</span>
                    </a>

                    <a href="pedagogie/notes.php" class="settings-box purple">
                        <div><i data-lucide="file-pen-line"></i></div>
                        <h3>Gérer les notes</h3>
                        <p>Ajouter, modifier et consulter les notes.</p>
                        <span><i data-lucide="arrow-right"></i> Accéder</span>
                    </a>
                </div>
            </div>

            <div class="activity-card">
                <h2>Étudiants récents</h2>

                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Étudiant</th>
                            <th>Email</th>
                            <th>Classe</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($recentEtudiants as $etu): ?>
                            <tr>
                                <td><?= htmlspecialchars($etu["MAT"]) ?></td>
                                <td><?= htmlspecialchars($etu["prenom"] . " " . $etu["nom"]) ?></td>
                                <td><?= htmlspecialchars($etu["email"]) ?></td>
                                <td><?= htmlspecialchars($etu["classe_nom"] . " - " . $etu["niveau"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <aside class="settings-side">

            <div class="system-card">
                <h3>Dernières notes</h3>

                <?php foreach ($recentNotes as $note): ?>
                    <div class="system-row">
                        <span><?= htmlspecialchars($note["prenom"] . " " . $note["nom"]) ?></span>
                        <strong><?= htmlspecialchars($note["valeur"]) ?>/20</strong>
                    </div>
                    <small><?= htmlspecialchars($note["module_nom"]) ?></small>
                <?php endforeach; ?>
            </div>

            <div class="help-settings-card">
                <h3>Rôle gestionnaire</h3>
                <p>
                    Le gestionnaire peut gérer les étudiants, les enseignants, les classes et les notes.
                </p>

                <button>
                    <i data-lucide="shield-check"></i>
                    Accès limité et sécurisé
                </button>
            </div>

        </aside>

    </div>

</section>

</main>

<script>
lucide.createIcons();
</script>

</body>
</html>
