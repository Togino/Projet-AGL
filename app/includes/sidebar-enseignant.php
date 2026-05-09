<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);

if (!function_exists("sidebar_active")) {
    function sidebar_active($pages) {
        global $currentPage;
        return in_array($currentPage, (array) $pages, true) ? "active" : "";
    }
}
?>

<aside class="sidebar teacher-sidebar">
    <div class="brand">
        <div class="brand-icon"><?= ui_icon("graduation") ?></div>
        <div>
            <h2>EduManage</h2>
            <p>Espace Enseignant</p>
        </div>
    </div>

    <nav class="menu">
        <a class="<?= sidebar_active("dashboard.php") ?>" href="dashboard.php"><?= ui_icon("home") ?> Tableau de bord</a>

        <span>Academique</span>
        <a class="<?= sidebar_active("classes.php") ?>" href="classes.php"><?= ui_icon("school") ?> Mes classes</a>
        <a class="<?= sidebar_active("notes.php") ?>" href="notes.php"><?= ui_icon("chart") ?> Notes</a>

        <span>Compte</span>
        <a class="<?= sidebar_active("profil.php") ?>" href="profil.php"><?= ui_icon("user") ?> Mon profil</a>
    </nav>

    <a href="../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
