<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);

function sidebar_active($pages) {
    global $currentPage;
    return in_array($currentPage, (array) $pages, true) ? "active" : "";
}
?>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><?= ui_icon("leaf") ?></div>
        <div>
            <h2>EduSystème</h2>
            <p>Gestion Universitaire</p>
        </div>
    </div>

    <nav class="menu">
        <a class="<?= sidebar_active("dashboard.php") ?>" href="dashboard.php"><?= ui_icon("home") ?> Tableau de bord</a>

        <span>Gestion des utilisateurs</span>
        <a class="<?= sidebar_active(["utilisateurs.php", "ajouter-utilisateur.php", "modifier-utilisateur.php"]) ?>" href="utilisateurs.php"><?= ui_icon("user") ?> Administrateurs</a>
        <a class="<?= sidebar_active(["gestionnaires.php"]) ?>" href="gestionnaires.php"><?= ui_icon("users") ?> Gestionnaires</a>
        <a class="<?= sidebar_active(["enseignants.php", "ajouter-enseignant.php", "modifier-enseignant.php"]) ?>" href="enseignants.php"><?= ui_icon("graduation") ?> Enseignants</a>
        <a class="<?= sidebar_active(["etudiants.php"]) ?>" href="etudiants.php"><?= ui_icon("graduation") ?> Etudiants</a>

        <span>Gestion academique</span>
        <a class="<?= sidebar_active(["classes.php", "ajouter-classe.php", "modifier-classe.php"]) ?>" href="classes.php"><?= ui_icon("school") ?> Classes</a>
        <a class="<?= sidebar_active(["emploi-temps.php"]) ?>" href="emploi-temps.php"><?= ui_icon("calendar") ?> Emploi du temps</a>
        <a class="<?= sidebar_active(["notes.php"]) ?>" href="notes.php"><?= ui_icon("chart") ?> Notes</a>

        <span>Securite</span>
        <a class="<?= sidebar_active(["profil.php"]) ?>" href="profil.php"><?= ui_icon("user") ?> Mon profil</a>
        <a class="<?= sidebar_active(["alertes.php"]) ?>" href="alertes.php"><?= ui_icon("bell") ?> Alertes</a>
        <a class="<?= sidebar_active(["parametres.php"]) ?>" href="parametres.php"><?= ui_icon("settings") ?> Parametres</a>
    </nav>

    <a href="../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
