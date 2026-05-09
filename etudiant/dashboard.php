<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "ETUDIANT") {
    header("Location: ../public/login.php");
    exit;
}

$mat = $_SESSION["user"]["MAT"];

$stmt = $pdo->prepare("
    SELECT
        u.MAT, u.nom, u.prenom, u.date_de_naissance, u.email, u.statut,
        e.annee_etude,
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

$notes = $pdo->prepare("
    SELECT
        n.valeur,
        n.poids,
        n.penalite,
        m.nom AS module_nom
    FROM note n
    INNER JOIN module m ON n.module_id = m.ID
    WHERE n.MAT_ET = ?
    ORDER BY m.nom ASC
");
$notes->execute([$mat]);
$notes = $notes->fetchAll();

$moyenne = 0;
$totalPoids = 0;

foreach ($notes as $n) {
    $moyenne += $n["valeur"] * $n["poids"];
    $totalPoids += $n["poids"];
}

$moyenneFinale = $totalPoids > 0 ? round($moyenne / $totalPoids, 2) : 0;
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
        <div class="notif"><?= ui_icon("bell") ?><span>3</span></div>

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
                <p>Moyenne generale</p>
                <h2><?= $moyenneFinale ?>/20</h2>
                <span><?= $moyenneFinale >= 10 ? "Bien" : "A ameliorer" ?></span>
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
                <h2>0</h2>
                <span>En cours</span>
            </div>
        </div>

    </div>

    <div class="dashboard-grid student-grid">

        <div class="card">
            <h3><?= ui_icon("calendar") ?> Emploi du temps</h3>

            <div class="timetable">
                <div class="time-col">
                    <span>08:00</span>
                    <span>10:00</span>
                    <span>14:00</span>
                    <span>16:00</span>
                </div>

                <div class="day-col">
                    <strong>Lun</strong>
                    <div class="course-box">Algorithmique<br><small>Salle 101</small></div>
                    <div></div>
                    <div class="course-box">BD<br><small>Salle 203</small></div>
                </div>

                <div class="day-col">
                    <strong>Mar</strong>
                    <div></div>
                    <div class="course-box">Maths discretes<br><small>Salle 201</small></div>
                    <div></div>
                    <div class="course-box">Reseaux<br><small>Salle 105</small></div>
                </div>

                <div class="day-col">
                    <strong>Mer</strong>
                    <div class="course-box">Base de donnees<br><small>Salle 203</small></div>
                    <div></div>
                    <div class="course-box">Structures<br><small>Salle 105</small></div>
                </div>

                <div class="day-col">
                    <strong>Jeu</strong>
                    <div></div>
                    <div class="course-box">Algorithmique<br><small>Salle 101</small></div>
                    <div></div>
                    <div class="course-box">BD<br><small>Salle 203</small></div>
                </div>

                <div class="day-col">
                    <strong>Ven</strong>
                    <div class="course-box">Reseaux<br><small>Salle 105</small></div>
                    <div></div>
                    <div class="course-box">Anglais<br><small>Salle 302</small></div>
                </div>
            </div>

            <a href="emploi-temps.php" class="card-link">Voir tout l'emploi du temps -></a>
        </div>

        <div class="card">
            <h3><?= ui_icon("chart") ?> Mes notes par matiere</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matiere</th>
                        <th>Note</th>
                        <th>Poids</th>
                        <th>Appreciation</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($notes as $n): ?>
                        <tr>
                            <td><?= htmlspecialchars($n["module_nom"]) ?></td>
                            <td><strong><?= htmlspecialchars($n["valeur"]) ?>/20</strong></td>
                            <td><?= htmlspecialchars($n["poids"]) ?>%</td>
                            <td>
                                <?php if ($n["valeur"] >= 14): ?>
                                    <span class="status active">Tres bien</span>
                                <?php elseif ($n["valeur"] >= 10): ?>
                                    <span class="badge">Bien</span>
                                <?php else: ?>
                                    <span class="status inactive">Faible</span>
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

            <a href="notes.php" class="card-link">Voir toutes mes notes -></a>
        </div>

        <div class="card">
            <h3><?= ui_icon("mail") ?> Mes reclamations</h3>

            <div class="list-item">
                <div class="mini-avatar"><?= ui_icon("pin") ?></div>
                <div>
                    <strong>Aucune reclamation recente</strong>
                    <small>Vos demandes apparaitront ici.</small>
                </div>
                <span>OK</span>
            </div>

            <a href="reclamations.php" class="card-link">Voir toutes mes reclamations -></a>
        </div>

        <div class="card">
            <h3><?= ui_icon("bolt") ?> Actions rapides</h3>

            <div class="quick-actions">
                <a href="nouvelle-reclamation.php"><?= ui_icon("clipboard") ?><strong>Faire une reclamation</strong><small>Declarer un probleme</small></a>
                <a href="notes.php"><?= ui_icon("chart") ?><strong>Consulter mes notes</strong><small>Voir mes resultats</small></a>
                <a href="emploi-temps.php"><?= ui_icon("calendar") ?><strong>Emploi du temps</strong><small>Voir mon planning</small></a>
                <a href="profil.php"><?= ui_icon("user") ?><strong>Mon profil</strong><small>Mes informations</small></a>
            </div>
        </div>

    </div>

</section>

</main>

</body>
</html>
