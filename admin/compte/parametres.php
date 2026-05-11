<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$totalUsers = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE deleted_at IS NULL")->fetchColumn();
$totalRoles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
$totalLogs = $pdo->query("SELECT COUNT(*) FROM security_logs")->fetchColumn();
$totalBackups = $pdo->query("SELECT COUNT(*) FROM backup_jobs")->fetchColumn();

$logs = $pdo->query("
    SELECT 
        l.action,
        l.description,
        l.created_at,
        u.prenom,
        u.nom
    FROM security_logs l
    LEFT JOIN utilisateur u ON l.mat_user = u.MAT
    ORDER BY l.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="settings-head">
        <h1>Paramètres</h1>
        <div class="breadcrumb">
            <span>Accueil</span>
            <i data-lucide="chevron-right"></i>
            <strong>Paramètres</strong>
        </div>
    </div>

    <div class="settings-tabs">
        <button class="active">Aperçu</button>
        <button>Informations établissement</button>
        <button>Année scolaire</button>
        <button>Utilisateurs & rôles</button>
        <button>Sécurité</button>
        <button>Notifications</button>
        <button>Sauvegarde</button>
        <button>Préférences</button>
    </div>

    <div class="settings-layout">

        <div class="settings-main">

            <div class="settings-overview-card">
                <h2>Aperçu des paramètres</h2>
                <p>Gérez les paramètres généraux de votre système EduManage.</p>

                <div class="settings-grid">

                    <a href="#" class="settings-box green">
                        <div><i data-lucide="school"></i></div>
                        <h3>Informations établissement</h3>
                        <p>Gérez les informations générales de votre établissement.</p>
                        <span><i data-lucide="check"></i> Configuré</span>
                    </a>

                    <a href="#" class="settings-box blue">
                        <div><i data-lucide="calendar-days"></i></div>
                        <h3>Année scolaire</h3>
                        <p>Configurez les années scolaires, semestres et périodes.</p>
                        <span><i data-lucide="calendar"></i> 2024-2025 en cours</span>
                    </a>

                    <a href="#" class="settings-box orange">
                        <div><i data-lucide="users"></i></div>
                        <h3>Utilisateurs & rôles</h3>
                        <p>Gérez les rôles et permissions des utilisateurs.</p>
                        <span><i data-lucide="users"></i> <?= $totalRoles ?> rôles définis</span>
                    </a>

                    <a href="#" class="settings-box purple">
                        <div><i data-lucide="shield-check"></i></div>
                        <h3>Sécurité</h3>
                        <p>Paramètres de sécurité et contrôle d’accès.</p>
                        <span><i data-lucide="check"></i> Sécurisé</span>
                    </a>

                    <a href="#" class="settings-box pink">
                        <div><i data-lucide="bell"></i></div>
                        <h3>Notifications</h3>
                        <p>Configurez les notifications système et email.</p>
                        <span><i data-lucide="mail"></i> Email activées</span>
                    </a>

                    <a href="#" class="settings-box cyan">
                        <div><i data-lucide="cloud-upload"></i></div>
                        <h3>Sauvegarde</h3>
                        <p>Gérez les sauvegardes et la récupération des données.</p>
                        <span><i data-lucide="check"></i> <?= $totalBackups ?> sauvegardes</span>
                    </a>

                    <a href="#" class="settings-box yellow">
                        <div><i data-lucide="sliders-horizontal"></i></div>
                        <h3>Préférences</h3>
                        <p>Personnalisez les préférences d’affichage et langue.</p>
                        <span><i data-lucide="globe"></i> Français</span>
                    </a>

                    <a href="logs.php" class="settings-box gray">
                        <div><i data-lucide="file-text"></i></div>
                        <h3>Journal d’activité</h3>
                        <p>Consultez l’historique des actions du système.</p>
                        <span><i data-lucide="list"></i> Voir les logs</span>
                    </a>

                </div>
            </div>

            <div class="activity-card">
                <h2>Activités récentes</h2>

                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Utilisateur</th>
                            <th>Date</th>
                            <th>Détails</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="dot green"></span>
                                    <?= htmlspecialchars($log["action"]) ?>
                                </td>
                                <td>
                                    <?= $log["prenom"] ? htmlspecialchars($log["prenom"] . " " . $log["nom"]) : "Système" ?>
                                </td>
                                <td><?= date("d/m/Y H:i", strtotime($log["created_at"])) ?></td>
                                <td><?= htmlspecialchars($log["description"]) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (count($logs) === 0): ?>
                            <tr>
                                <td colspan="4">Aucune activité récente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <a href="logs.php" class="view-all-activity">
                    <i data-lucide="list"></i>
                    Voir toutes les activités
                </a>
            </div>

        </div>

        <aside class="settings-side">

            <div class="system-card">
                <h3>Informations système</h3>

                <div class="system-row">
                    <span>Version</span>
                    <strong>v2.1.0</strong>
                </div>

                <div class="system-row">
                    <span>Environnement</span>
                    <b class="green-pill">Production</b>
                </div>

                <div class="system-row">
                    <span>Base de données</span>
                    <b class="green-pill">Connectée</b>
                </div>

                <div class="system-row">
                    <span>Utilisateurs</span>
                    <strong><?= $totalUsers ?></strong>
                </div>

                <div class="system-row">
                    <span>Logs sécurité</span>
                    <strong><?= $totalLogs ?></strong>
                </div>

                <div class="storage-bar">
                    <div></div>
                </div>

                <small>24.5% espace utilisé</small>
            </div>

            <div class="system-card">
                <h3>Actions rapides</h3>

                <button class="quick-setting-btn">
                    <i data-lucide="sparkles"></i>
                    Vider le cache
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="database"></i>
                    Optimiser la base de données
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="mail"></i>
                    Tester les emails
                </button>

                <button class="quick-setting-btn">
                    <i data-lucide="file-text"></i>
                    Générer un rapport système
                </button>
            </div>

            <div class="help-settings-card">
                <h3>Besoin d’aide ?</h3>
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