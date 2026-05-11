<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

require_auth(["ENSEIGNANT"]);

$mat = $_SESSION["user"]["MAT"];

$stmtClasses = $pdo->prepare("
    SELECT DISTINCT
        c.ID,
        c.nom,
        c.niveau
    FROM enseignement_affectation a
    INNER JOIN classe c ON c.ID = a.classe_id
    WHERE a.MAT_enseignant = ?
    ORDER BY c.niveau ASC, c.nom ASC
");
$stmtClasses->execute([$mat]);
$classes = $stmtClasses->fetchAll();
$totalClasses = count($classes);

$stmtStudents = $pdo->prepare("
    SELECT COUNT(DISTINCT e.MAT)
    FROM etudiant e
    INNER JOIN enseignement_affectation a ON a.classe_id = e.classe_id
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE a.MAT_enseignant = ?
    AND u.deleted_at IS NULL
");
$stmtStudents->execute([$mat]);
$totalStudents = (int) $stmtStudents->fetchColumn();

$stmtNotes = $pdo->prepare("
    SELECT COUNT(*)
    FROM note n
    INNER JOIN enseignement_affectation a ON a.module_id = n.module_id
    INNER JOIN etudiant e ON e.MAT = n.MAT_ET AND e.classe_id = a.classe_id
    WHERE a.MAT_enseignant = ?
");
$stmtNotes->execute([$mat]);
$totalNotes = (int) $stmtNotes->fetchColumn();

$stmtModules = $pdo->prepare("
    SELECT COUNT(DISTINCT module_id)
    FROM enseignement_affectation
    WHERE MAT_enseignant = ?
");
$stmtModules->execute([$mat]);
$totalModules = (int) $stmtModules->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Enseignant - EduSystème</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../app/includes/sidebar-enseignant.php"; ?>

<main class="main-content">
<?php include "../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="teacher-hero">
        <div>
            <h1>Bonjour <?= htmlspecialchars($_SESSION["user"]["prenom"]) ?></h1>
            <p>Bienvenue dans votre espace enseignant. Consultez vos classes, vos etudiants et les notes associees a vos modules.</p>
        </div>

        <div class="teacher-hero-badge">
            <i data-lucide="graduation-cap"></i>
        </div>
    </div>

    <div class="teacher-stats-grid">
        <div class="teacher-stat-card green">
            <div><i data-lucide="school"></i></div>
            <article>
                <h2><?= $totalClasses ?></h2>
                <p>Classes assignees</p>
            </article>
        </div>

        <div class="teacher-stat-card blue">
            <div><i data-lucide="users"></i></div>
            <article>
                <h2><?= $totalStudents ?></h2>
                <p>Etudiants</p>
            </article>
        </div>

        <div class="teacher-stat-card orange">
            <div><i data-lucide="file-pen-line"></i></div>
            <article>
                <h2><?= $totalNotes ?></h2>
                <p>Notes enregistrees</p>
            </article>
        </div>

        <div class="teacher-stat-card red">
            <div><i data-lucide="book-open"></i></div>
            <article>
                <h2><?= $totalModules ?></h2>
                <p>Modules affectes</p>
            </article>
        </div>
    </div>

    <div class="teacher-dashboard-layout">
        <div class="teacher-main-card">
            <div class="teacher-card-head">
                <div>
                    <h2>Mes classes</h2>
                    <p>Classes dans lesquelles vous enseignez.</p>
                </div>
            </div>

            <div class="teacher-classes-grid">
                <?php if (count($classes) > 0): ?>
                    <?php foreach ($classes as $classe): ?>
                        <a href="academique/classes.php?id=<?= urlencode($classe["ID"]) ?>" class="teacher-class-card">
                            <div class="teacher-class-icon">
                                <i data-lucide="school"></i>
                            </div>

                            <h3><?= htmlspecialchars($classe["nom"]) ?></h3>
                            <p><?= htmlspecialchars($classe["niveau"]) ?></p>

                            <span>
                                Voir les etudiants
                                <i data-lucide="arrow-right"></i>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i data-lucide="school"></i>
                        <h3>Aucune classe assignee</h3>
                        <p>Aucune affectation enseignant n'est enregistree pour votre compte.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="teacher-side">
            <div class="teacher-side-card">
                <h3>Actions rapides</h3>

                <a href="academique/classes.php">
                    <i data-lucide="school"></i>
                    Voir mes classes
                </a>

                <a href="academique/notes.php">
                    <i data-lucide="file-pen-line"></i>
                    Voir les notes
                </a>
            </div>

            <div class="teacher-side-card">
                <h3>Informations</h3>

                <div class="teacher-info-row">
                    <span>Role</span>
                    <strong>Enseignant</strong>
                </div>

                <div class="teacher-info-row">
                    <span>Classes</span>
                    <strong><?= $totalClasses ?></strong>
                </div>

                <div class="teacher-info-row">
                    <span>Etudiants</span>
                    <strong><?= $totalStudents ?></strong>
                </div>
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
