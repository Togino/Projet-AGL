<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);

$search = trim($_GET["search"] ?? "");
$statut = $_GET["statut"] ?? "";

$query = "
    SELECT
        e.MAT,
        e.specialisation,
        u.nom,
        u.prenom,
        u.email,
        u.statut,
        u.created_at,
        COUNT(DISTINCT a.module_id) AS total_modules,
        COUNT(DISTINCT a.classe_id) AS total_classes
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    LEFT JOIN enseignement_affectation a ON a.MAT_enseignant = e.MAT
    WHERE u.deleted_at IS NULL
";

$params = [];

if ($search !== "") {
    $query .= " AND (u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR e.MAT LIKE :search OR e.specialisation LIKE :search)";
    $params["search"] = "%$search%";
}

if ($statut !== "" && in_array($statut, ["0", "1"], true)) {
    $query .= " AND u.statut = :statut";
    $params["statut"] = $statut;
}

$query .= "
    GROUP BY e.MAT, e.specialisation, u.nom, u.prenom, u.email, u.statut, u.created_at
    ORDER BY u.prenom ASC, u.nom ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$enseignants = $stmt->fetchAll();

$total = count($enseignants);
$actifs = 0;
$inactifs = 0;
$totalModules = 0;

foreach ($enseignants as $enseignant) {
    if ((int) $enseignant["statut"] === 1) {
        $actifs++;
    } else {
        $inactifs++;
    }

    $totalModules += (int) $enseignant["total_modules"];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enseignants - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">

<?php include "../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
    <?php include "../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="students-header">
            <div>
                <h1>Enseignants</h1>
                <div class="breadcrumb">
                    <span>Accueil</span>
                    <i data-lucide="chevron-right"></i>
                    <strong>Enseignants</strong>
                </div>
            </div>
        </div>

        <div class="students-stats">
            <div class="student-stat-card green">
                <div class="icon"><i data-lucide="graduation-cap"></i></div>
                <div>
                    <small>Total enseignants</small>
                    <h2><?= $total ?></h2>
                    <span>Personnel pedagogique</span>
                </div>
            </div>

            <div class="student-stat-card blue">
                <div class="icon"><i data-lucide="shield-check"></i></div>
                <div>
                    <small>Actifs</small>
                    <h2><?= $actifs ?></h2>
                    <span>Comptes actifs</span>
                </div>
            </div>

            <div class="student-stat-card orange">
                <div class="icon"><i data-lucide="user-x"></i></div>
                <div>
                    <small>Inactifs</small>
                    <h2><?= $inactifs ?></h2>
                    <span>Comptes inactifs</span>
                </div>
            </div>

            <div class="student-stat-card pink">
                <div class="icon"><i data-lucide="book-open"></i></div>
                <div>
                    <small>Modules affectes</small>
                    <h2><?= $totalModules ?></h2>
                    <span>Total des affectations</span>
                </div>
            </div>
        </div>

        <div class="students-table-card">
            <div class="students-filters">
                <form method="GET" class="search-form">
                    <div class="search-input">
                        <i data-lucide="search"></i>
                        <input
                            type="text"
                            name="search"
                            placeholder="Rechercher par nom, matricule, email, specialisation..."
                            value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>

                    <select name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="1" <?= $statut === "1" ? "selected" : "" ?>>Actifs</option>
                        <option value="0" <?= $statut === "0" ? "selected" : "" ?>>Inactifs</option>
                    </select>

                    <button type="submit" class="filter-btn">
                        <i data-lucide="filter"></i>
                        Filtres
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Matricule</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Specialisation</th>
                            <th>Modules</th>
                            <th>Classes</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($enseignants) > 0): ?>
                            <?php foreach ($enseignants as $index => $enseignant): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($enseignant["MAT"]) ?></td>
                                    <td><strong><?= htmlspecialchars($enseignant["prenom"] . " " . $enseignant["nom"]) ?></strong></td>
                                    <td><?= htmlspecialchars($enseignant["email"]) ?></td>
                                    <td><?= htmlspecialchars($enseignant["specialisation"] ?: "Non renseignee") ?></td>
                                    <td><?= htmlspecialchars($enseignant["total_modules"]) ?></td>
                                    <td><?= htmlspecialchars($enseignant["total_classes"]) ?></td>
                                    <td>
                                        <?php if ($enseignant["statut"]): ?>
                                            <span class="status active">Actif</span>
                                        <?php else: ?>
                                            <span class="status inactive">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($enseignant["created_at"]) ? date("d/m/Y", strtotime($enseignant["created_at"])) : "-" ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i data-lucide="graduation-cap"></i>
                                        <h3>Aucun enseignant trouve</h3>
                                        <p>Aucun enseignant ne correspond a votre recherche.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script>
lucide.createIcons();
</script>

</body>
</html>
