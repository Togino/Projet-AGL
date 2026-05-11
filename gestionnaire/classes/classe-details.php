<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

$classeId = $_GET["id"] ?? "";

if (!ctype_digit((string) $classeId)) {
    header("Location: classes.php");
    exit;
}

$stmtClasse = $pdo->prepare("SELECT ID, nom, niveau FROM classe WHERE ID = ? LIMIT 1");
$stmtClasse->execute([$classeId]);
$classe = $stmtClasse->fetch();

if (!$classe) {
    header("Location: classes.php?error=not_found");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_semester"])) {
    $semesterName = clean($_POST["semester_name"] ?? "");
    $semesterOrder = clean($_POST["semester_order"] ?? "");
    $schoolYear = clean($_POST["school_year"] ?? "2024-2025");

    if (empty($semesterName) || empty($semesterOrder)) {
        $error = "Le nom et l'ordre du semestre sont obligatoires.";
    } elseif (!ctype_digit((string) $semesterOrder)) {
        $error = "L'ordre du semestre doit etre un nombre.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO classe_semestres (classe_id, nom, ordre, annee_scolaire)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$classeId, $semesterName, $semesterOrder, $schoolYear]);

            header("Location: classe-details.php?id=" . urlencode($classeId));
            exit;
        } catch (PDOException $e) {
            $error = "Ce semestre existe deja pour cette classe.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_module"])) {
    $semesterId = clean($_POST["semestre_id"] ?? "");
    $moduleId = clean($_POST["module_id"] ?? "");
    $newModuleName = clean($_POST["new_module_name"] ?? "");
    $coefficient = clean($_POST["coefficient"] ?? "");
    $credits = clean($_POST["credits"] ?? "");
    $heures = clean($_POST["heures"] ?? "");
    $typeModule = clean($_POST["type_module"] ?? "Obligatoire");

    $stmtSemesterCheck = $pdo->prepare("SELECT id, ordre FROM classe_semestres WHERE id = ? AND classe_id = ? LIMIT 1");
    $stmtSemesterCheck->execute([$semesterId, $classeId]);
    $selectedSemester = $stmtSemesterCheck->fetch();

    if (!$selectedSemester) {
        $error = "Selectionnez un semestre valide.";
    } elseif (empty($moduleId) && empty($newModuleName)) {
        $error = "Selectionnez un module ou saisissez un nouveau module.";
    } else {
        try {
            $pdo->beginTransaction();

            if (empty($moduleId)) {
                $stmtModule = $pdo->prepare("INSERT INTO module (nom) VALUES (?)");
                $stmtModule->execute([$newModuleName]);
                $moduleId = $pdo->lastInsertId();
            }

            $stmtLink = $pdo->prepare("
                INSERT INTO classe_modules
                (classe_id, module_id, semestre_id, semestre, coefficient, credits, heures, type_module)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtLink->execute([
                $classeId,
                $moduleId,
                $selectedSemester["id"],
                $selectedSemester["ordre"],
                $coefficient !== "" ? $coefficient : null,
                $credits !== "" ? $credits : null,
                $heures !== "" ? $heures : null,
                $typeModule
            ]);

            $pdo->commit();
            header("Location: classe-details.php?id=" . urlencode($classeId));
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Impossible d'ajouter ce module. Verifiez qu'il n'existe pas deja dans ce semestre.";
        }
    }
}

$stmtStudents = $pdo->prepare("
    SELECT e.MAT, e.annee_etude, u.nom, u.prenom, u.email, u.statut
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE e.classe_id = ?
    AND u.deleted_at IS NULL
    ORDER BY u.prenom ASC, u.nom ASC
");
$stmtStudents->execute([$classeId]);
$students = $stmtStudents->fetchAll();

$stmtSemesters = $pdo->prepare("
    SELECT id, nom, ordre, annee_scolaire
    FROM classe_semestres
    WHERE classe_id = ?
    ORDER BY ordre ASC, id ASC
");
$stmtSemesters->execute([$classeId]);
$semesters = $stmtSemesters->fetchAll();

$stmtSemesterModules = $pdo->prepare("
    SELECT
        cm.id,
        cm.semestre_id,
        cm.coefficient,
        cm.credits,
        cm.heures,
        cm.type_module,
        m.ID AS module_id,
        m.nom,
        COUNT(DISTINCT a.MAT_enseignant) AS total_enseignants
    FROM classe_modules cm
    INNER JOIN module m ON m.ID = cm.module_id
    LEFT JOIN enseignement_affectation a ON a.classe_id = cm.classe_id AND a.module_id = cm.module_id
    WHERE cm.classe_id = ?
    GROUP BY cm.id, cm.semestre_id, cm.coefficient, cm.credits, cm.heures, cm.type_module, m.ID, m.nom
    ORDER BY m.nom ASC
");
$stmtSemesterModules->execute([$classeId]);
$semesterModules = $stmtSemesterModules->fetchAll();

$modulesBySemester = [];
foreach ($semesterModules as $module) {
    $modulesBySemester[$module["semestre_id"]][] = $module;
}

$stmtTeachers = $pdo->prepare("
    SELECT DISTINCT u.MAT, u.nom, u.prenom, u.email, ens.specialisation
    FROM enseignement_affectation a
    INNER JOIN enseignant ens ON ens.MAT = a.MAT_enseignant
    INNER JOIN utilisateur u ON u.MAT = ens.MAT
    WHERE a.classe_id = ?
    AND u.deleted_at IS NULL
    ORDER BY u.prenom ASC, u.nom ASC
");
$stmtTeachers->execute([$classeId]);
$teachers = $stmtTeachers->fetchAll();

$allModules = $pdo->query("SELECT ID, nom FROM module ORDER BY nom ASC")->fetchAll();
$totalStudents = count($students);
$totalModules = count($semesterModules);
$totalTeachers = count($teachers);
$totalSemesters = count($semesters);
$classCode = "CLS-" . str_pad($classe["ID"], 3, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Detail classe - Gestionnaire</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="class-detail-header">
        <div>
            <h1>Detail de la classe</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <a href="classes.php">Classes</a>
                <i data-lucide="chevron-right"></i>
                <strong><?= htmlspecialchars($classe["nom"]) ?></strong>
            </div>
        </div>

        <div class="class-detail-actions">
            <a href="classes.php" class="white-btn">
                <i data-lucide="arrow-left"></i>
                Retour   la liste
            </a>

            <a href="modifier-classe.php?id=<?= urlencode($classe["ID"]) ?>" class="green-btn">
                <i data-lucide="square-pen"></i>
                Modifier la classe
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="class-hero-card">
        <div class="class-hero-left">
            <div class="class-hero-icon"><i data-lucide="school"></i></div>
            <div>
                <div class="class-title-row">
                    <h2><?= htmlspecialchars($classe["nom"]) ?></h2>
                    <span class="status active">Active</span>
                </div>
                <div class="class-meta">
                    <p><strong>Code classe :</strong> <?= htmlspecialchars($classCode) ?></p>
                    <p><strong>Niveau :</strong> <?= htmlspecialchars($classe["niveau"] ?: "-") ?></p>
                    <p><strong>Annee scolaire :</strong> 2024-2025</p>
                </div>
            </div>
        </div>

        <div class="class-stats-grid">
            <div class="class-stat-card green"><div><i data-lucide="users"></i></div><article><h3><?= $totalStudents ?></h3><p>Etudiants</p></article></div>
            <div class="class-stat-card orange"><div><i data-lucide="book-open"></i></div><article><h3><?= $totalModules ?></h3><p>Modules</p></article></div>
            <div class="class-stat-card blue"><div><i data-lucide="graduation-cap"></i></div><article><h3><?= $totalTeachers ?></h3><p>Enseignants</p></article></div>
            <div class="class-stat-card purple"><div><i data-lucide="calendar-days"></i></div><article><h3><?= $totalSemesters ?></h3><p>Semestres</p></article></div>
        </div>
    </div>

    <div class="class-tabs">
        <button class="active" data-class-tab="info">Informations de la classe</button>
        <button data-class-tab="teachers">Enseignants</button>
        <button data-class-tab="students">Etudiants</button>
    </div>

    <div class="class-tab-panel active" data-class-panel="info">
        <div class="semester-card">
            <div class="semester-top"><div><h2>Informations de la classe</h2><p>Vue generale de la classe, de son niveau et de ses effectifs.</p></div></div>
            <div class="side-info-list">
                <div><i data-lucide="school"></i><article><small>Classe</small><strong><?= htmlspecialchars($classe["nom"]) ?></strong></article></div>
                <div><i data-lucide="graduation-cap"></i><article><small>Niveau</small><strong><?= htmlspecialchars($classe["niveau"] ?: "-") ?></strong></article></div>
                <div><i data-lucide="hash"></i><article><small>Code classe</small><strong><?= htmlspecialchars($classCode) ?></strong></article></div>
                <div><i data-lucide="calendar-days"></i><article><small>Annee scolaire</small><strong>2024-2025</strong></article></div>
                <div><i data-lucide="users"></i><article><small>Etudiants</small><strong><?= $totalStudents ?> inscrit<?= $totalStudents > 1 ? "s" : "" ?></strong></article></div>
                <div><i data-lucide="graduation-cap"></i><article><small>Enseignants</small><strong><?= $totalTeachers ?> affecte<?= $totalTeachers > 1 ? "s" : "" ?></strong></article></div>
            </div>
        </div>
    </div>

    <div class="class-tab-panel" data-class-panel="teachers">
        <div class="semester-card">
            <div class="semester-top"><div><h2>Enseignants</h2><p>Liste des enseignants affectes a cette classe.</p></div></div>
            <table class="semester-table"><thead><tr><th>Matricule</th><th>Nom complet</th><th>Email</th><th>Specialisation</th></tr></thead><tbody>
            <?php foreach ($teachers as $teacher): ?>
                <tr><td><?= htmlspecialchars($teacher["MAT"]) ?></td><td><?= htmlspecialchars($teacher["prenom"] . " " . $teacher["nom"]) ?></td><td><?= htmlspecialchars($teacher["email"]) ?></td><td><?= htmlspecialchars($teacher["specialisation"] ?: "-") ?></td></tr>
            <?php endforeach; ?>
            <?php if (count($teachers) === 0): ?><tr><td colspan="4">Aucun enseignant affecte a cette classe.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </div>

    <div class="class-tab-panel" data-class-panel="students">
        <div class="semester-card">
            <div class="semester-top"><div><h2>Etudiants</h2><p>Liste des etudiants inscrits dans cette classe.</p></div></div>
            <table class="semester-table"><thead><tr><th>Matricule</th><th>Nom complet</th><th>Email</th><th>Annee d'etude</th><th>Statut</th></tr></thead><tbody>
            <?php foreach ($students as $student): ?>
                <tr><td><?= htmlspecialchars($student["MAT"]) ?></td><td><?= htmlspecialchars($student["prenom"] . " " . $student["nom"]) ?></td><td><?= htmlspecialchars($student["email"]) ?></td><td><?= htmlspecialchars($student["annee_etude"]) ?></td><td><?= $student["statut"] ? '<span class="status active">Actif</span>' : '<span class="status inactive">Inactif</span>' ?></td></tr>
            <?php endforeach; ?>
            <?php if (count($students) === 0): ?><tr><td colspan="5">Aucun etudiant inscrit dans cette classe.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </div>

    <div class="semester-layout class-legacy-modules" style="display: none;">
        <div class="semester-main">
            <div class="semester-top">
                <div>
                    <h2>Semestres & Modules</h2>
                    <p>Creez les semestres de la classe, puis ajoutez les modules dans chaque semestre.</p>
                </div>

                <div class="class-detail-actions">
                    <button class="white-btn" id="openSemesterCreateModal">
                        <i data-lucide="calendar-plus"></i>
                        Creer un semestre
                    </button>
                    <button class="green-btn" id="openModuleModal" <?= count($semesters) === 0 ? "disabled" : "" ?>>
                        <i data-lucide="plus"></i>
                        Ajouter un module
                    </button>
                </div>
            </div>

            <?php foreach ($semesters as $semester): ?>
                <?php $modules = $modulesBySemester[$semester["id"]] ?? []; ?>
                <div class="semester-card">
                    <div class="semester-header">
                        <div class="semester-title">
                            <h3><?= htmlspecialchars($semester["nom"]) ?></h3>
                            <span class="status <?= count($modules) > 0 ? "active" : "inactive" ?>">
                                <?= count($modules) ?> module<?= count($modules) > 1 ? "s" : "" ?>
                            </span>
                        </div>
                        <small><?= htmlspecialchars($semester["annee_scolaire"]) ?></small>
                    </div>

                    <table class="semester-table">
                        <thead>
                        <tr>
                            <th>Module</th>
                            <th>Credits</th>
                            <th>Coefficient</th>
                            <th>Heures</th>
                            <th>Type</th>
                            <th>Enseignants</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($modules as $module): ?>
                            <tr>
                                <td><?= htmlspecialchars($module["nom"]) ?></td>
                                <td><?= htmlspecialchars($module["credits"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($module["coefficient"] ?? "-") ?></td>
                                <td><?= $module["heures"] !== null ? htmlspecialchars($module["heures"]) . "h" : "-" ?></td>
                                <td><span class="module-type green"><?= htmlspecialchars($module["type_module"] ?: "Obligatoire") ?></span></td>
                                <td><?= htmlspecialchars($module["total_enseignants"]) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (count($modules) === 0): ?>
                            <tr><td colspan="6">Aucun module dans ce semestre.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>

            <?php if (count($semesters) === 0): ?>
                <div class="semester-card">
                    <div class="empty-state">
                        <i data-lucide="calendar-plus"></i>
                        <h3>Aucun semestre cree</h3>
                        <p>Creez d'abord un semestre pour pouvoir y inserer des modules.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="semester-top">
                <div>
                    <h2>Etudiants</h2>
                    <p>Liste des etudiants inscrits dans cette classe.</p>
                </div>
            </div>

            <div class="semester-card">
                <table class="semester-table">
                    <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Annee d'etude</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student["MAT"]) ?></td>
                            <td><?= htmlspecialchars($student["prenom"] . " " . $student["nom"]) ?></td>
                            <td><?= htmlspecialchars($student["email"]) ?></td>
                            <td><?= htmlspecialchars($student["annee_etude"]) ?></td>
                            <td><?= $student["statut"] ? '<span class="status active">Actif</span>' : '<span class="status inactive">Inactif</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($students) === 0): ?>
                        <tr><td colspan="5">Aucun etudiant inscrit dans cette classe.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="semester-sidebar">
            <div class="side-card">
                <h3>Informations sur la classe</h3>
                <div class="side-info-list">
                    <div><i data-lucide="school"></i><article><small>Classe</small><strong><?= htmlspecialchars($classe["nom"]) ?></strong></article></div>
                    <div><i data-lucide="graduation-cap"></i><article><small>Niveau</small><strong><?= htmlspecialchars($classe["niveau"] ?: "-") ?></strong></article></div>
                    <div><i data-lucide="calendar-days"></i><article><small>Semestres</small><strong><?= $totalSemesters ?> crees</strong></article></div>
                    <div><i data-lucide="book-open"></i><article><small>Modules</small><strong><?= $totalModules ?> modules</strong></article></div>
                </div>
            </div>

            <div class="side-card">
                <h3>Enseignants</h3>
                <div class="side-info-list">
                    <?php foreach ($teachers as $teacher): ?>
                        <div>
                            <i data-lucide="user"></i>
                            <article>
                                <small><?= htmlspecialchars($teacher["specialisation"] ?: "Specialisation non renseignee") ?></small>
                                <strong><?= htmlspecialchars($teacher["prenom"] . " " . $teacher["nom"]) ?></strong>
                            </article>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($teachers) === 0): ?>
                        <div><i data-lucide="info"></i><article><small>Aucune affectation</small><strong>Aucun enseignant</strong></article></div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</section>
</main>

<div class="modal-overlay" id="semesterCreateModal">
    <div class="module-modal">
        <div class="modal-head">
            <h2>Creer un semestre</h2>
            <button id="closeSemesterCreateModal" type="button"><i data-lucide="x"></i></button>
        </div>

        <form method="POST" class="admin-form">
            <input type="hidden" name="add_semester" value="1">
            <div class="form-grid">
                <div>
                    <label>Nom du semestre</label>
                    <input type="text" name="semester_name" placeholder="Ex : Semestre 1" required>
                </div>
                <div>
                    <label>Ordre</label>
                    <input type="number" name="semester_order" min="1" placeholder="Ex : 1" required>
                </div>
                <div>
                    <label>Annee scolaire</label>
                    <input type="text" name="school_year" value="2024-2025" placeholder="Ex : 2024-2025">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="white-btn" id="cancelSemesterCreateBtn">Annuler</button>
                <button type="submit" class="green-btn">Creer le semestre</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="moduleModal">
    <div class="module-modal">
        <div class="modal-head">
            <h2>Ajouter un module au semestre</h2>
            <button id="closeModuleModal" type="button"><i data-lucide="x"></i></button>
        </div>

        <form method="POST" class="admin-form">
            <input type="hidden" name="add_module" value="1">
            <div class="form-grid">
                <div>
                    <label>Semestre</label>
                    <select name="semestre_id" required>
                        <option value="">Selectionner un semestre</option>
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?= htmlspecialchars($semester["id"]) ?>"><?= htmlspecialchars($semester["nom"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Module existant</label>
                    <select name="module_id">
                        <option value="">Creer un nouveau module</option>
                        <?php foreach ($allModules as $module): ?>
                            <option value="<?= htmlspecialchars($module["ID"]) ?>"><?= htmlspecialchars($module["nom"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Nouveau module</label>
                    <input type="text" name="new_module_name" placeholder="Ex : Programmation Web">
                </div>
                <div>
                    <label>Type</label>
                    <select name="type_module">
                        <option value="Obligatoire">Obligatoire</option>
                        <option value="Complementaire">Complementaire</option>
                        <option value="Optionnel">Optionnel</option>
                    </select>
                </div>
                <div>
                    <label>Credits</label>
                    <input type="number" name="credits" min="0" placeholder="Ex : 4">
                </div>
                <div>
                    <label>Coefficient</label>
                    <input type="number" step="0.5" min="0" name="coefficient" placeholder="Ex : 2">
                </div>
                <div>
                    <label>Heures</label>
                    <input type="number" name="heures" min="0" placeholder="Ex : 36">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="white-btn" id="cancelModuleBtn">Annuler</button>
                <button type="submit" class="green-btn">Ajouter au semestre</button>
            </div>
        </form>
    </div>
</div>

<script>
lucide.createIcons();

const semesterModal = document.getElementById("semesterCreateModal");
const moduleModal = document.getElementById("moduleModal");

document.getElementById("openSemesterCreateModal").onclick = () => semesterModal.classList.add("active");
document.getElementById("closeSemesterCreateModal").onclick = () => semesterModal.classList.remove("active");
document.getElementById("cancelSemesterCreateBtn").onclick = () => semesterModal.classList.remove("active");

const openModuleBtn = document.getElementById("openModuleModal");
if (openModuleBtn) {
    openModuleBtn.onclick = () => moduleModal.classList.add("active");
}
document.getElementById("closeModuleModal").onclick = () => moduleModal.classList.remove("active");
document.getElementById("cancelModuleBtn").onclick = () => moduleModal.classList.remove("active");

document.querySelectorAll("[data-class-tab]").forEach((tab) => {
    tab.addEventListener("click", () => {
        const target = tab.dataset.classTab;

        document.querySelectorAll("[data-class-tab]").forEach((button) => button.classList.remove("active"));
        document.querySelectorAll("[data-class-panel]").forEach((panel) => panel.classList.remove("active"));

        tab.classList.add("active");
        document.querySelector(`[data-class-panel="${target}"]`)?.classList.add("active");
    });
});
</script>

</body>
</html>
