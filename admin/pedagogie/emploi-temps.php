<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

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

$classes = $pdo->query("
    SELECT ID, nom, niveau
    FROM classe
    ORDER BY nom ASC
")->fetchAll();

$selectedClasse = $_GET["classe"] ?? ($classes[0]["ID"] ?? 0);

$stmtClasse = $pdo->prepare("
    SELECT *
    FROM classe
    WHERE ID = ?
");
$stmtClasse->execute([$selectedClasse]);

$currentClasse = $stmtClasse->fetch();

$stmtPlanning = $pdo->prepare("
    SELECT 
        edt.*,
        m.nom AS module_nom,
        u.nom,
        u.prenom
    FROM emploi_temps edt
    INNER JOIN module m ON edt.module_id = m.ID
    LEFT JOIN utilisateur u ON edt.MAT_enseignant = u.MAT
    WHERE edt.classe_id = ?
    ORDER BY FIELD(edt.jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), edt.heure_debut
");

$stmtPlanning->execute([$selectedClasse]);

$planning = $stmtPlanning->fetchAll();
$anneeAffichee = $planning[0]["annee_scolaire"] ?? (date("Y") . "-" . ((int) date("Y") + 1));

function getCours($planning, $jour, $heure)
{
    foreach ($planning as $cours) {

        if (
            $cours["jour_semaine"] === $jour &&
            substr($cours["heure_debut"], 0, 5) === $heure
        ) {
            return $cours;
        }
    }

    return null;
}

$jours = [
    "Lundi",
    "Mardi",
    "Mercredi",
    "Jeudi",
    "Vendredi",
    "Samedi"
];

$heures = [];

foreach ($planning as $cours) {
    $debut = substr($cours["heure_debut"], 0, 5);
    if (!in_array($debut, $heures, true)) {
        $heures[] = $debut;
    }
}

usort($heures, function ($a, $b) {
    return strtotime($a) <=> strtotime($b);
});
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

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">

<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="students-header">
        <div>
            <h1>Emploi du temps des classes</h1>

            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <span>Classes</span>
                <i data-lucide="chevron-right"></i>
                <strong>Emploi du temps</strong>
            </div>
        </div>
    </div>

    <div class="edt-toolbar">

        <form method="GET" class="edt-toolbar-left">

            <div class="toolbar-group">
                <label>Classe</label>

                <select name="classe" onchange="this.form.submit()">

                    <?php foreach ($classes as $classe): ?>

                        <option 
                            value="<?= $classe["ID"] ?>"
                            <?= $selectedClasse == $classe["ID"] ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($classe["nom"]) ?>
                            -
                            <?= htmlspecialchars($classe["niveau"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <div class="toolbar-group">
                <label>Annee scolaire</label>

                <input type="text" value="<?= htmlspecialchars($anneeAffichee) ?>" readonly>
            </div>

        </form>

        <div class="edt-toolbar-actions">

            <button class="outlined-btn">
                <i data-lucide="download"></i>
                Exporter
            </button>

        </div>

    </div>

    <div class="edt-layout">

        <div class="edt-main-card">

            <div class="edt-table">

                <div class="edt-header">

                    <div class="edt-hour-column">
                        Heure
                    </div>

                    <?php foreach ($jours as $jour): ?>

                        <div class="edt-day-column">
                            <strong><?= $jour ?></strong>
                        </div>

                    <?php endforeach; ?>

                </div>

                <?php foreach ($heures as $heure): ?>

                    <div class="edt-row">

                        <div class="edt-hour-cell">
                            <?= $heure ?>
                        </div>

                        <?php foreach ($jours as $jour): ?>

                            <?php $cours = getCours($planning, $jour, $heure); ?>

                            <div class="edt-course-cell">

                                <?php if ($cours): ?>

                                    <div class="course-card green">

                                        <h4>
                                            <?= htmlspecialchars($cours["module_nom"]) ?>
                                        </h4>

                                        <p>
                                            <?= htmlspecialchars($cours["prenom"]) ?>
                                            <?= htmlspecialchars($cours["nom"]) ?>
                                        </p>

                                        <small>
                                            Salle <?= htmlspecialchars($cours["salle"] ?: "-") ?>
                                        </small>

                                    </div>

                                <?php else: ?>

                                    <span class="empty-course">—</span>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

                <?php if (count($heures) === 0): ?>
                    <div class="edt-empty-state">Aucun cours enregistre pour cette classe.</div>
                <?php endif; ?>

            </div>

            <div class="edt-footer-info">

                <i data-lucide="info"></i>

                Vue en lecture seule de l'emploi du temps de la classe.

            </div>

        </div>

        <aside class="edt-sidebar">

            <div class="edt-side-card">

                <h3>Informations de la classe</h3>

                <div class="class-summary-icon">
                    <i data-lucide="users"></i>
                </div>

                <div class="class-summary-info">
                    <span>Classe</span>
                    <strong>
                        <?= htmlspecialchars($currentClasse["nom"] ?? "") ?>
                    </strong>
                </div>

                <div class="class-summary-info">
                    <span>Niveau</span>
                    <strong>
                        <?= htmlspecialchars($currentClasse["niveau"] ?? "") ?>
                    </strong>
                </div>

                <div class="class-summary-info">
                    <span>Année scolaire</span>
                    <strong><?= htmlspecialchars($anneeAffichee) ?></strong>
                </div>
            </div>
            <div class="edt-side-card">
                <h3>Lecture seule</h3>
                <p class="muted-text">La configuration se fait depuis le compte gestionnaire.</p>
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
