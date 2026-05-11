<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);
$gestionnaireNavPrefix = $gestionnaireNavPrefix ?? "";

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
            <h2>EduSysteme</h2>
            <p>Espace Gestionnaire</p>
        </div>
    </div>

    <nav class="menu">
        <a class="<?= sidebar_active("dashboard.php") ?>" href="<?= $gestionnaireNavPrefix ?>dashboard.php"><?= ui_icon("home") ?> Tableau de bord</a>

        <span>Gestion pedagogique</span>
        <a class="<?= sidebar_active(["etudiants.php", "inscriptions.php", "modifier-etudiant.php", "fiche-etudiant.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>etudiants/etudiants.php"><?= ui_icon("users") ?> Etudiants</a>
        <a class="<?= sidebar_active(["enseignants.php", "modifier-enseignant.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>enseignants/enseignants.php"><?= ui_icon("graduation") ?> Enseignants</a>
        <a class="<?= sidebar_active(["index.php", "ajouter.php", "modifier.php", "affectations.php", "ajouter-affectation.php", "modifier-affectation.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>affectations/"><?= ui_icon("clipboard") ?> Affectations</a>
        <a class="<?= sidebar_active(["inscriptions.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>etudiants/inscriptions.php"><?= ui_icon("clipboard") ?> Inscriptions</a>
        <a class="<?= sidebar_active(["classes.php", "ajouter-classe.php", "modifier-classe.php", "classe-details.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>classes/classes.php"><?= ui_icon("school") ?> Classes</a>
        <a class="<?= sidebar_active(["centre-attente.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>workflow/centre-attente.php"><?= ui_icon("clipboard") ?> Centre d'attente</a>
        <a class="<?= sidebar_active(["archive.php"]) ?>" href="<?= $gestionnaireNavPrefix ?>workflow/archive.php"><?= ui_icon("book") ?> Archive</a>
        <a class="<?= sidebar_active("emploi-temps.php") ?>" href="<?= $gestionnaireNavPrefix ?>pedagogie/emploi-temps.php"><?= ui_icon("calendar") ?> Emploi du temps</a>
        <a class="<?= sidebar_active("notes.php") ?>" href="<?= $gestionnaireNavPrefix ?>pedagogie/notes.php"><?= ui_icon("chart") ?> Notes</a>
        <a class="<?= sidebar_active("index.php") ?>" href="<?= $gestionnaireNavPrefix ?>reclamations/"><?= ui_icon("mail") ?> Reclamations</a>

        <span>Compte</span>
        <a class="<?= sidebar_active("profil.php") ?>" href="<?= $gestionnaireNavPrefix ?>compte/profil.php"><?= ui_icon("user") ?> Mon profil</a>
        <a class="<?= sidebar_active("alertes.php") ?>" href="<?= $gestionnaireNavPrefix ?>workflow/alertes.php"><?= ui_icon("bell") ?> Alertes</a>
    </nav>

    <a href="<?= $gestionnaireNavPrefix ?>../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
