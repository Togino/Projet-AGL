<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS emploi_temps (
        id INT NOT NULL AUTO_INCREMENT,
        classe_id INT NOT NULL,
        module_id INT NOT NULL,
        MAT_enseignant VARCHAR(10) NULL,
        jour_semaine ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
        heure_debut TIME NOT NULL,
        heure_fin TIME NOT NULL,
        salle VARCHAR(80) NULL,
        annee_scolaire VARCHAR(20) NOT NULL,
        created_by VARCHAR(10) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY fk_edt_classe (classe_id),
        KEY fk_edt_module (module_id),
        KEY fk_edt_enseignant (MAT_enseignant)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
$heures = [];
$error = "";
$success = "";
$anneeScolaire = $_GET["annee_scolaire"] ?? (date("Y") . "-" . ((int) date("Y") + 1));
$classeFilter = $_GET["classe_id"] ?? "";

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
$enseignants = $pdo->query("
    SELECT e.MAT, u.nom, u.prenom
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE u.deleted_at IS NULL AND u.statut = 1
    ORDER BY u.prenom ASC, u.nom ASC
")->fetchAll();

if ($classeFilter === "" && count($classes) > 0) {
    $classeFilter = (string) $classes[0]["ID"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "delete") {
        $id = clean($_POST["id"] ?? "");
        $stmt = $pdo->prepare("DELETE FROM emploi_temps WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: emploi-temps.php?success=deleted&classe_id=" . urlencode($_POST["classe_id"] ?? "") . "&annee_scolaire=" . urlencode($_POST["annee_scolaire"] ?? $anneeScolaire));
        exit;
    }

    if ($action === "create") {
        $classeId = clean($_POST["classe_id"] ?? "");
        $moduleId = clean($_POST["module_id"] ?? "");
        $matEnseignant = clean($_POST["MAT_enseignant"] ?? "");
        $jour = clean($_POST["jour_semaine"] ?? "");
        $heureDebut = clean($_POST["heure_debut"] ?? "");
        $heureFin = clean($_POST["heure_fin"] ?? "");
        $salle = clean($_POST["salle"] ?? "");
        $anneeScolairePost = clean($_POST["annee_scolaire"] ?? "");

        if (empty($classeId) || empty($moduleId) || empty($jour) || empty($heureDebut) || empty($heureFin) || empty($anneeScolairePost)) {
            $error = "Veuillez remplir la classe, le module, le jour, les heures et l'annee scolaire.";
        } elseif (!in_array($jour, $jours, true)) {
            $error = "Jour invalide.";
        } elseif ($heureFin <= $heureDebut) {
            $error = "L'heure de fin doit etre apres l'heure de debut.";
        } else {
            $checkModule = $pdo->prepare("SELECT COUNT(*) FROM classe_modules WHERE classe_id = ? AND module_id = ?");
            $checkModule->execute([$classeId, $moduleId]);
            if ((int) $checkModule->fetchColumn() === 0) {
                $error = "Le module selectionne n'appartient pas a la classe choisie.";
            } else {
                $conflictStmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM emploi_temps
                    WHERE annee_scolaire = ?
                    AND jour_semaine = ?
                    AND (
                        classe_id = ?
                        OR (MAT_enseignant IS NOT NULL AND MAT_enseignant = ?)
                    )
                    AND heure_debut < ?
                    AND heure_fin > ?
                ");
                $conflictStmt->execute([
                    $anneeScolairePost,
                    $jour,
                    $classeId,
                    $matEnseignant ?: "__NO_TEACHER__",
                    $heureFin,
                    $heureDebut
                ]);

                if ((int) $conflictStmt->fetchColumn() > 0) {
                    $error = "Conflit : cette classe ou cet enseignant a deja un cours sur ce creneau.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO emploi_temps
                        (classe_id, module_id, MAT_enseignant, jour_semaine, heure_debut, heure_fin, salle, annee_scolaire, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $classeId,
                        $moduleId,
                        $matEnseignant ?: null,
                        $jour,
                        $heureDebut,
                        $heureFin,
                        $salle ?: null,
                        $anneeScolairePost,
                        $_SESSION["user"]["MAT"] ?? null
                    ]);

                    header("Location: emploi-temps.php?success=created&classe_id=" . urlencode($classeId) . "&annee_scolaire=" . urlencode($anneeScolairePost));
                    exit;
                }
            }
        }
    }
}

$selectedClasse = null;
foreach ($classes as $classe) {
    if ((string) $classe["ID"] === (string) $classeFilter) {
        $selectedClasse = $classe;
        break;
    }
}

$creneaux = [];
$creneauxByDay = array_fill_keys($jours, []);

if ($classeFilter !== "") {
    $stmt = $pdo->prepare("
        SELECT
            et.*,
            c.nom AS classe_nom,
            c.niveau,
            m.nom AS module_nom,
            u.nom AS enseignant_nom,
            u.prenom AS enseignant_prenom
        FROM emploi_temps et
        INNER JOIN classe c ON c.ID = et.classe_id
        INNER JOIN module m ON m.ID = et.module_id
        LEFT JOIN utilisateur u ON u.MAT = et.MAT_enseignant
        WHERE et.classe_id = ?
        AND et.annee_scolaire = ?
        ORDER BY FIELD(et.jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'), et.heure_debut ASC
    ");
    $stmt->execute([$classeFilter, $anneeScolaire]);
    $creneaux = $stmt->fetchAll();

    foreach ($creneaux as $creneau) {
        $creneauxByDay[$creneau["jour_semaine"]][] = $creneau;
    }
}

$heuresByDebut = [];
foreach ($heures as $heure) {
    $heuresByDebut[$heure["debut"]] = $heure;
}

foreach ($creneaux as $creneau) {
    $debut = substr($creneau["heure_debut"], 0, 5);
    $fin = substr($creneau["heure_fin"], 0, 5);
    $heuresByDebut[$debut] = ["debut" => $debut, "fin" => $fin];
}

uksort($heuresByDebut, function ($a, $b) {
    return strtotime($a) <=> strtotime($b);
});

$heures = array_values($heuresByDebut);

function findCreneauForSlot($creneauxByDay, $jour, $heureDebut)
{
    foreach ($creneauxByDay[$jour] ?? [] as $creneau) {
        if (substr($creneau["heure_debut"], 0, 5) === $heureDebut) {
            return $creneau;
        }
    }

    return null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - EduManage</title>
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
            <h1>Emploi du temps</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong><?= $selectedClasse ? htmlspecialchars($selectedClasse["nom"] . " - " . $selectedClasse["niveau"]) : "Classe" ?></strong>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (($_GET["success"] ?? "") === "created"): ?>
        <div class="alert-success">Creneau ajoute et place automatiquement dans le tableau.</div>
    <?php endif; ?>

    <?php if (($_GET["success"] ?? "") === "deleted"): ?>
        <div class="alert-success">Creneau supprime.</div>
    <?php endif; ?>

    <div class="students-table-card">
        <div class="students-filters">
            <form method="GET" class="search-form">
                <select name="classe_id">
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeFilter === (string) $classe["ID"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>" placeholder="Annee scolaire">

                <button type="submit" class="filter-btn">
                    <i data-lucide="filter"></i>
                    Afficher
                </button>
            </form>
        </div>
    </div>

    <div class="form-card">
        <h2>Inserer un cours</h2>
        <p>Choisissez le jour et l'heure : le module sera place automatiquement dans le tableau.</p>

        <form method="POST" class="admin-form" id="course-form">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <div>
                    <label>Classe</label>
                    <select name="classe_id" id="course-classe" required>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeFilter === (string) $classe["ID"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Module</label>
                    <select name="module_id" required id="course-module">
                        <option value="">Selectionner une classe d'abord</option>
                    </select>
                </div>

                <div>
                    <label>Enseignant</label>
                    <select name="MAT_enseignant" id="course-enseignant">
                        <option value="">Non affecte</option>
                        <?php foreach ($enseignants as $enseignant): ?>
                            <option value="<?= htmlspecialchars($enseignant["MAT"]) ?>"><?= htmlspecialchars($enseignant["prenom"] . " " . $enseignant["nom"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Jour</label>
                    <select name="jour_semaine" required id="course-jour">
                        <?php foreach ($jours as $jour): ?>
                            <option value="<?= htmlspecialchars($jour) ?>"><?= htmlspecialchars($jour) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Debut</label>
                    <input type="time" name="heure_debut" required id="course-debut">
                </div>

                <div>
                    <label>Fin</label>
                    <input type="time" name="heure_fin" required id="course-fin">
                </div>

                <div>
                    <label>Salle</label>
                    <input type="text" name="salle" placeholder="Ex : Salle 12" id="course-salle">
                </div>

                <div>
                    <label>Annee scolaire</label>
                    <input type="text" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Placer dans l'emploi du temps</button>
        </form>
    </div>

    <div class="edt-main-card">
        <div class="edt-table">
            <div class="edt-header">
                <div class="edt-hour-column">Heure</div>

                <?php foreach ($jours as $jour): ?>
                    <div class="edt-day-column">
                        <strong><?= htmlspecialchars($jour) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php foreach ($heures as $heure): ?>
                <div class="edt-row">
                    <div class="edt-hour-cell">
                        <?= htmlspecialchars($heure["debut"] . " - " . $heure["fin"]) ?>
                    </div>

                    <?php foreach ($jours as $jour): ?>
                        <?php $creneau = findCreneauForSlot($creneauxByDay, $jour, $heure["debut"]); ?>

                        <div class="edt-course-cell">
                            <?php if ($creneau): ?>
                                <article class="course-card green">
                                    <h4><?= htmlspecialchars($creneau["module_nom"]) ?></h4>
                                    <p><?= $creneau["enseignant_prenom"] ? htmlspecialchars($creneau["enseignant_prenom"] . " " . $creneau["enseignant_nom"]) : "Enseignant non affecte" ?></p>
                                    <small><?= substr($creneau["heure_debut"], 0, 5) ?> - <?= substr($creneau["heure_fin"], 0, 5) ?></small>
                                    <small><?= htmlspecialchars($creneau["salle"] ?: "Salle non renseignee") ?></small>

                                    <form method="POST" onsubmit="return confirm('Supprimer ce cours ?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($creneau["id"]) ?>">
                                        <input type="hidden" name="classe_id" value="<?= htmlspecialchars($classeFilter) ?>">
                                        <input type="hidden" name="annee_scolaire" value="<?= htmlspecialchars($anneeScolaire) ?>">
                                        <button type="submit">Supprimer</button>
                                    </form>
                                </article>
                            <?php else: ?>
                                <button
                                    type="button"
                                    class="timetable-cell-add"
                                    data-jour="<?= htmlspecialchars($jour) ?>"
                                    data-debut="<?= htmlspecialchars($heure["debut"]) ?>"
                                    data-fin="<?= htmlspecialchars($heure["fin"]) ?>"
                                    aria-label="Ajouter un cours le <?= htmlspecialchars($jour) ?> a <?= htmlspecialchars($heure["debut"]) ?>"
                                >
                                    <span class="empty-course">—</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if (count($heures) === 0): ?>
                <div class="edt-empty-state">Aucun cours enregistre pour cette classe.</div>
            <?php endif; ?>
        </div>
    </div>
</section>
</main>

<script>
lucide.createIcons();
const modulesByClasse = <?= json_encode($modulesByClasse, JSON_UNESCAPED_UNICODE) ?>;
const classeSelect = document.getElementById("course-classe");
const moduleSelect = document.getElementById("course-module");
function refreshModulesForClasse() {
    const classeId = classeSelect.value;
    const modules = modulesByClasse[classeId] || [];
    moduleSelect.innerHTML = "";
    const first = document.createElement("option");
    first.value = "";
    first.textContent = modules.length ? "Selectionner un module" : "Aucun module pour cette classe";
    moduleSelect.appendChild(first);
    modules.forEach((m) => {
        const opt = document.createElement("option");
        opt.value = m.id;
        opt.textContent = m.nom;
        moduleSelect.appendChild(opt);
    });
}
classeSelect.addEventListener("change", refreshModulesForClasse);
refreshModulesForClasse();

document.querySelectorAll(".timetable-cell-add").forEach((cell) => {
    cell.addEventListener("click", () => {
        document.getElementById("course-jour").value = cell.dataset.jour;
        document.getElementById("course-debut").value = cell.dataset.debut;
        document.getElementById("course-fin").value = cell.dataset.fin;
        document.getElementById("course-module").focus();
        document.getElementById("course-form").scrollIntoView({ behavior: "smooth", block: "center" });
    });
});
</script>
</body>
</html>
