<?php
require_once __DIR__ . '/../../app/includes/auth.php';
$adminNavPrefix = '../';
$navPrefix = '../';
require_auth(['SUPER_ADMIN', 'ADMIN']);

$search = trim($_GET["search"] ?? "");
$moduleId = $_GET["module_id"] ?? "";
$classeId = $_GET["classe_id"] ?? "";

$modules = $pdo->query("SELECT ID, nom FROM module ORDER BY nom ASC")->fetchAll();
$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();

$query = "
    SELECT
        n.ID,
        n.valeur,
        n.poids,
        n.penalite,
        n.MAT_ET,
        u.nom,
        u.prenom,
        m.nom AS module_nom,
        c.nom AS classe_nom,
        c.niveau
    FROM note n
    INNER JOIN etudiant e ON e.MAT = n.MAT_ET
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    INNER JOIN module m ON m.ID = n.module_id
    LEFT JOIN classe c ON c.ID = e.classe_id
    WHERE u.deleted_at IS NULL
";

$params = [];

if ($search !== "") {
    $query .= " AND (u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR n.MAT_ET LIKE :search)";
    $params["search"] = "%$search%";
}

if ($moduleId !== "") {
    $query .= " AND n.module_id = :module_id";
    $params["module_id"] = $moduleId;
}

if ($classeId !== "") {
    $query .= " AND e.classe_id = :classe_id";
    $params["classe_id"] = $classeId;
}

$query .= " ORDER BY u.prenom ASC, u.nom ASC, m.nom ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notes = $stmt->fetchAll();

$totalNotes = count($notes);
$moyenne = 0;

if ($totalNotes > 0) {
    $moyenne = array_sum(array_column($notes, "valeur")) / $totalNotes;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="students-header">
            <div>
                <h1>Notes</h1>
                <div class="breadcrumb">
                    <span>Accueil</span>
                    <i data-lucide="chevron-right"></i>
                    <strong>Notes</strong>
                </div>
            </div>
        </div>

        <div class="students-stats">
            <div class="student-stat-card green">
                <div class="icon"><i data-lucide="chart-no-axes-column"></i></div>
                <div>
                    <small>Total notes</small>
                    <h2><?= $totalNotes ?></h2>
                    <span>Notes enregistrees</span>
                </div>
            </div>

            <div class="student-stat-card blue">
                <div class="icon"><i data-lucide="percent"></i></div>
                <div>
                    <small>Moyenne</small>
                    <h2><?= number_format($moyenne, 2) ?></h2>
                    <span>Sur 20</span>
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
                            placeholder="Rechercher par etudiant, matricule, email..."
                            value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>

                    <select name="module_id">
                        <option value="">Tous les modules</option>
                        <?php foreach ($modules as $module): ?>
                            <option value="<?= htmlspecialchars($module["ID"]) ?>" <?= (string) $moduleId === (string) $module["ID"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($module["nom"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="classe_id">
                        <option value="">Toutes les classes</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeId === (string) $classe["ID"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                            </option>
                        <?php endforeach; ?>
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
                            <th>Etudiant</th>
                            <th>Classe</th>
                            <th>Module</th>
                            <th>Note</th>
                            <th>Poids</th>
                            <th>Penalite</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($notes) > 0): ?>
                            <?php foreach ($notes as $index => $note): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($note["MAT_ET"]) ?></td>
                                    <td><strong><?= htmlspecialchars($note["prenom"] . " " . $note["nom"]) ?></strong></td>
                                    <td><?= htmlspecialchars(trim(($note["classe_nom"] ?? "-") . " " . ($note["niveau"] ?? ""))) ?></td>
                                    <td><?= htmlspecialchars($note["module_nom"]) ?></td>
                                    <td><span class="status active"><?= htmlspecialchars($note["valeur"]) ?>/20</span></td>
                                    <td><?= $note["poids"] !== null ? htmlspecialchars($note["poids"]) . "%" : "-" ?></td>
                                    <td><?= $note["penalite"] ? "Oui" : "Non" ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i data-lucide="chart-no-axes-column"></i>
                                        <h3>Aucune note trouvee</h3>
                                        <p>Aucune note ne correspond a votre recherche.</p>
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
