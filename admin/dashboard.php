<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

function scalar(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function trend_text(int $current, int $previous, string $noun): string
{
    if ($previous === 0) {
        return $current > 0 ? "+" . $current . " " . $noun . " ce mois" : "Aucun nouveau ce mois";
    }

    $percent = round((($current - $previous) / $previous) * 100, 1);
    $prefix = $percent > 0 ? "+" : "";
    return $prefix . $percent . "% vs mois dernier";
}

function percent(int $part, int $total): int
{
    return $total > 0 ? (int) round(($part / $total) * 100) : 0;
}

$totalEtudiants = scalar($pdo, "
    SELECT COUNT(*)
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
");
$totalEnseignants = scalar($pdo, "
    SELECT COUNT(*)
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
");
$totalClasses = scalar($pdo, "SELECT COUNT(*) FROM classe");
$totalModules = scalar($pdo, "SELECT COUNT(*) FROM module");
$nouveauxEtudiants = scalar($pdo, "
    SELECT COUNT(*)
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
    AND u.created_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
");
$nouveauxEtudiantsPrecedent = scalar($pdo, "
    SELECT COUNT(*)
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
    AND u.created_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01')
    AND u.created_at < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
");
$nouveauxEnseignants = scalar($pdo, "
    SELECT COUNT(*)
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
    AND u.created_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
");
$nouveauxEnseignantsPrecedent = scalar($pdo, "
    SELECT COUNT(*)
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
    AND u.created_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01')
    AND u.created_at < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
");
$classesAvecEtudiants = scalar($pdo, "SELECT COUNT(DISTINCT classe_id) FROM etudiant");
$modulesAffectes = scalar($pdo, "SELECT COUNT(DISTINCT module_id) FROM enseignement_affectation");

$anneeScolaire = $pdo->query("
    SELECT annee_scolaire
    FROM (
        SELECT annee_scolaire FROM enseignement_affectation
        UNION ALL
        SELECT annee_scolaire FROM classe_semestres
    ) annees
    WHERE annee_scolaire IS NOT NULL AND annee_scolaire <> ''
    ORDER BY annee_scolaire DESC
    LIMIT 1
")->fetchColumn();

if (!$anneeScolaire) {
    $year = (int) date("Y");
    $startYear = (int) date("n") >= 9 ? $year : $year - 1;
    $anneeScolaire = $startYear . "-" . ($startYear + 1);
}

$studentDistribution = $pdo->query("
    SELECT COALESCE(NULLIF(c.niveau, ''), c.nom, 'Sans classe') AS label, COUNT(e.MAT) AS total
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    LEFT JOIN classe c ON c.ID = e.classe_id
    WHERE u.deleted_at IS NULL
    GROUP BY label
    ORDER BY total DESC, label ASC
    LIMIT 4
")->fetchAll();

$studentDistributionTotal = array_sum(array_map(fn($row) => (int) $row["total"], $studentDistribution));
$distributionColors = ["#157a3d", "#1fa855", "#0d4f2a", "#d9b94f"];
$distributionStops = [];
$cursor = 0;
foreach ($studentDistribution as $index => $row) {
    $share = $studentDistributionTotal > 0 ? ((int) $row["total"] / $studentDistributionTotal) * 100 : 0;
    $next = $index === count($studentDistribution) - 1 ? 100 : $cursor + $share;
    $color = $distributionColors[$index % count($distributionColors)];
    $distributionStops[] = $color . " " . round($cursor, 2) . "% " . round($next, 2) . "%";
    $cursor = $next;
}
$distributionGradient = count($distributionStops) > 0
    ? "conic-gradient(" . implode(", ", $distributionStops) . ")"
    : "conic-gradient(#dfe8e2 0 100%)";

$months = [];
$monthLabels = ["Jan", "Fev", "Mar", "Avr", "Mai", "Juin", "Juil", "Aout", "Sept", "Oct", "Nov", "Dec"];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime("first day of this month");
    $date->modify("-$i month");
    $months[$date->format("Y-m")] = [
        "label" => $monthLabels[(int) $date->format("n") - 1],
        "total" => 0,
    ];
}

$monthlyStmt = $pdo->prepare("
    SELECT DATE_FORMAT(u.created_at, '%Y-%m') AS month_key, COUNT(*) AS total
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL
    AND u.created_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 5 MONTH, '%Y-%m-01')
    GROUP BY month_key
");
$monthlyStmt->execute();
foreach ($monthlyStmt->fetchAll() as $row) {
    if (isset($months[$row["month_key"]])) {
        $months[$row["month_key"]]["total"] = (int) $row["total"];
    }
}
$maxMonthlyStudents = max(1, ...array_column($months, "total"));

$userStatus = [
    "active" => scalar($pdo, "SELECT COUNT(*) FROM utilisateur WHERE deleted_at IS NULL AND statut = 1"),
    "inactive" => scalar($pdo, "SELECT COUNT(*) FROM utilisateur WHERE deleted_at IS NULL AND statut = 0"),
    "deleted" => scalar($pdo, "SELECT COUNT(*) FROM utilisateur WHERE deleted_at IS NOT NULL"),
];
$totalUsersStatus = array_sum($userStatus);
$activePercent = percent($userStatus["active"], $totalUsersStatus);
$inactivePercent = percent($userStatus["inactive"], $totalUsersStatus);
$deletedPercent = max(0, 100 - $activePercent - $inactivePercent);
$inactiveStart = $activePercent + $inactivePercent;
$userGradient = "conic-gradient(#157a3d 0 {$activePercent}%, #d9b94f {$activePercent}% {$inactiveStart}%, #ff6b6b {$inactiveStart}% 100%)";

$recentUsers = $pdo->query("
    SELECT u.MAT, u.nom, u.prenom, u.email, u.created_at, r.name AS role
    FROM utilisateur u
    INNER JOIN roles r ON r.id = u.role_id
    WHERE u.deleted_at IS NULL
    ORDER BY u.created_at DESC
    LIMIT 4
")->fetchAll();

$topClasses = $pdo->query("
    SELECT c.nom, c.niveau, COUNT(u.MAT) AS total
    FROM classe c
    LEFT JOIN etudiant e ON e.classe_id = c.ID
    LEFT JOIN utilisateur u ON u.MAT = e.MAT AND u.deleted_at IS NULL
    GROUP BY c.ID, c.nom, c.niveau
    ORDER BY total DESC, c.nom ASC
    LIMIT 3
")->fetchAll();

$alerts = $pdo->query("
    SELECT title, message, severity, created_at
    FROM admin_alerts
    ORDER BY created_at DESC
    LIMIT 3
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../app/includes/sidebar-admin.php"; ?>

<main class="main-content">

    <?php include "../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Tableau de bord</h1>
                <p>Bienvenue, Admin ! Voici un apercu general de votre etablissement.</p>
            </div>

            <button class="year-btn"><?= ui_icon("calendar") ?> Annee scolaire <?= htmlspecialchars($anneeScolaire) ?> <?= ui_icon("chevron-down") ?></button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><?= ui_icon("users") ?></div>
                <div>
                    <p>Etudiants</p>
                    <h2><?= $totalEtudiants ?></h2>
                    <span><?= htmlspecialchars(trend_text($nouveauxEtudiants, $nouveauxEtudiantsPrecedent, "etudiant")) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><?= ui_icon("graduation") ?></div>
                <div>
                    <p>Enseignants</p>
                    <h2><?= $totalEnseignants ?></h2>
                    <span><?= htmlspecialchars(trend_text($nouveauxEnseignants, $nouveauxEnseignantsPrecedent, "enseignant")) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><?= ui_icon("school") ?></div>
                <div>
                    <p>Classes</p>
                    <h2><?= $totalClasses ?></h2>
                    <span><?= $classesAvecEtudiants ?> avec etudiants</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><?= ui_icon("book") ?></div>
                <div>
                    <p>Modules</p>
                    <h2><?= $totalModules ?></h2>
                    <span><?= $modulesAffectes ?> affectes</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><?= ui_icon("clipboard") ?></div>
                <div>
                    <p>Nouveaux etudiants</p>
                    <h2><?= $nouveauxEtudiants ?></h2>
                    <span>Crees ce mois</span>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card chart-card">
                <h3>Repartition des etudiants</h3>
                <div class="donut-area">
                    <div class="donut" style="background: <?= htmlspecialchars($distributionGradient) ?>;">
                        <div>
                            <strong><?= $totalEtudiants ?></strong>
                            <small>Etudiants</small>
                        </div>
                    </div>

                    <ul class="legend">
                        <?php if (count($studentDistribution) > 0): ?>
                            <?php foreach ($studentDistribution as $index => $row): ?>
                                <li>
                                    <span style="background: <?= htmlspecialchars($distributionColors[$index % count($distributionColors)]) ?>;"></span>
                                    <?= htmlspecialchars($row["label"]) ?>
                                    <b><?= percent((int) $row["total"], $studentDistributionTotal) ?>%</b>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span></span> Aucune donnee <b>0%</b></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <a href="etudiants.php" class="card-link">Voir le detail -></a>
            </div>

            <div class="card line-card">
                <h3>Evolution des nouveaux etudiants</h3>
                <div class="fake-line-chart">
                    <?php foreach ($months as $month): ?>
                        <?php $height = max(8, (int) round(($month["total"] / $maxMonthlyStudents) * 100)); ?>
                        <span style="height:<?= $height ?>%" title="<?= htmlspecialchars($month["label"]) ?> : <?= $month["total"] ?>"></span>
                    <?php endforeach; ?>
                </div>
                <div class="months">
                    <?php foreach ($months as $month): ?>
                        <small><?= htmlspecialchars($month["label"]) ?></small>
                    <?php endforeach; ?>
                </div>
                <a href="etudiants.php" class="card-link">Voir les etudiants -></a>
            </div>

            <div class="card chart-card">
                <h3>Statut des utilisateurs</h3>
                <div class="donut-area">
                    <div class="donut user-donut" style="background: <?= htmlspecialchars($userGradient) ?>;">
                        <div>
                            <strong><?= $totalUsersStatus ?></strong>
                            <small>Comptes</small>
                        </div>
                    </div>
                    <ul class="legend">
                        <li><span></span> Actifs <b><?= $activePercent ?>%</b></li>
                        <li><span style="background:#d9b94f;"></span> Inactifs <b><?= $inactivePercent ?>%</b></li>
                        <li><span class="danger"></span> Supprimes <b><?= $deletedPercent ?>%</b></li>
                    </ul>
                </div>
                <a href="utilisateurs.php" class="card-link">Voir tous les utilisateurs -></a>
            </div>

            <div class="card">
                <h3>Utilisateurs recents</h3>

                <?php foreach ($recentUsers as $user): ?>
                    <div class="list-item">
                        <div class="mini-avatar"><?= ui_icon("user") ?></div>
                        <div>
                            <strong><?= htmlspecialchars($user["prenom"] . " " . $user["nom"]) ?></strong>
                            <small><?= htmlspecialchars($user["email"]) ?></small>
                        </div>
                        <span><?= htmlspecialchars($user["role"]) ?></span>
                    </div>
                <?php endforeach; ?>

                <a href="utilisateurs.php" class="card-link">Voir tous les utilisateurs -></a>
            </div>

            <div class="card">
                <h3>Classes les plus remplies</h3>

                <?php if (count($topClasses) > 0): ?>
                    <?php foreach ($topClasses as $class): ?>
                        <div class="event-item">
                            <div class="date-box"><strong><?= (int) $class["total"] ?></strong><small>ETU</small></div>
                            <div>
                                <strong><?= htmlspecialchars($class["nom"]) ?></strong>
                                <small><?= htmlspecialchars($class["niveau"] ?: "Niveau non renseigne") ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="event-item">
                        <div class="date-box"><strong>0</strong><small>ETU</small></div>
                        <div>
                            <strong>Aucune classe enregistree</strong>
                            <small>Les effectifs apparaitront apres creation des classes</small>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="classes.php" class="card-link">Voir toutes les classes -></a>
            </div>

            <div class="card">
                <h3>Alertes et notifications</h3>

                <?php if (count($alerts) > 0): ?>
                    <?php foreach ($alerts as $alert): ?>
                        <div class="alert-item">
                            <div class="alert-icon"><?= ui_icon("alert") ?></div>
                            <div>
                                <strong><?= htmlspecialchars($alert["title"]) ?></strong>
                                <small><?= htmlspecialchars($alert["message"]) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert-item">
                        <div class="alert-icon"><?= ui_icon("check") ?></div>
                        <div>
                            <strong>Aucune alerte critique</strong>
                            <small>Le systeme fonctionne normalement</small>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="alertes.php" class="card-link">Voir toutes les alertes -></a>
            </div>
        </div>
    </section>
</main>

</body>
</html>
