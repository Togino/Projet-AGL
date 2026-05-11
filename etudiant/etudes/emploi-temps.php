<?php
require_once __DIR__ . "/../../app/includes/auth.php";
require_once __DIR__ . "/../../app/config/database.php";
require_once __DIR__ . "/../../app/helpers/functions.php";

require_auth(["ETUDIANT"]);
$etudiantNavPrefix = '../';
$navPrefix = '../';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS emploi_temps (
        id INT NOT NULL AUTO_INCREMENT,
        classe_id INT NOT NULL,
        module_id INT NOT NULL,
        MAT_enseignant VARCHAR(10) NULL,
        jour_semaine ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
        heure_debut TIME NOT NULL,
        heure_fin TIME NOT NULL,
        salle VARCHAR(80) NULL,
        annee_scolaire VARCHAR(20) NOT NULL,
        created_by VARCHAR(10) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY fk_edt_classe (classe_id),
        KEY fk_edt_module (module_id),
        KEY fk_edt_enseignant (MAT_enseignant)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$mat = $_SESSION["user"]["MAT"];

$studentStmt = $pdo->prepare("
    SELECT e.classe_id, c.nom AS classe_nom, c.niveau
    FROM etudiant e
    LEFT JOIN classe c ON c.ID = e.classe_id
    WHERE e.MAT = ?
    LIMIT 1
");
$studentStmt->execute([$mat]);
$student = $studentStmt->fetch();

$anneeScolaire = $_GET["annee_scolaire"] ?? (date("Y") . "-" . ((int) date("Y") + 1));
$creneaux = [];
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
$creneauxByDay = array_fill_keys($jours, []);

if ($student) {
    $stmt = $pdo->prepare("
        SELECT et.*, m.nom AS module_nom, u.nom AS enseignant_nom, u.prenom AS enseignant_prenom
        FROM emploi_temps et
        INNER JOIN module m ON m.ID = et.module_id
        LEFT JOIN utilisateur u ON u.MAT = et.MAT_enseignant
        WHERE et.classe_id = ?
        AND et.annee_scolaire = ?
        ORDER BY FIELD(et.jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), et.heure_debut ASC
    ");
    $stmt->execute([$student["classe_id"], $anneeScolaire]);
    $creneaux = $stmt->fetchAll();

    foreach ($creneaux as $creneau) {
        if (isset($creneauxByDay[$creneau["jour_semaine"]])) {
            $creneauxByDay[$creneau["jour_semaine"]][] = $creneau;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-etudiant.php"; ?>
<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Emploi du temps</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong><?= $student ? htmlspecialchars($student["classe_nom"] . " - " . $student["niveau"]) : "Classe" ?></strong>
            </div>
        </div>
    </div>

    <div class="students-table-card">
        <div class="students-filters">
            <form method="GET" class="search-form">
                <input type="text" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>" placeholder="Annee scolaire">
                <button type="submit" class="filter-btn"><i data-lucide="filter"></i> Filtrer</button>
            </form>
        </div>
    </div>

    <div class="timetable-board">
        <?php foreach ($jours as $jour): ?>
            <section class="timetable-day">
                <h3><?= htmlspecialchars($jour) ?></h3>

                <?php if (count($creneauxByDay[$jour]) === 0): ?>
                    <div class="timetable-empty">Aucun cours</div>
                <?php endif; ?>

                <?php foreach ($creneauxByDay[$jour] as $creneau): ?>
                    <article class="timetable-slot">
                        <strong><?= htmlspecialchars($creneau["module_nom"]) ?></strong>
                        <span><?= substr($creneau["heure_debut"], 0, 5) ?> - <?= substr($creneau["heure_fin"], 0, 5) ?></span>
                        <small><?= $creneau["enseignant_prenom"] ? htmlspecialchars($creneau["enseignant_prenom"] . " " . $creneau["enseignant_nom"]) : "Enseignant non affecte" ?></small>
                        <small><?= htmlspecialchars($creneau["salle"] ?: "Salle non renseignee") ?></small>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
</section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
