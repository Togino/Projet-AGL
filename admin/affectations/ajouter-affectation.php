<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$enseignants = $pdo->query("
    SELECT e.MAT, u.nom, u.prenom
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL AND u.statut = 1
    ORDER BY u.prenom ASC, u.nom ASC
")->fetchAll();

$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();
$classeModulesRows = $pdo->query("
    SELECT cm.classe_id, m.ID AS module_id, m.nom AS module_nom
    FROM classe_modules cm
    INNER JOIN module m ON m.ID = cm.module_id
    ORDER BY cm.classe_id ASC, m.nom ASC
")->fetchAll();
$modulesByClasse = [];
foreach ($classeModulesRows as $row) {
    $cid = (string) $row["classe_id"];
    if (!isset($modulesByClasse[$cid])) {
        $modulesByClasse[$cid] = [];
    }
    $modulesByClasse[$cid][] = ["id" => (string) $row["module_id"], "nom" => $row["module_nom"]];
}

$error = "";
$success = "";
$mat = "";
$selectedClassIds = [];
$selectedModulesByClass = [];
$anneeScolaire = date("Y") . "-" . ((int) date("Y") + 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mat = clean($_POST["MAT_enseignant"] ?? "");
    $selectedClassIds = array_values(array_filter($_POST["classe_ids"] ?? [], static fn($id) => ctype_digit((string) $id)));
    $selectedModulesByClass = $_POST["module_ids_by_classe"] ?? [];
    $anneeScolaire = clean($_POST["annee_scolaire"] ?? "");

    if (empty($mat) || empty($selectedClassIds) || empty($anneeScolaire)) {
        $error = "Selectionnez un professeur, au moins une classe et l'annee scolaire.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmtCheckModule = $pdo->prepare("SELECT COUNT(*) FROM classe_modules WHERE classe_id = ? AND module_id = ?");
            $stmtExisting = $pdo->prepare("
                SELECT u.prenom, u.nom
                FROM enseignement_affectation a
                INNER JOIN utilisateur u ON u.MAT = a.MAT_enseignant
                WHERE a.module_id = ? AND a.classe_id = ? AND a.annee_scolaire = ?
                LIMIT 1
            ");
            $stmtInsert = $pdo->prepare("
                INSERT INTO enseignement_affectation (MAT_enseignant, module_id, classe_id, annee_scolaire)
                VALUES (?, ?, ?, ?)
            ");

            $created = 0;
            $skipped = 0;
            $invalid = 0;
            $missingModules = 0;

            foreach ($selectedClassIds as $classeId) {
                $moduleIds = array_values(array_filter($selectedModulesByClass[$classeId] ?? [], static fn($id) => ctype_digit((string) $id)));

                if (empty($moduleIds)) {
                    $missingModules++;
                    continue;
                }

                foreach ($moduleIds as $moduleId) {
                    $stmtCheckModule->execute([$classeId, $moduleId]);
                    if ((int) $stmtCheckModule->fetchColumn() === 0) {
                        $invalid++;
                        continue;
                    }

                    $stmtExisting->execute([$moduleId, $classeId, $anneeScolaire]);
                    if ($stmtExisting->fetch()) {
                        $skipped++;
                        continue;
                    }

                    $stmtInsert->execute([$mat, $moduleId, $classeId, $anneeScolaire]);
                    $created++;
                }
            }

            if ($created === 0) {
                $pdo->rollBack();
                $error = "Aucune affectation creee. Selectionnez au moins un module disponible dans les classes choisies.";
                if ($skipped > 0) {
                    $error .= " Certaines affectations existent deja pour cette annee.";
                }
                if ($missingModules > 0) {
                    $error .= " Certaines classes n'ont aucun module selectionne.";
                }
                if ($invalid > 0) {
                    $error .= " Certains modules ne correspondent pas aux classes choisies.";
                }
            } else {
                $pdo->commit();
                header("Location: affectations.php?success=created&count=" . urlencode((string) $created) . "&skipped=" . urlencode((string) $skipped));
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Erreur lors de la creation des affectations.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter affectation - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Nouvelle affectation</h1>
                <p>Affecter un professeur a un module pour une classe et une annee scolaire.</p>
            </div>

            <a href="affectations.php" class="year-btn">Retour</a>
        </div>

        <div class="form-card">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div>
                        <label>Professeur</label>
                        <select name="MAT_enseignant">
                            <option value="">Selectionner un professeur</option>
                            <?php foreach ($enseignants as $enseignant): ?>
                                <option value="<?= htmlspecialchars($enseignant["MAT"]) ?>" <?= $mat === $enseignant["MAT"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($enseignant["prenom"] . " " . $enseignant["nom"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Annee scolaire</label>
                        <input type="text" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>" placeholder="Ex : 2025-2026">
                    </div>
                </div>

                <div class="assignment-picker">
                    <div class="assignment-picker-head">
                        <div>
                            <h3>Classes et modules</h3>
                            <p>Selectionnez les classes, puis cochez les modules a confier au professeur.</p>
                        </div>
                    </div>

                    <?php foreach ($classes as $classe): ?>
                        <?php
                            $cid = (string) $classe["ID"];
                            $classModules = $modulesByClasse[$cid] ?? [];
                            $isClassSelected = in_array($cid, array_map("strval", $selectedClassIds), true);
                            $selectedModuleIds = array_map("strval", $selectedModulesByClass[$cid] ?? []);
                        ?>
                        <div class="assignment-class" data-assignment-class="<?= htmlspecialchars($cid) ?>">
                            <label class="assignment-class-title">
                                <input type="checkbox" name="classe_ids[]" value="<?= htmlspecialchars($cid) ?>" <?= $isClassSelected ? "checked" : "" ?>>
                                <span><?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?></span>
                                <small><?= count($classModules) ?> module<?= count($classModules) > 1 ? "s" : "" ?></small>
                            </label>

                            <div class="assignment-modules">
                                <?php foreach ($classModules as $module): ?>
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="module_ids_by_classe[<?= htmlspecialchars($cid) ?>][]"
                                            value="<?= htmlspecialchars($module["id"]) ?>"
                                            <?= in_array((string) $module["id"], $selectedModuleIds, true) ? "checked" : "" ?>
                                        >
                                        <span><?= htmlspecialchars($module["nom"]) ?></span>
                                    </label>
                                <?php endforeach; ?>

                                <?php if (count($classModules) === 0): ?>
                                    <div class="assignment-empty">Aucun module ajoute dans cette classe.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="submit-btn">Creer les affectations</button>
            </form>
        </div>
    </section>
</main>
<script>
function syncAssignmentClass(card) {
    const classCheckbox = card.querySelector('.assignment-class-title input[type="checkbox"]');
    const moduleCheckboxes = card.querySelectorAll('.assignment-modules input[type="checkbox"]');
    card.classList.toggle("active", classCheckbox.checked);
    moduleCheckboxes.forEach((checkbox) => {
        checkbox.disabled = !classCheckbox.checked;
        if (!classCheckbox.checked) {
            checkbox.checked = false;
        }
    });
}

document.querySelectorAll(".assignment-class").forEach((card) => {
    const classCheckbox = card.querySelector('.assignment-class-title input[type="checkbox"]');
    classCheckbox.addEventListener("change", () => syncAssignmentClass(card));
    syncAssignmentClass(card);
});
</script>
</body>
</html>
