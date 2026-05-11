<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);
$etudiantNavPrefix = $etudiantNavPrefix ?? "";

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
        <a class="<?= sidebar_active("dashboard.php") ?>" href="<?= $etudiantNavPrefix ?>dashboard.php"><?= ui_icon("home") ?> Accueil</a>

        <span>Mes etudes</span>
        <a class="<?= sidebar_active("notes.php") ?>" href="<?= $etudiantNavPrefix ?>etudes/notes.php"><?= ui_icon("chart") ?> Mes notes</a>
        <a class="<?= sidebar_active("emploi-temps.php") ?>" href="<?= $etudiantNavPrefix ?>etudes/emploi-temps.php"><?= ui_icon("calendar") ?> Emploi du temps</a>

        <span>Reclamations</span>
        <a class="<?= sidebar_active(["index.php", "reclamations.php"]) ?>" href="<?= $etudiantNavPrefix ?>reclamations/"><?= ui_icon("mail") ?> Mes reclamations</a>
        <a class="<?= sidebar_active(["nouvelle.php", "nouvelle-reclamation.php"]) ?>" href="<?= $etudiantNavPrefix ?>reclamations/nouvelle.php"><?= ui_icon("plus") ?> Nouvelle reclamation</a>

        <span>Mon compte</span>
        <a class="<?= sidebar_active("profil.php") ?>" href="<?= $etudiantNavPrefix ?>compte/profil.php"><?= ui_icon("user") ?> Mon profil</a>
    </nav>

    <a href="<?= $etudiantNavPrefix ?>../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
