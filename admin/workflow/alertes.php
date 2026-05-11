<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

$adminNavPrefix = '../';
$navPrefix = '../';
require_auth(["SUPER_ADMIN", "ADMIN"]);
$currentMat = $_SESSION["user"]["MAT"] ?? null;

$alerts = $pdo->query("
    SELECT 
        a.id,
        a.type,
        a.severity,
        a.title,
        a.message,
        a.is_read,
        a.created_at,
        u.nom,
        u.prenom
    FROM admin_alerts a
    LEFT JOIN utilisateur u ON a.target_mat_user = u.MAT
    WHERE a.target_mat_user IS NULL OR a.target_mat_user = " . $pdo->quote($currentMat) . "
    ORDER BY a.created_at DESC
")->fetchAll();

$totalAlerts = count($alerts);
$unreadAlerts = count(array_filter($alerts, fn($a) => !$a["is_read"]));
$highAlerts = count(array_filter($alerts, fn($a) => $a["severity"] === "high"));
$mediumAlerts = count(array_filter($alerts, fn($a) => $a["severity"] === "medium"));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Alertes - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="alerts-head">
        <div>
            <h1>Alertes et notifications</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Alertes</strong>
            </div>
        </div>

        <button class="mark-all-btn">
            <i data-lucide="check-check"></i>
            Tout marquer comme lu
        </button>
    </div>

    <div class="alerts-stats">
        <div class="alert-stat green">
            <div><i data-lucide="bell"></i></div>
            <article>
                <h2><?= $totalAlerts ?></h2>
                <p>Total alertes</p>
                <small>Toutes les notifications</small>
            </article>
        </div>

        <div class="alert-stat orange">
            <div><i data-lucide="mail-warning"></i></div>
            <article>
                <h2><?= $unreadAlerts ?></h2>
                <p>Non lues</p>
                <small>À consulter</small>
            </article>
        </div>

        <div class="alert-stat red">
            <div><i data-lucide="triangle-alert"></i></div>
            <article>
                <h2><?= $highAlerts ?></h2>
                <p>Critiques</p>
                <small>Priorité élevée</small>
            </article>
        </div>

        <div class="alert-stat blue">
            <div><i data-lucide="info"></i></div>
            <article>
                <h2><?= $mediumAlerts ?></h2>
                <p>Moyennes</p>
                <small>Suivi recommandé</small>
            </article>
        </div>
    </div>

    <div class="alerts-layout">

        <div class="alerts-main-card">
            <div class="alerts-card-head">
                <div>
                    <h2>Liste des alertes</h2>
                    <p>Consultez les événements importants du système.</p>
                </div>

                <div class="alerts-filter">
                    <select>
                        <option>Toutes les alertes</option>
                        <option>Critiques</option>
                        <option>Moyennes</option>
                        <option>Faibles</option>
                    </select>

                    <button>
                        <i data-lucide="filter"></i>
                        Filtrer
                    </button>
                </div>
            </div>

            <div class="alerts-list">
                <?php foreach ($alerts as $alert): ?>
                    <?php
                        $icon = "info";
                        $class = "low";

                        if ($alert["severity"] === "high") {
                            $icon = "triangle-alert";
                            $class = "high";
                        } elseif ($alert["severity"] === "medium") {
                            $icon = "circle-alert";
                            $class = "medium";
                        }
                    ?>

                    <div class="alert-row <?= $alert["is_read"] ? "" : "unread" ?>">
                        <div class="alert-icon <?= $class ?>">
                            <i data-lucide="<?= $icon ?>"></i>
                        </div>

                        <div class="alert-content">
                            <div class="alert-title-line">
                                <h3><?= htmlspecialchars($alert["title"]) ?></h3>

                                <?php if (!$alert["is_read"]): ?>
                                    <span class="unread-badge">Nouveau</span>
                                <?php endif; ?>
                            </div>

                            <p><?= htmlspecialchars($alert["message"]) ?></p>

                            <div class="alert-meta">
                                <span>
                                    <i data-lucide="tag"></i>
                                    <?= htmlspecialchars($alert["type"]) ?>
                                </span>

                                <span>
                                    <i data-lucide="clock"></i>
                                    <?= date("d/m/Y H:i", strtotime($alert["created_at"])) ?>
                                </span>

                                <?php if ($alert["prenom"]): ?>
                                    <span>
                                        <i data-lucide="user"></i>
                                        <?= htmlspecialchars($alert["prenom"] . " " . $alert["nom"]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="alert-actions">
                            <button title="Voir">
                                <i data-lucide="eye"></i>
                            </button>

                            <button title="Marquer comme lu">
                                <i data-lucide="check"></i>
                            </button>

                            <button title="Supprimer">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (count($alerts) === 0): ?>
                    <div class="empty-state">
                        <i data-lucide="bell-off"></i>
                        <h3>Aucune alerte</h3>
                        <p>Le système ne contient aucune alerte pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="alerts-side">

            <div class="alerts-side-card">
                <h3>Résumé</h3>

                <div class="alert-summary-row">
                    <span>Alertes critiques</span>
                    <b class="red-pill"><?= $highAlerts ?></b>
                </div>

                <div class="alert-summary-row">
                    <span>Alertes moyennes</span>
                    <b class="orange-pill"><?= $mediumAlerts ?></b>
                </div>

                <div class="alert-summary-row">
                    <span>Alertes non lues</span>
                    <b class="green-pill"><?= $unreadAlerts ?></b>
                </div>

                <div class="alert-summary-row">
                    <span>Total système</span>
                    <strong><?= $totalAlerts ?></strong>
                </div>
            </div>

            <div class="alerts-side-card">
                <h3>Types d’alertes</h3>

                <div class="alert-type-item">
                    <i data-lucide="user-plus"></i>
                    <span>Inscriptions</span>
                </div>

                <div class="alert-type-item">
                    <i data-lucide="shield-alert"></i>
                    <span>Sécurité</span>
                </div>

                <div class="alert-type-item">
                    <i data-lucide="database"></i>
                    <span>Sauvegarde</span>
                </div>

                <div class="alert-type-item">
                    <i data-lucide="file-warning"></i>
                    <span>Académique</span>
                </div>
            </div>

            <div class="alerts-help-card">
                <h3>Conseil</h3>
                <p>
                    Consultez régulièrement les alertes critiques afin de garder le système sécurisé et stable.
                </p>

                <button>
                    <i data-lucide="shield-check"></i>
                    Vérifier la sécurité
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