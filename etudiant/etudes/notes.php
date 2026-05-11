<?php
$pageTitle = 'Mes notes';
require_once __DIR__ . '/../../app/includes/auth.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/helpers/grades.php';

require_auth(['ETUDIANT']);
$etudiantNavPrefix = '../';
$navPrefix = '../';
ensure_simple_grades_schema($pdo);

$mat = $_SESSION["user"]["MAT"];

$studentStmt = $pdo->prepare("
    SELECT e.classe_id
    FROM etudiant e
    WHERE e.MAT = ?
    LIMIT 1
");
$studentStmt->execute([$mat]);
$student = $studentStmt->fetch();
$semesters = $student ? student_semesters($pdo, (int) $student["classe_id"]) : [];
$selectedSemesterId = $_GET["semestre_id"] ?? ($semesters[0]["id"] ?? "");
$semesterAverage = $selectedSemesterId && $student
    ? student_semester_average($pdo, $mat, (int) $student["classe_id"], (int) $selectedSemesterId)
    : [
        "semestre_nom" => "Semestre",
        "total_modules" => 0,
        "notes_finales" => 0,
        "complete" => false,
        "moyenne" => null,
    ];

$stmt = $pdo->prepare("
    SELECT
        n.devoir_1,
        n.devoir_2,
        n.devoir_3,
        n.note_classe,
        n.note_examen,
        n.note_finale,
        m.nom AS module_nom,
        cs.nom AS semestre_nom,
        c.nom AS classe_nom,
        c.niveau
    FROM note n
    INNER JOIN module m ON m.ID = n.module_id
    INNER JOIN etudiant e ON e.MAT = n.MAT_ET
    LEFT JOIN classe c ON c.ID = e.classe_id
    LEFT JOIN classe_semestres cs ON cs.id = n.semestre_id
    WHERE n.MAT_ET = ?
    AND (? = '' OR n.semestre_id = ?)
    ORDER BY cs.ordre ASC, m.nom ASC
");
$stmt->execute([$mat, (string) $selectedSemesterId, $selectedSemesterId]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes notes - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-etudiant.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Mes notes</h1>
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
                <small>Modules notes</small>
                <h2><?= count($notes) ?></h2>
                <span>Notes disponibles</span>
            </div>
        </div>

        <div class="student-stat-card blue">
            <div class="icon"><i data-lucide="percent"></i></div>
            <div>
                <small>Moyenne generale - <?= htmlspecialchars($semesterAverage["semestre_nom"]) ?></small>
                <h2><?= $semesterAverage["complete"] ? number_format((float) $semesterAverage["moyenne"], 2) : "-" ?></h2>
                <span><?= htmlspecialchars($semesterAverage["notes_finales"] . "/" . $semesterAverage["total_modules"]) ?> notes finales</span>
            </div>
        </div>
    </div>

    <div class="students-table-card">
        <div class="students-filters">
            <form method="GET" class="search-form">
                <select name="semestre_id">
                    <?php foreach ($semesters as $semestre): ?>
                        <option value="<?= htmlspecialchars($semestre["id"]) ?>" <?= (string) $selectedSemesterId === (string) $semestre["id"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($semestre["nom"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="filter-btn"><i data-lucide="filter"></i> Charger</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Semestre</th>
                        <th>Module</th>
                        <th>Devoir 1</th>
                        <th>Devoir 2</th>
                        <th>Devoir 3</th>
                        <th>Note classe</th>
                        <th>Examen</th>
                        <th>Note finale</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($notes) === 0): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i data-lucide="chart-no-axes-column"></i>
                                    <h3>Aucune note disponible</h3>
                                    <p>Vos notes apparaitront ici apres la saisie.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($notes as $index => $note): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($note["semestre_nom"] ?? "-") ?></td>
                            <td><strong><?= htmlspecialchars($note["module_nom"]) ?></strong></td>
                            <td><?= $note["devoir_1"] !== null ? htmlspecialchars($note["devoir_1"]) : "-" ?></td>
                            <td><?= $note["devoir_2"] !== null ? htmlspecialchars($note["devoir_2"]) : "-" ?></td>
                            <td><?= $note["devoir_3"] !== null ? htmlspecialchars($note["devoir_3"]) : "-" ?></td>
                            <td><span class="badge"><?= $note["note_classe"] !== null ? htmlspecialchars($note["note_classe"]) : "-" ?></span></td>
                            <td><?= $note["note_examen"] !== null ? htmlspecialchars($note["note_examen"]) : "-" ?></td>
                            <td>
                                <?php if ($note["note_finale"] !== null): ?>
                                    <span class="status active"><?= htmlspecialchars($note["note_finale"]) ?>/20</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
