<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/grades.php";
require_once "../app/helpers/reclamations.php";

if ($_SESSION["user"]["role"] !== "ETUDIANT") {
    header("Location: ../public/login.php");
    exit;
}

$mat = $_SESSION["user"]["MAT"];

ensure_simple_grades_schema($pdo);
ensure_reclamations_schema($pdo);

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

$stmt = $pdo->prepare("
    SELECT
        u.MAT, u.nom, u.prenom, u.date_de_naissance, u.email, u.statut,
        e.annee_etude,
        c.ID AS classe_id,
        c.nom AS classe_nom,
        c.niveau
    FROM etudiant e
    INNER JOIN utilisateur u ON e.MAT = u.MAT
    INNER JOIN classe c ON e.classe_id = c.ID
    WHERE e.MAT = ?
    LIMIT 1
");
$stmt->execute([$mat]);
$etudiant = $stmt->fetch();

$anneeScolaire = date("Y") . "-" . ((int) date("Y") + 1);
$joursEmploi = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
$libellesJours = [
    "Lundi" => "Lun",
    "Mardi" => "Mar",
    "Mercredi" => "Mer",
    "Jeudi" => "Jeu",
    "Vendredi" => "Ven",
    "Samedi" => "Sam",
];
$creneauxEmploi = [];
$creneauxByDay = array_fill_keys($joursEmploi, []);
$heuresEmploi = [];

if ($etudiant) {
    $emploiStmt = $pdo->prepare("
        SELECT et.*, m.nom AS module_nom, u.nom AS enseignant_nom, u.prenom AS enseignant_prenom
        FROM emploi_temps et
        INNER JOIN module m ON m.ID = et.module_id
        LEFT JOIN utilisateur u ON u.MAT = et.MAT_enseignant
        INNER JOIN etudiant e ON e.classe_id = et.classe_id
        WHERE e.MAT = ?
        AND et.annee_scolaire = ?
        ORDER BY FIELD(et.jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), et.heure_debut ASC
    ");
    $emploiStmt->execute([$mat, $anneeScolaire]);
    $creneauxEmploi = $emploiStmt->fetchAll();

    foreach ($creneauxEmploi as $creneau) {
        if (isset($creneauxByDay[$creneau["jour_semaine"]])) {
            $creneauxByDay[$creneau["jour_semaine"]][] = $creneau;
        }

        $debut = substr($creneau["heure_debut"], 0, 5);
        if (!in_array($debut, $heuresEmploi, true)) {
            $heuresEmploi[] = $debut;
        }
    }

    usort($heuresEmploi, function ($a, $b) {
        return strtotime($a) <=> strtotime($b);
    });
}

function findDashboardCreneau(array $creneauxByDay, string $jour, string $heureDebut)
{
    foreach ($creneauxByDay[$jour] ?? [] as $creneau) {
        if (substr($creneau["heure_debut"], 0, 5) === $heureDebut) {
            return $creneau;
        }
    }

    return null;
}

$notes = $pdo->prepare("
    SELECT
        n.valeur,
        n.devoir_1,
        n.devoir_2,
        n.devoir_3,
        n.note_classe,
        n.note_examen,
        n.note_finale,
        m.nom AS module_nom
    FROM note n
    INNER JOIN module m ON n.module_id = m.ID
    WHERE n.MAT_ET = ?
    ORDER BY m.nom ASC
");
$notes->execute([$mat]);
$notes = $notes->fetchAll();

$semesters = $etudiant ? student_semesters($pdo, (int) $etudiant["classe_id"]) : [];
$selectedSemesterId = $semesters[0]["id"] ?? null;
$semesterAverage = $selectedSemesterId
    ? student_semester_average($pdo, $mat, (int) $etudiant["classe_id"], (int) $selectedSemesterId)
    : [
        "semestre_nom" => "Semestre",
        "total_modules" => 0,
        "notes_finales" => 0,
        "complete" => false,
        "moyenne" => null,
    ];

$reclamationsStmt = $pdo->prepare("
    SELECT sujet, statut, created_at
    FROM reclamations
    WHERE MAT_etudiant = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$reclamationsStmt->execute([$mat]);
$recentReclamations = $reclamationsStmt->fetchAll();
$pendingReclamationsStmt = $pdo->prepare("SELECT COUNT(*) FROM reclamations WHERE MAT_etudiant = ? AND statut = 'EN_ATTENTE'");
$pendingReclamationsStmt->execute([$mat]);
$pendingReclamations = (int) $pendingReclamationsStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Etudiant - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>

<body class="app-body">

<?php include "../app/includes/sidebar-etudiant.php"; ?>

<main class="main-content">

<header class="topbar">
    <button class="menu-btn" type="button" aria-label="Ouvrir le menu"><?= ui_icon("menu") ?></button>

    <div class="search-box">
        <?= ui_icon("search") ?>
        <input type="text" placeholder="Rechercher un cours, une matiere...">
    </div>

    <div class="top-actions">
        <div class="admin-profile">
            <div class="avatar"><?= ui_icon("graduation") ?></div>
            <div>
                <strong><?= htmlspecialchars($etudiant["prenom"] . " " . $etudiant["nom"]) ?></strong>
                <small>Etudiant</small>
            </div>
        </div>
    </div>
</header>

<section class="dashboard">

    <div class="dashboard-head">
        <div>
            <h1>Bienvenue, <?= htmlspecialchars($etudiant["prenom"]) ?></h1>
            <p>Voici un apercu de votre parcours academique.</p>
        </div>
    </div>

    <div class="student-top-grid">

        <div class="student-profile-card">
            <div class="student-photo"><?= ui_icon("graduation") ?></div>

            <div class="student-info">
                <h2><?= htmlspecialchars($etudiant["prenom"] . " " . $etudiant["nom"]) ?></h2>
                <p class="green-text"><?= htmlspecialchars($etudiant["MAT"]) ?></p>
                <p><?= ui_icon("calendar") ?> <?= date("d/m/Y", strtotime($etudiant["date_de_naissance"])) ?></p>
                <p><?= ui_icon("graduation") ?> <?= htmlspecialchars($etudiant["niveau"]) ?></p>
                <p><?= ui_icon("school") ?> <?= htmlspecialchars($etudiant["classe_nom"]) ?></p>

                <span class="status active">Actif</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><?= ui_icon("book") ?></div>
            <div>
                <p>Moyenne generale - <?= htmlspecialchars($semesterAverage["semestre_nom"]) ?></p>
                <h2><?= $semesterAverage["complete"] ? htmlspecialchars((string) $semesterAverage["moyenne"]) . "/20" : "-" ?></h2>
                <span>
                    <?= $semesterAverage["complete"]
                        ? ($semesterAverage["moyenne"] >= 10 ? "Bien" : "A ameliorer")
                        : htmlspecialchars($semesterAverage["notes_finales"] . "/" . $semesterAverage["total_modules"] . " notes finales") ?>
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><?= ui_icon("calendar") ?></div>
            <div>
                <p>Absences</p>
                <h2>0</h2>
                <span>Heures d'absence</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><?= ui_icon("mail") ?></div>
            <div>
                <p>Reclamations</p>
                <h2><?= $pendingReclamations ?></h2>
                <span>En cours</span>
            </div>
        </div>

    </div>

    <div class="dashboard-grid student-grid">

        <div class="card">
            <h3><?= ui_icon("calendar") ?> Emploi du temps</h3>

            <?php if (count($heuresEmploi) > 0): ?>
                <div
                    class="timetable"
                    style="grid-template-columns: 70px repeat(<?= count($joursEmploi) ?>, minmax(120px, 1fr));"
                >
                    <div class="time-col" style="grid-template-rows: 40px repeat(<?= count($heuresEmploi) ?>, 70px);">
                        <span></span>
                        <?php foreach ($heuresEmploi as $heure): ?>
                            <span><?= htmlspecialchars($heure) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ($joursEmploi as $jour): ?>
                        <div class="day-col" style="grid-template-rows: 40px repeat(<?= count($heuresEmploi) ?>, 70px);">
                            <strong><?= htmlspecialchars($libellesJours[$jour]) ?></strong>

                            <?php foreach ($heuresEmploi as $heure): ?>
                                <?php $creneau = findDashboardCreneau($creneauxByDay, $jour, $heure); ?>

                                <?php if ($creneau): ?>
                                    <div class="course-box">
                                        <?= htmlspecialchars($creneau["module_nom"]) ?><br>
                                        <small><?= substr($creneau["heure_debut"], 0, 5) ?>-<?= substr($creneau["heure_fin"], 0, 5) ?></small><br>
                                        <small><?= htmlspecialchars($creneau["salle"] ?: "Salle non renseignee") ?></small>
                                    </div>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="timetable-empty">Aucun cours dans l'emploi du temps <?= htmlspecialchars($anneeScolaire) ?>.</div>
            <?php endif; ?>

            <a href="etudes/emploi-temps.php" class="card-link">Voir tout l'emploi du temps -></a>
        </div>

        <div class="card">
            <h3><?= ui_icon("chart") ?> Mes notes par matiere</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matiere</th>
                        <th>Classe</th>
                        <th>Examen</th>
                        <th>Finale</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($notes as $n): ?>
                        <tr>
                            <td><?= htmlspecialchars($n["module_nom"]) ?></td>
                            <td><?= $n["note_classe"] !== null ? htmlspecialchars($n["note_classe"]) : "-" ?></td>
                            <td><?= $n["note_examen"] !== null ? htmlspecialchars($n["note_examen"]) : "-" ?></td>
                            <td>
                                <?php if ($n["note_finale"] === null): ?>
                                    -
                                <?php elseif ($n["note_finale"] >= 10): ?>
                                    <span class="status active"><?= htmlspecialchars($n["note_finale"]) ?>/20</span>
                                <?php else: ?>
                                    <span class="status inactive"><?= htmlspecialchars($n["note_finale"]) ?>/20</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($notes) === 0): ?>
                        <tr>
                            <td colspan="4">Aucune note disponible.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="etudes/notes.php" class="card-link">Voir toutes mes notes -></a>
        </div>

        <div class="card">
            <h3><?= ui_icon("mail") ?> Mes reclamations</h3>

            <?php if (count($recentReclamations) === 0): ?>
                <div class="list-item">
                    <div class="mini-avatar"><?= ui_icon("pin") ?></div>
                    <div>
                        <strong>Aucune reclamation recente</strong>
                        <small>Vos demandes apparaitront ici.</small>
                    </div>
                    <span>OK</span>
                </div>
            <?php endif; ?>

            <?php foreach ($recentReclamations as $reclamation): ?>
                <div class="list-item">
                    <div class="mini-avatar"><?= ui_icon("mail") ?></div>
                    <div>
                        <strong><?= htmlspecialchars($reclamation["sujet"]) ?></strong>
                        <small><?= date("d/m/Y", strtotime($reclamation["created_at"])) ?></small>
                    </div>
                    <span class="<?= htmlspecialchars(reclamation_status_class($reclamation["statut"])) ?>">
                        <?= htmlspecialchars(reclamation_status_label($reclamation["statut"])) ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <a href="reclamations/" class="card-link">Voir toutes mes reclamations -></a>
        </div>

        <div class="card">
            <h3><?= ui_icon("bolt") ?> Actions rapides</h3>

            <div class="quick-actions">
                <a href="reclamations/nouvelle.php"><?= ui_icon("clipboard") ?><strong>Faire une reclamation</strong><small>Declarer un probleme</small></a>
                <a href="etudes/notes.php"><?= ui_icon("chart") ?><strong>Consulter mes notes</strong><small>Voir mes resultats</small></a>
                <a href="etudes/emploi-temps.php"><?= ui_icon("calendar") ?><strong>Emploi du temps</strong><small>Voir mon planning</small></a>
                <a href="compte/profil.php"><?= ui_icon("user") ?><strong>Mon profil</strong><small>Mes informations</small></a>
            </div>
        </div>

    </div>

</section>

</main>

</body>
</html>
