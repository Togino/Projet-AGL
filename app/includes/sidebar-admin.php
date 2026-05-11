<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);
$adminNavPrefix = $adminNavPrefix ?? "";

function sidebar_active($pages) {
    global $currentPage;
    return in_array($currentPage, (array) $pages, true) ? "active" : "";
}
?>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><?= ui_icon("leaf") ?></div>
        <div>
            <h2>EduSysteme</h2>
            <p>Gestion Universitaire</p>
        </div>
    </div>

    <nav class="menu">
        <a class="<?= sidebar_active("dashboard.php") ?>" href="<?= $adminNavPrefix ?>dashboard.php"><?= ui_icon("home") ?> Tableau de bord</a>

        <span>Gestion des utilisateurs</span>
        <a class="<?= sidebar_active(["utilisateurs.php", "ajouter-utilisateur.php", "modifier-utilisateur.php"]) ?>" href="<?= $adminNavPrefix ?>utilisateurs/utilisateurs.php"><?= ui_icon("user") ?> Administrateurs</a>
        <a class="<?= sidebar_active(["gestionnaires.php"]) ?>" href="<?= $adminNavPrefix ?>utilisateurs/gestionnaires.php"><?= ui_icon("users") ?> Gestionnaires</a>
        <a class="<?= sidebar_active(["enseignants.php", "ajouter-enseignant.php", "modifier-enseignant.php"]) ?>" href="<?= $adminNavPrefix ?>enseignants/enseignants.php"><?= ui_icon("graduation") ?> Enseignants</a>
        <a class="<?= sidebar_active(["etudiants.php", "ajouter-etudiant.php", "modifier-etudiant.php"]) ?>" href="<?= $adminNavPrefix ?>etudiants/etudiants.php"><?= ui_icon("graduation") ?> Etudiants</a>

        <span>Gestion academique</span>
        <a class="<?= sidebar_active(["classes.php", "ajouter-classe.php", "modifier-classe.php", "classe-details.php"]) ?>" href="<?= $adminNavPrefix ?>classes/classes.php"><?= ui_icon("school") ?> Classes</a>
        <a class="<?= sidebar_active(["modules.php", "ajouter-module.php", "supprimer-module.php"]) ?>" href="<?= $adminNavPrefix ?>modules/modules.php"><?= ui_icon("folder") ?> Modules</a>
        <a class="<?= sidebar_active(["affectations.php", "ajouter-affectation.php", "supprimer-affectation.php"]) ?>" href="<?= $adminNavPrefix ?>affectations/affectations.php"><?= ui_icon("link") ?> Affectations</a>
        <a class="<?= sidebar_active(["centre-attente.php"]) ?>" href="<?= $adminNavPrefix ?>workflow/centre-attente.php"><?= ui_icon("clipboard") ?> Centre d'attente</a>
        <a class="<?= sidebar_active(["archive.php"]) ?>" href="<?= $adminNavPrefix ?>workflow/archive.php"><?= ui_icon("book") ?> Archive</a>
        <a class="<?= sidebar_active(["emploi-temps.php"]) ?>" href="<?= $adminNavPrefix ?>pedagogie/emploi-temps.php"><?= ui_icon("calendar") ?> Emploi du temps</a>
        <a class="<?= sidebar_active(["notes.php"]) ?>" href="<?= $adminNavPrefix ?>pedagogie/notes.php"><?= ui_icon("chart") ?> Notes</a>

        <span>Securite</span>
        <a class="<?= sidebar_active(["profil.php"]) ?>" href="<?= $adminNavPrefix ?>compte/profil.php"><?= ui_icon("user") ?> Mon profil</a>
        <a class="<?= sidebar_active(["alertes.php"]) ?>" href="<?= $adminNavPrefix ?>workflow/alertes.php"><?= ui_icon("bell") ?> Alertes</a>
    </nav>

    <a href="<?= $adminNavPrefix ?>../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
