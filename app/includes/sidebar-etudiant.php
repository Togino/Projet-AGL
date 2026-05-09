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
        <a class="active" href="dashboard.php"><?= ui_icon("home") ?> Accueil</a>

        <span>Mes etudes</span>
        <a href="notes.php"><?= ui_icon("chart") ?> Mes notes</a>
        <a href="emploi-temps.php"><?= ui_icon("calendar") ?> Emploi du temps</a>

        <span>Reclamations</span>
        <a href="reclamations.php"><?= ui_icon("mail") ?> Mes reclamations</a>
        <a href="nouvelle-reclamation.php"><?= ui_icon("plus") ?> Nouvelle reclamation</a>

        <span>Mon compte</span>
        <a href="profil.php"><?= ui_icon("user") ?> Mon profil</a>
        <a href="parametres.php"><?= ui_icon("settings") ?> Parametres</a>
    </nav>

    <a href="../public/logout.php" class="logout"><?= ui_icon("log-out") ?> Deconnexion</a>
</aside>
