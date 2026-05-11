<?php
require_once __DIR__ . '/../../app/includes/auth.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/helpers/grades.php';

require_auth(['GESTIONNAIRE']);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
ensure_simple_grades_schema($pdo);

$mat = $_SESSION["user"]["MAT"];
$success = "";
$error = "";

$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();
$semesterRows = $pdo->query("
    SELECT id, classe_id, nom, ordre
    FROM classe_semestres
    ORDER BY classe_id ASC, ordre ASC
")->fetchAll();
$classeModuleRows = $pdo->query("
    SELECT cm.classe_id, cm.semestre_id, m.ID, m.nom
    FROM classe_modules cm
    INNER JOIN module m ON m.ID = cm.module_id
    ORDER BY cm.classe_id ASC, cm.semestre ASC, m.nom ASC
")->fetchAll();

$semestersByClass = [];
foreach ($semesterRows as $row) {
    $semestersByClass[(string) $row["classe_id"]][(string) $row["id"]] = [
        "id" => $row["id"],
        "nom" => $row["nom"],
        "ordre" => $row["ordre"],
    ];
}

$modulesByClassSemester = [];
foreach ($classeModuleRows as $row) {
    $modulesByClassSemester[(string) $row["classe_id"]][(string) $row["semestre_id"]][] = ["ID" => $row["ID"], "nom" => $row["nom"]];
}

$classeId = $_GET["classe_id"] ?? ($_POST["classe_id"] ?? ($classes[0]["ID"] ?? ""));
$semestreId = $_GET["semestre_id"] ?? ($_POST["semestre_id"] ?? array_key_first($semestersByClass[(string) $classeId] ?? []));
$availableModules = $modulesByClassSemester[(string) $classeId][(string) $semestreId] ?? [];
$moduleId = $_GET["module_id"] ?? ($_POST["module_id"] ?? ($availableModules[0]["ID"] ?? ""));

if ($classeId !== "" && !isset($semestersByClass[(string) $classeId][(string) $semestreId])) {
    $semestreId = array_key_first($semestersByClass[(string) $classeId] ?? []);
    $availableModules = $modulesByClassSemester[(string) $classeId][(string) $semestreId] ?? [];
    $moduleId = $availableModules[0]["ID"] ?? "";
}

if ($classeId !== "" && $moduleId !== "") {
    $allowedModuleIds = array_map("strval", array_column($availableModules, "ID"));
    if (!in_array((string) $moduleId, $allowedModuleIds, true)) {
        $moduleId = $availableModules[0]["ID"] ?? "";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "save_grades") {
    $studentsStmt = $pdo->prepare("SELECT MAT FROM etudiant WHERE classe_id = ?");
    $studentsStmt->execute([$classeId]);
    $allowedStudents = array_column($studentsStmt->fetchAll(), "MAT");
    $saved = 0;

    foreach ($allowedStudents as $studentMat) {
        $rawValues = [
            "devoir_1" => $_POST["devoir_1"][$studentMat] ?? "",
            "devoir_2" => $_POST["devoir_2"][$studentMat] ?? "",
            "devoir_3" => $_POST["devoir_3"][$studentMat] ?? "",
            "note_examen" => $_POST["note_examen"][$studentMat] ?? "",
        ];

        if (implode("", $rawValues) === "") {
            continue;
        }

        $values = [];
        foreach ($rawValues as $key => $rawValue) {
            $values[$key] = grade_number($rawValue);

            if ($rawValue !== "" && $values[$key] === null) {
                $error = "Les notes saisies doivent etre comprises entre 0 et 20.";
                break 2;
            }
        }

        save_simple_grade($pdo, $studentMat, (int) $moduleId, $semestreId !== null ? (int) $semestreId : null, $values, $mat);
        $saved++;
    }

    if ($error === "") {
        $success = $saved > 0 ? "Notes enregistrees avec succes." : "Aucune note a enregistrer.";
    }
}

$selectedClasse = null;
foreach ($classes as $classe) {
    if ((string) $classe["ID"] === (string) $classeId) {
        $selectedClasse = $classe;
        break;
    }
}

$selectedSemester = $semestersByClass[(string) $classeId][(string) $semestreId] ?? null;
$selectedModule = null;
foreach ($availableModules as $module) {
    if ((string) $module["ID"] === (string) $moduleId) {
        $selectedModule = $module;
        break;
    }
}

$students = [];
if ($selectedClasse && $selectedSemester && $selectedModule) {
    $studentsStmt = $pdo->prepare("
        SELECT e.MAT, u.nom, u.prenom
        FROM etudiant e
        INNER JOIN utilisateur u ON u.MAT = e.MAT
        WHERE e.classe_id = ?
        AND u.deleted_at IS NULL
        ORDER BY u.prenom ASC, u.nom ASC
    ");
    $studentsStmt->execute([$classeId]);
    $students = $studentsStmt->fetchAll();
}

$gradesByStudent = $moduleId !== "" ? existing_grades_by_student($pdo, (int) $moduleId, $semestreId !== null ? (int) $semestreId : null) : [];
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
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Notes</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Saisie et correction</strong>
            </div>
        </div>
    </div>

    <?php if ($success): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="students-table-card">
        <div class="students-filters">
            <form method="GET" class="search-form">
                <select name="classe_id" onchange="this.form.submit()">
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeId === (string) $classe["ID"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="semestre_id" onchange="this.form.submit()">
                    <?php foreach ($semestersByClass[(string) $classeId] ?? [] as $semestre): ?>
                        <option value="<?= htmlspecialchars($semestre["id"]) ?>" <?= (string) $semestreId === (string) $semestre["id"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($semestre["nom"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="module_id">
                    <?php foreach ($availableModules as $module): ?>
                        <option value="<?= htmlspecialchars($module["ID"]) ?>" <?= (string) $moduleId === (string) $module["ID"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($module["nom"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="filter-btn"><i data-lucide="filter"></i> Charger</button>
            </form>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="save_grades">
            <input type="hidden" name="classe_id" value="<?= htmlspecialchars((string) $classeId) ?>">
            <input type="hidden" name="semestre_id" value="<?= htmlspecialchars((string) $semestreId) ?>">
            <input type="hidden" name="module_id" value="<?= htmlspecialchars((string) $moduleId) ?>">

            <div class="table-responsive">
                <table class="students-table grades-entry-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Matricule</th>
                            <th>Etudiant</th>
                            <th>Devoir 1</th>
                            <th>Devoir 2</th>
                            <th>Devoir 3</th>
                            <th>Note classe</th>
                            <th>Examen</th>
                            <th>Note finale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) === 0): ?>
                            <tr><td colspan="9">Aucun etudiant disponible pour cette selection.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($students as $index => $student): ?>
                            <?php $grade = $gradesByStudent[$student["MAT"]] ?? []; ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student["MAT"]) ?></td>
                                <td><strong><?= htmlspecialchars($student["prenom"] . " " . $student["nom"]) ?></strong></td>
                                <td><input class="grade-input" data-student="<?= htmlspecialchars($student["MAT"]) ?>" data-field="devoir_1" type="number" min="0" max="20" step="0.01" name="devoir_1[<?= htmlspecialchars($student["MAT"]) ?>]" value="<?= htmlspecialchars((string) ($grade["devoir_1"] ?? "")) ?>"></td>
                                <td><input class="grade-input" data-student="<?= htmlspecialchars($student["MAT"]) ?>" data-field="devoir_2" type="number" min="0" max="20" step="0.01" name="devoir_2[<?= htmlspecialchars($student["MAT"]) ?>]" value="<?= htmlspecialchars((string) ($grade["devoir_2"] ?? "")) ?>"></td>
                                <td><input class="grade-input" data-student="<?= htmlspecialchars($student["MAT"]) ?>" data-field="devoir_3" type="number" min="0" max="20" step="0.01" name="devoir_3[<?= htmlspecialchars($student["MAT"]) ?>]" value="<?= htmlspecialchars((string) ($grade["devoir_3"] ?? "")) ?>"></td>
                                <td><span class="status active class-score" data-student="<?= htmlspecialchars($student["MAT"]) ?>"><?= isset($grade["note_classe"]) ? htmlspecialchars($grade["note_classe"]) : "-" ?></span></td>
                                <td><input class="grade-input" data-student="<?= htmlspecialchars($student["MAT"]) ?>" data-field="note_examen" type="number" min="0" max="20" step="0.01" name="note_examen[<?= htmlspecialchars($student["MAT"]) ?>]" value="<?= htmlspecialchars((string) ($grade["note_examen"] ?? "")) ?>"></td>
                                <td><strong class="final-score" data-student="<?= htmlspecialchars($student["MAT"]) ?>"><?= isset($grade["note_finale"]) ? htmlspecialchars($grade["note_finale"]) . "/20" : "-" ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (count($students) > 0): ?>
                <button type="submit" class="submit-btn">Enregistrer les modifications</button>
            <?php endif; ?>
        </form>
    </div>
</section>
</main>

<script>
lucide.createIcons();
document.querySelectorAll(".grade-input").forEach((input) => {
    input.addEventListener("input", () => {
        const student = input.dataset.student;
        const values = {};

        document.querySelectorAll(`.grade-input[data-student="${student}"]`).forEach((field) => {
            values[field.dataset.field] = field.value === "" ? null : Number(field.value);
        });

        const classScore = document.querySelector(`.class-score[data-student="${student}"]`);
        const finalScore = document.querySelector(`.final-score[data-student="${student}"]`);
        const classComplete = ["devoir_1", "devoir_2", "devoir_3"].every((key) => Number.isFinite(values[key]));

        if (!classComplete) {
            classScore.textContent = "-";
            finalScore.textContent = "-";
            return;
        }

        const noteClasse = (values.devoir_1 + values.devoir_2 + values.devoir_3) / 3;
        classScore.textContent = noteClasse.toFixed(2);

        if (!Number.isFinite(values.note_examen)) {
            finalScore.textContent = "-";
            return;
        }

        const noteFinale = (noteClasse + values.note_examen * 2) / 3;
        finalScore.textContent = `${noteFinale.toFixed(2)}/20`;
    });
});
</script>
</body>
</html>
