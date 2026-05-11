<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$adminNavPrefix = '../';
$navPrefix = '../';

$totalUsers = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE deleted_at IS NULL")->fetchColumn();
$totalRoles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
$totalBackups = $pdo->query("SELECT COUNT(*) FROM backup_jobs")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Parametres - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="settings-head">
        <h1>Parametres</h1>
        <div class="breadcrumb">
            <span>Accueil</span>
            <i data-lucide="chevron-right"></i>
            <strong>Parametres</strong>
        </div>
    </div>

    <div class="settings-tabs">
        <button class="active">Apercu</button>
        <button>Informations etablissement</button>
        <button>Annee scolaire</button>
        <button>Utilisateurs & roles</button>
        <button>Securite</button>
        <button>Notifications</button>
        <button>Sauvegarde</button>
        <button>Preferences</button>
    </div>

    <div class="settings-layout">

        <div class="settings-main">

            <div class="settings-overview-card">
                <h2>Apercu des parametres</h2>
                <p>Gerez les parametres generaux de votre systeme EduManage.</p>

                <div class="settings-grid">

                    <a href="#" class="settings-box green">
                        <div><i data-lucide="school"></i></div>
                        <h3>Informations etablissement</h3>
                        <p>Gerez les informations generales de votre etablissement.</p>
                        <span><i data-lucide="check"></i> Configure</span>
                    </a>

                    <a href="#" class="settings-box blue">
                        <div><i data-lucide="calendar-days"></i></div>
                        <h3>Annee scolaire</h3>
                        <p>Configurez les annees scolaires, semestres et periodes.</p>
                        <span><i data-lucide="calendar"></i> 2024-2025 en cours</span>
                    </a>

                    <a href="#" class="settings-box orange">
                        <div><i data-lucide="users"></i></div>
                        <h3>Utilisateurs & roles</h3>
                        <p>Gerez les roles et permissions des utilisateurs.</p>
                        <span><i data-lucide="users"></i> <?= $totalRoles ?> roles definis</span>
                    </a>

                    <a href="#" class="settings-box purple">
                        <div><i data-lucide="shield-check"></i></div>
                        <h3>Securite</h3>
                        <p>Parametres de securite et controle d'acces.</p>
                        <span><i data-lucide="check"></i> Securise</span>
                    </a>

                    <a href="#" class="settings-box pink">
                        <div><i data-lucide="bell"></i></div>
                        <h3>Notifications</h3>
                        <p>Configurez les notifications systeme et email.</p>
                        <span><i data-lucide="mail"></i> Email activees</span>
                    </a>

                    <a href="#" class="settings-box cyan">
                        <div><i data-lucide="cloud-upload"></i></div>
                        <h3>Sauvegarde</h3>
                        <p>Gerez les sauvegardes et la recuperation des donnees.</p>
                        <span><i data-lucide="check"></i> <?= $totalBackups ?> sauvegardes</span>
                    </a>

                    <a href="#" class="settings-box yellow">
                        <div><i data-lucide="sliders-horizontal"></i></div>
                        <h3>Preferences</h3>
                        <p>Personnalisez les preferences d'affichage et langue.</p>
                        <span><i data-lucide="globe"></i> Francais</span>
                    </a>


                </div>
            </div>


        </div>

        <aside class="settings-side">

            <div class="system-card">
                <h3>Informations systeme</h3>

                <div class="system-row">
                    <span>Version</span>
                    <strong>v2.1.0</strong>
                </div>

                <div class="system-row">
                    <span>Environnement</span>
                    <b class="green-pill">Production</b>
                </div>

                <div class="system-row">
                    <span>Base de donnees</span>
                    <b class="green-pill">Connectee</b>
                </div>

                <div class="system-row">
                    <span>Utilisateurs</span>
                    <strong><?= $totalUsers ?></strong>
                </div>

                <div class="storage-bar">
                    <div></div>
                </div>

                <small>24.5% espace utilise</small>
            </div>

            <div class="system-card">
                <h3>Actions rapides</h3>

                <button class="quick-setting-btn">
                    <i data-lucide="sparkles"></i>
                    Vider le cache
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="database"></i>
                    Optimiser la base de donnees
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="mail"></i>
                    Tester les emails
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="file-text"></i>
                    Generer un rapport systeme
                </button>
            </div>

            <div class="help-settings-card">
                <h3>Besoin d'aide ?</h3>
                <p>Consultez notre documentation ou contactez notre support technique.</p>

                <button>
                    <i data-lucide="book-open"></i>
                    Voir la documentation
                </button>

                <button>
                    <i data-lucide="headphones"></i>
                    Contacter le support
                </button>
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
