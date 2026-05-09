<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);

if (!function_exists("sidebar_active")) {
    function sidebar_active($pages) {
        global $currentPage;
        return in_array($currentPage, (array) $pages, true) ? "active" : "";
    }
}
?>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><?= ui_icon("leaf") ?></div>
        <div>
            <h2>EduManage</h2>
            <p>Espace Etudiant</p>
        </div>
    </div>

    <nav class="menu">
        <span>Tableau de bord</span>
        <a class="<?= sidebar_active("dashboard.php") ?>" href="dashboard.php"><?= ui_icon("home") ?> Accueil</a>

        <span>Mes etudes</span>
        <a class="<?= sidebar_active("notes.php") ?>" href="notes.php"><?= ui_icon("chart") ?> Mes notes</a>
        <a class="<?= sidebar_active("emploi-temps.php") ?>" href="emploi-temps.php"><?= ui_icon("calendar") ?> Emploi du temps</a>

        <span>Reclamations</span>
        <a class="<?= sidebar_active("reclamations.php") ?>" href="reclamations.php"><?= ui_icon("mail") ?> Mes reclamations</a>
        <a class="<?= sidebar_active("nouvelle-reclamation.php") ?>" href="nouvelle-reclamation.php"><?= ui_icon("plus") ?> Nouvelle reclamation</a>

        <span>Mon compte</span>
        <a class="<?= sidebar_active("profil.php") ?>" href="profil.php"><?= ui_icon("user") ?> Mon profil</a>
        <a class="<?= sidebar_active("parametres.php") ?>" href="parametres.php"><?= ui_icon("settings") ?> Parametres</a>
    </nav>

    <a href="../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
