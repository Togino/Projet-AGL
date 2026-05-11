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
    <title>ParamÃ¨tres - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="settings-head">
        <h1>ParamÃ¨tres</h1>
        <div class="breadcrumb">
            <span>Accueil</span>
            <i data-lucide="chevron-right"></i>
            <strong>ParamÃ¨tres</strong>
        </div>
    </div>

    <div class="settings-tabs">
        <button class="active">AperÃ§u</button>
        <button>Informations Ã©tablissement</button>
        <button>AnnÃ©e scolaire</button>
        <button>Utilisateurs & rÃ´les</button>
        <button>SÃ©curitÃ©</button>
        <button>Notifications</button>
        <button>Sauvegarde</button>
        <button>PrÃ©fÃ©rences</button>
    </div>

    <div class="settings-layout">

        <div class="settings-main">

            <div class="settings-overview-card">
                <h2>AperÃ§u des paramÃ¨tres</h2>
                <p>GÃ©rez les paramÃ¨tres gÃ©nÃ©raux de votre systÃ¨me EduManage.</p>

                <div class="settings-grid">

                    <a href="#" class="settings-box green">
                        <div><i data-lucide="school"></i></div>
                        <h3>Informations Ã©tablissement</h3>
                        <p>GÃ©rez les informations gÃ©nÃ©rales de votre Ã©tablissement.</p>
                        <span><i data-lucide="check"></i> ConfigurÃ©</span>
                    </a>

                    <a href="#" class="settings-box blue">
                        <div><i data-lucide="calendar-days"></i></div>
                        <h3>AnnÃ©e scolaire</h3>
                        <p>Configurez les annÃ©es scolaires, semestres et pÃ©riodes.</p>
                        <span><i data-lucide="calendar"></i> 2024-2025 en cours</span>
                    </a>

                    <a href="#" class="settings-box orange">
                        <div><i data-lucide="users"></i></div>
                        <h3>Utilisateurs & rÃ´les</h3>
                        <p>GÃ©rez les rÃ´les et permissions des utilisateurs.</p>
                        <span><i data-lucide="users"></i> <?= $totalRoles ?> rÃ´les dÃ©finis</span>
                    </a>

                    <a href="#" class="settings-box purple">
                        <div><i data-lucide="shield-check"></i></div>
                        <h3>SÃ©curitÃ©</h3>
                        <p>ParamÃ¨tres de sÃ©curitÃ© et contrÃ´le dâ€™accÃ¨s.</p>
                        <span><i data-lucide="check"></i> SÃ©curisÃ©</span>
                    </a>

                    <a href="#" class="settings-box pink">
                        <div><i data-lucide="bell"></i></div>
                        <h3>Notifications</h3>
                        <p>Configurez les notifications systÃ¨me et email.</p>
                        <span><i data-lucide="mail"></i> Email activÃ©es</span>
                    </a>

                    <a href="#" class="settings-box cyan">
                        <div><i data-lucide="cloud-upload"></i></div>
                        <h3>Sauvegarde</h3>
                        <p>GÃ©rez les sauvegardes et la rÃ©cupÃ©ration des donnÃ©es.</p>
                        <span><i data-lucide="check"></i> <?= $totalBackups ?> sauvegardes</span>
                    </a>

                    <a href="#" class="settings-box yellow">
                        <div><i data-lucide="sliders-horizontal"></i></div>
                        <h3>PrÃ©fÃ©rences</h3>
                        <p>Personnalisez les prÃ©fÃ©rences dâ€™affichage et langue.</p>
                        <span><i data-lucide="globe"></i> FranÃ§ais</span>
                    </a>


                </div>
            </div>


        </div>

        <aside class="settings-side">

            <div class="system-card">
                <h3>Informations systÃ¨me</h3>

                <div class="system-row">
                    <span>Version</span>
                    <strong>v2.1.0</strong>
                </div>

                <div class="system-row">
                    <span>Environnement</span>
                    <b class="green-pill">Production</b>
                </div>

                <div class="system-row">
                    <span>Base de donnÃ©es</span>
                    <b class="green-pill">ConnectÃ©e</b>
                </div>

                <div class="system-row">
                    <span>Utilisateurs</span>
                    <strong><?= $totalUsers ?></strong>
                </div>

                <div class="storage-bar">
                    <div></div>
                </div>

                <small>24.5% espace utilisÃ©</small>
            </div>

            <div class="system-card">
                <h3>Actions rapides</h3>

                <button class="quick-setting-btn">
                    <i data-lucide="sparkles"></i>
                    Vider le cache
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="database"></i>
                    Optimiser la base de donnÃ©es
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="mail"></i>
                    Tester les emails
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="file-text"></i>
                    GÃ©nÃ©rer un rapport systÃ¨me
                </button>
            </div>

            <div class="help-settings-card">
                <h3>Besoin dâ€™aide ?</h3>
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
