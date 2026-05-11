<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = "../";
$navPrefix = "../";

$enseignants = $pdo->query("
    SELECT e.MAT, u.nom, u.prenom
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL AND u.statut = 1
    ORDER BY u.prenom ASC, u.nom ASC
")->fetchAll();
$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();
$classeModulesRows = $pdo->query("
    SELECT cm.classe_id, m.ID AS module_id, m.nom AS module_nom, cs.nom AS semestre_nom
    FROM classe_modules cm
    INNER JOIN module m ON m.ID = cm.module_id
    LEFT JOIN classe_semestres cs ON cs.id = cm.semestre_id
    ORDER BY cm.classe_id ASC, cs.ordre ASC, m.nom ASC
")->fetchAll();

$modulesByClasse = [];
foreach ($classeModulesRows as $row) {
    $cid = (string) $row["classe_id"];
    $label = $row["module_nom"] . ($row["semestre_nom"] ? " - " . $row["semestre_nom"] : "");
    $modulesByClasse[$cid][] = ["id" => (string) $row["module_id"], "nom" => $label];
}

$error = "";
$mat = "";
$moduleId = "";
$classeId = "";
$anneeScolaire = date("Y") . "-" . ((int) date("Y") + 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mat = clean($_POST["MAT_enseignant"] ?? "");
    $moduleId = clean($_POST["module_id"] ?? "");
    $classeId = clean($_POST["classe_id"] ?? "");
    $anneeScolaire = clean($_POST["annee_scolaire"] ?? "");

    if ($mat === "" || $moduleId === "" || $classeId === "" || $anneeScolaire === "") {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmtCheckModule = $pdo->prepare("SELECT COUNT(*) FROM classe_modules WHERE classe_id = ? AND module_id = ?");
        $stmtCheckModule->execute([$classeId, $moduleId]);

        if ((int) $stmtCheckModule->fetchColumn() === 0) {
            $error = "Le module choisi n'appartient pas a cette classe.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO enseignement_affectation (MAT_enseignant, module_id, classe_id, annee_scolaire)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$mat, $moduleId, $classeId, $anneeScolaire]);
                header("Location: index.php?success=created");
                exit;
            } catch (PDOException $e) {
                $error = "Cette classe a deja un professeur pour ce module sur cette annee scolaire.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle affectation - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>
<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="dashboard-head">
        <div>
            <h1>Nouvelle affectation</h1>
            <p>Affecter un professeur a un module d'une classe.</p>
        </div>
        <a href="index.php" class="year-btn">Retour</a>
    </div>

    <div class="form-card">
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-grid">
                <div>
                    <label>Professeur</label>
                    <select name="MAT_enseignant" required>
                        <option value="">Selectionner un professeur</option>
                        <?php foreach ($enseignants as $enseignant): ?>
                            <option value="<?= htmlspecialchars($enseignant["MAT"]) ?>" <?= $mat === $enseignant["MAT"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($enseignant["prenom"] . " " . $enseignant["nom"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Classe</label>
                    <select name="classe_id" required>
                        <option value="">Selectionner une classe</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeId === (string) $classe["ID"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Module du semestre</label>
                    <select name="module_id" id="moduleSelect" required>
                        <option value="">Selectionner une classe d'abord</option>
                    </select>
                </div>

                <div>
                    <label>Annee scolaire</label>
                    <input type="text" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>" placeholder="Ex : 2026-2027" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Creer l'affectation</button>
        </form>
    </div>
</section>
</main>

<script>
const modulesByClasse = <?= json_encode($modulesByClasse, JSON_UNESCAPED_UNICODE) ?>;
const classeSelect = document.querySelector('select[name="classe_id"]');
const moduleSelect = document.getElementById("moduleSelect");
const selectedModule = <?= json_encode((string) $moduleId) ?>;
function syncModules() {
    const modules = modulesByClasse[classeSelect.value] || [];
    moduleSelect.innerHTML = "";
    const first = document.createElement("option");
    first.value = "";
    first.textContent = modules.length ? "Selectionner un module" : "Aucun module pour cette classe";
    moduleSelect.appendChild(first);
    modules.forEach((module) => {
        const option = document.createElement("option");
        option.value = module.id;
        option.textContent = module.nom;
        if (selectedModule === module.id) option.selected = true;
        moduleSelect.appendChild(option);
    });
}
classeSelect.addEventListener("change", syncModules);
syncModules();
</script>
</body>
</html>
