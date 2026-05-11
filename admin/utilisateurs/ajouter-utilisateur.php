<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

$adminNavPrefix = '../';
$navPrefix = '../';

ensure_optional_birthdate_for_personnel($pdo);

$roles = $pdo->query("
    SELECT id, name 
    FROM roles 
    WHERE name IN ('SUPER_ADMIN', 'ADMIN', 'GESTIONNAIRE', 'ENSEIGNANT')
    ORDER BY FIELD(name, 'SUPER_ADMIN', 'ADMIN', 'GESTIONNAIRE', 'ENSEIGNANT')
")->fetchAll();
$classes = $pdo->query("
    SELECT ID, nom, niveau
    FROM classe
    ORDER BY niveau ASC, nom ASC
")->fetchAll();

$modules = $pdo->query("
    SELECT ID, nom
    FROM module
    ORDER BY nom ASC
")->fetchAll();

$error = "";
$success = "";
$temporaryPassword = "";
$allowedPreselectedRoles = ["SUPER_ADMIN", "ADMIN", "GESTIONNAIRE", "ENSEIGNANT"];
$preselectedRole = strtoupper(clean($_GET["role"] ?? ""));
$editMat = clean($_GET["mat"] ?? "");
$isEdit = $editMat !== "";
$editUser = null;

if (!in_array($preselectedRole, $allowedPreselectedRoles, true)) {
    $preselectedRole = "";
}

if ($isEdit) {
    $stmtEdit = $pdo->prepare("
        SELECT u.*, r.name AS role_name
        FROM utilisateur u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE u.MAT = ?
        AND u.deleted_at IS NULL
        LIMIT 1
    ");
    $stmtEdit->execute([$editMat]);
    $editUser = $stmtEdit->fetch();

    if (!$editUser) {
        header("Location: utilisateurs.php");
        exit;
    }

    $preselectedRole = $editUser["role_name"];
}

$roleLabels = [
    "SUPER_ADMIN" => "super administrateur",
    "ADMIN" => "administrateur",
    "GESTIONNAIRE" => "gestionnaire",
    "ENSEIGNANT" => "enseignant"
];
$createdUserLabel = $roleLabels[$preselectedRole] ?? "utilisateur";
$pageActionLabel = $isEdit ? "Modifier" : "Creer";

function generateMatriculeByRole($roleName, $pdo) {
    $prefixes = [
        "SUPER_ADMIN" => "AD",
        "ADMIN" => "AD",
        "GESTIONNAIRE" => "GE",
        "ENSEIGNANT" => "ES"
    ];

    $prefix = $prefixes[$roleName] ?? "UT";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE MAT LIKE ?");
    $stmt->execute([$prefix . "-%"]);
    $count = $stmt->fetchColumn() + 1;

    return $prefix . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);
}

function generateTemporaryPassword() {
    $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%";
    $password = "";

    for ($i = 0; $i < 12; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $password;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role_id = clean($_POST["role_id"]);
    $nom = clean($_POST["nom"]);
    $prenom = clean($_POST["prenom"]);
    $date_naissance = clean($_POST["date_de_naissance"] ?? "");
    $sexe = clean($_POST["sexe"] ?? "");
    $email = clean($_POST["email"]);
    $telephone = clean($_POST["telephone"] ?? "");
    $statut = isset($_POST["statut"]) ? 1 : 0;

    $specialisation = clean($_POST["specialisation"] ?? "");
    $date_embauche = clean($_POST["date_embauche"] ?? "");
    $bureau = clean($_POST["bureau"] ?? "");
    $charge_horaire = clean($_POST["charge_horaire"] ?? "");
    $classeIds = array_values(array_unique(array_filter(array_map("intval", $_POST["classe_ids"] ?? []))));
    $moduleIds = array_values(array_unique(array_filter(array_map("intval", $_POST["module_ids"] ?? []))));
    $anneeScolaire = clean($_POST["annee_scolaire"] ?? "");

    if (!$role_id || !$nom || !$prenom || !$email) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmtRole = $pdo->prepare("SELECT id, name FROM roles WHERE id = ?");
        $stmtRole->execute([$role_id]);
        $role = $stmtRole->fetch();

        if (!$role) {
            $error = "Rôle invalide.";
        } elseif ($role["name"] === "ENSEIGNANT" && (empty($classeIds) || empty($moduleIds) || empty($anneeScolaire))) {
            $error = "Veuillez selectionner au moins une classe, une matiere et l'annee scolaire pour l'enseignant.";
        } else {
            try {
                $pdo->beginTransaction();

                if ($isEdit) {
                    $stmt = $pdo->prepare("
                        UPDATE utilisateur
                        SET nom = ?, prenom = ?, date_de_naissance = ?, email = ?, telephone = ?, role_id = ?, statut = ?, updated_by = ?
                        WHERE MAT = ?
                    ");
                    $stmt->execute([
                        $nom,
                        $prenom,
                        $date_naissance !== "" ? $date_naissance : null,
                        $email,
                        $telephone !== "" ? $telephone : null,
                        $role["id"],
                        $statut,
                        $_SESSION["user"]["MAT"],
                        $editMat
                    ]);

                    $stmtLog = $pdo->prepare("
                        INSERT INTO security_logs (mat_user, action, description)
                        VALUES (?, 'UPDATE_USER', ?)
                    ");
                    $stmtLog->execute([
                        $_SESSION["user"]["MAT"],
                        "Modification de l'utilisateur $editMat"
                    ]);

                    $pdo->commit();
                    $success = "Utilisateur modifie avec succes.";

                    $stmtEdit->execute([$editMat]);
                    $editUser = $stmtEdit->fetch();
                    $preselectedRole = $editUser["role_name"];
                } else {
                $mat = generateMatriculeByRole($role["name"], $pdo);
                $temporaryPassword = generateTemporaryPassword();
                $hashedPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO utilisateur
                    (MAT, nom, prenom, date_de_naissance, email, telephone, motdepasse, role_id, statut, must_change_password, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
                ");

                $stmt->execute([
                    $mat,
                    $nom,
                    $prenom,
                    $date_naissance !== "" ? $date_naissance : null,
                    $email,
                    $telephone !== "" ? $telephone : null,
                    $hashedPassword,
                    $role["id"],
                    $statut,
                    $_SESSION["user"]["MAT"]
                ]);

                if ($role["name"] === "ADMIN" || $role["name"] === "GESTIONNAIRE") {
                    $stmtPA = $pdo->prepare("INSERT INTO PA (MAT, post) VALUES (?, ?)");
                    $stmtPA->execute([$mat, $role["name"]]);
                }

                if ($role["name"] === "ENSEIGNANT") {
                    $stmtEns = $pdo->prepare("INSERT INTO enseignant (MAT, specialisation) VALUES (?, ?)");
                    $stmtEns->execute([$mat, $specialisation]);

                    $stmtAffectation = $pdo->prepare("
                        INSERT INTO enseignement_affectation (MAT_enseignant, module_id, classe_id, annee_scolaire)
                        VALUES (?, ?, ?, ?)
                    ");

                    foreach ($classeIds as $classeId) {
                        foreach ($moduleIds as $moduleId) {
                            $stmtAffectation->execute([$mat, $moduleId, $classeId, $anneeScolaire]);
                        }
                    }
                }

                $stmtLog = $pdo->prepare("
                    INSERT INTO security_logs (mat_user, action, description)
                    VALUES (?, 'CREATE_USER', ?)
                ");
                $stmtLog->execute([
                    $_SESSION["user"]["MAT"],
                    "Création de l'utilisateur $mat"
                ]);

                $pdo->commit();
                $success = "Utilisateur créé avec succès. Matricule : " . $mat;

                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $temporaryPassword = "";
                $error = "Erreur lors de la création. Vérifiez l’email ou la base.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageActionLabel) ?> un <?= htmlspecialchars($createdUserLabel) ?> - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="dashboard-head create-head">
        <div>
            <h1><?= htmlspecialchars($pageActionLabel) ?> un <?= htmlspecialchars($createdUserLabel) ?></h1>
            <p>Accueil <i data-lucide="chevron-right"></i> Utilisateurs <i data-lucide="chevron-right"></i> <?= htmlspecialchars($pageActionLabel) ?> un <?= htmlspecialchars($createdUserLabel) ?></p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($temporaryPassword): ?>
        <div class="temp-password-box">
            <strong>Mot de passe temporaire</strong>
            <code><?= htmlspecialchars($temporaryPassword) ?></code>
            <small>Transmettez ce mot de passe à l'utilisateur. Il devra obligatoirement le modifier à sa première connexion.</small>
        </div>
    <?php endif; ?>

    <form method="POST" id="createUserForm" class="admin-form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="mat" value="<?= htmlspecialchars($editMat) ?>">
        <?php endif; ?>

        <div class="wizard-layout">

            <div class="wizard-card step-panel active" id="step1">
                <div class="wizard-title">
                    <span>1</span>
                    <h3>Informations générales</h3>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Rôle de l'utilisateur</label>
                        <select name="role_id" id="roleSelect" required>
                            <option value="">Sélectionnez</option>
                            <?php foreach ($roles as $role): ?>
                                <option
                                    value="<?= $role["id"] ?>"
                                    data-role="<?= $role["name"] ?>"
                                    <?= $preselectedRole === $role["name"] ? "selected" : "" ?>
                                >
                                    <?= $role["name"] === "ADMIN" ? "Administrateur" : ucfirst(strtolower($role["name"])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Matricule</label>
                        <input type="text" id="matPreview" value="<?= $isEdit ? htmlspecialchars($editUser["MAT"]) : "" ?>" placeholder="Ex : ES-0001" disabled>
                    </div>

                    <div>
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?= $isEdit ? htmlspecialchars($editUser["nom"]) : "" ?>" placeholder="Entrez le nom" required>
                    </div>

                    <div>
                        <label>Prénom</label>
                        <input type="text" name="prenom" placeholder="Entrez le prénom" required>
                    </div>

                    <div>
                        <label>Sexe</label>
                        <select name="sexe">
                            <option value="">Sélectionnez</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= $isEdit ? htmlspecialchars($editUser["email"]) : "" ?>" placeholder="exemple@email.com" required>
                    </div>

                    <div>
                        <label>Téléphone</label>
                        <input type="text" name="telephone" value="<?= $isEdit ? htmlspecialchars((string) ($editUser["telephone"] ?? "")) : "" ?>" placeholder="+223 70 12 34 56">
                    </div>

                    <div class="full">
                        <div class="info-green small">
                            <i data-lucide="key-round"></i>
                            <p>Le système générera automatiquement un mot de passe temporaire après la création du compte.</p>
                        </div>
                    </div>
                </div>

                <div class="status-row">
                    <span>Statut</span>
                    <label class="switch">
                        <input type="checkbox" name="statut" <?= !$isEdit || (int) $editUser["statut"] === 1 ? "checked" : "" ?>>
                        <b></b>
                    </label>
                    <small>Utilisateur actif</small>
                </div>
            </div>

            <div class="wizard-card step-panel" id="step2">
                <div class="wizard-title">
                    <span>2</span>
                    <h3>Informations spécifiques</h3>
                </div>

                <div class="info-green">
                    <i data-lucide="info"></i>
                    <p>Les champs à remplir dépendent du rôle sélectionné.</p>
                </div>

                <div class="specific-box" id="adminBox">
                    <h4><i data-lucide="shield-check"></i> Informations administrateur</h4>

                    <div class="permissions-list">
                        <div><i data-lucide="check-circle"></i> Créer des utilisateurs</div>
                        <div><i data-lucide="check-circle"></i> Modifier des utilisateurs</div>
                        <div><i data-lucide="check-circle"></i> Désactiver des comptes</div>
                        <div><i data-lucide="check-circle"></i> Consulter les journaux de sécurité</div>
                    </div>

                    <div class="info-green small">
                        <i data-lucide="lightbulb"></i>
                        <p>Ce rôle possède des privilèges élevés dans le système.</p>
                    </div>
                </div>

                <div class="specific-box" id="gestionnaireBox">
                    <h4><i data-lucide="user-cog"></i> Informations gestionnaire</h4>

                    <div class="form-grid">
                        <div>
                            <label>Service</label>
                            <select name="service">
                                <option value="">Sélectionnez le service</option>
                                <option>Service scolarité</option>
                                <option>Administration académique</option>
                                <option>Gestion pédagogique</option>
                            </select>
                        </div>

                        <div>
                            <label>Salle / Bureau</label>
                            <input type="text" name="bureau_gestionnaire" placeholder="ex : Bureau 204">
                        </div>
                    </div>

                    <div class="info-green small">
                        <i data-lucide="lightbulb"></i>
                        <p>Le gestionnaire s’occupe des opérations scolaires quotidiennes.</p>
                    </div>
                </div>

                <div class="specific-box" id="enseignantBox">
                    <h4><i data-lucide="graduation-cap"></i> Informations enseignant</h4>

                    <div class="form-grid">
                        <div class="full">
                            <label>Spécialité</label>
                            <select name="specialisation" id="specialisation">
                                <option value="">Sélectionnez la spécialité</option>
                                <option>Programmation Web</option>
                                <option>Base de données</option>
                                <option>Algorithmique</option>
                                <option>Réseaux informatiques</option>
                                <option>Mathématiques</option>
                            </select>
                        </div>

                        <div>
                            <label>Date d'embauche</label>
                            <input type="date" name="date_embauche">
                        </div>

                        <div>
                            <label>Salle / Bureau</label>
                            <input type="text" name="bureau" placeholder="ex : Salle 105">
                        </div>

                        <div>
                            <label>Charge horaire (h/semaine)</label>
                            <input type="number" name="charge_horaire" placeholder="ex : 18">
                        </div>

                        <div>
                            <label>Annee scolaire</label>
                            <input type="text" name="annee_scolaire" value="<?= htmlspecialchars(date("Y") . "-" . ((int) date("Y") + 1)) ?>" placeholder="Ex : 2026-2027">
                        </div>
                    </div>

                    <div class="teacher-assignments">
                        <div class="assignment-block">
                            <div class="assignment-head">
                                <div>
                                    <h5>Classes</h5>
                                    <small>Selectionnez une ou plusieurs classes.</small>
                                </div>
                                <button type="button" class="mini-action" data-toggle-group="classes">Tout cocher</button>
                            </div>

                            <div class="choice-grid" data-choice-group="classes">
                                <?php if (count($classes) > 0): ?>
                                    <?php foreach ($classes as $classe): ?>
                                        <label class="choice-card">
                                            <input type="checkbox" name="classe_ids[]" value="<?= htmlspecialchars($classe["ID"]) ?>" data-choice-label="<?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>">
                                            <span>
                                                <strong><?= htmlspecialchars($classe["nom"]) ?></strong>
                                                <small><?= htmlspecialchars($classe["niveau"] ?: "Niveau non renseigne") ?></small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="assignment-empty">Aucune classe disponible.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="assignment-block">
                            <div class="assignment-head">
                                <div>
                                    <h5>Matieres</h5>
                                    <small>Selectionnez les matieres enseignees.</small>
                                </div>
                                <button type="button" class="mini-action" data-toggle-group="modules">Tout cocher</button>
                            </div>

                            <div class="choice-grid" data-choice-group="modules">
                                <?php if (count($modules) > 0): ?>
                                    <?php foreach ($modules as $module): ?>
                                        <label class="choice-card">
                                            <input type="checkbox" name="module_ids[]" value="<?= htmlspecialchars($module["ID"]) ?>" data-choice-label="<?= htmlspecialchars($module["nom"]) ?>">
                                            <span>
                                                <strong><?= htmlspecialchars($module["nom"]) ?></strong>
                                                <small>Matiere</small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="assignment-empty">Aucune matiere disponible.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="info-green small">
                        <i data-lucide="lightbulb"></i>
                        <p>Ces informations aident à mieux gérer les enseignants et leurs affectations.</p>
                    </div>
                </div>
            </div>

            <div class="wizard-card step-panel" id="step3">
                <div class="wizard-title">
                    <span>3</span>
                    <h3>Confirmation</h3>
                </div>

                <div class="confirm-box">
                    <i data-lucide="check-circle-2"></i>
                    <h2>Vérification finale</h2>
                    <p>Confirmez la création de cet utilisateur.</p>
                </div>

                <div class="confirm-summary" id="confirmSummary"></div>
            </div>

            <div class="help-card light">
                <h3><span>?</span> Aide rapide</h3>
                <p>Vous créez un nouvel <?= htmlspecialchars($createdUserLabel) ?>.</p>
                <p>Veuillez remplir tous les champs obligatoires.</p>

                <h4>Rôles disponibles</h4>

                <div class="role-help">
                    <div><i data-lucide="shield-check"></i></div>
                    <article>
                        <strong>Administrateur</strong>
                        <small>Accès complet à toutes les fonctionnalités.</small>
                    </article>
                </div>

                <div class="role-help orange">
                    <div><i data-lucide="user"></i></div>
                    <article>
                        <strong>Gestionnaire</strong>
                        <small>Gère les opérations quotidiennes.</small>
                    </article>
                </div>

                <div class="role-help blue">
                    <div><i data-lucide="graduation-cap"></i></div>
                    <article>
                        <strong>Enseignant</strong>
                        <small>Enseigne et gère les notes.</small>
                    </article>
                </div>

                <div class="secure-box">
                    <i data-lucide="lock"></i>
                    <span>Les identifiants de connexion seront enregistrés de façon sécurisée.</span>
                </div>
            </div>

        </div>

        <div class="wizard-bottom">
            <div class="steps-indicator">
                <div class="step-dot active" data-step="1"><b>1</b> Informations générales</div>
                <span></span>
                <div class="step-dot" data-step="2"><b>2</b> Informations spécifiques</div>
                <span></span>
                <div class="step-dot" data-step="3"><b>3</b> Confirmation</div>
            </div>

            <div class="bottom-actions">
                <button type="button" class="cancel-btn" id="prevBtn">Retour</button>
                <a href="utilisateurs.php" class="cancel-btn">Annuler</a>
                <button type="button" class="next-btn" id="nextBtn">Suivant <i data-lucide="arrow-right"></i></button>
                <button type="submit" class="next-btn hidden" id="submitBtn"><?= $isEdit ? "Enregistrer les modifications" : "Créer " . htmlspecialchars($createdUserLabel === "utilisateur" ? "l’utilisateur" : "le " . $createdUserLabel) ?> <i data-lucide="check"></i></button>
            </div>
        </div>

    </form>

</section>
</main>

<script>
lucide.createIcons();

const roleSelect = document.getElementById("roleSelect");
const matPreview = document.getElementById("matPreview");
const form = document.getElementById("createUserForm");
const isEdit = <?= $isEdit ? "true" : "false" ?>;
const editMat = <?= json_encode($editUser["MAT"] ?? "") ?>;
const editPrenom = <?= json_encode($editUser["prenom"] ?? "") ?>;

if (isEdit && form.prenom) {
    form.prenom.value = editPrenom;
}

const boxes = {
    ADMIN: document.getElementById("adminBox"),
    GESTIONNAIRE: document.getElementById("gestionnaireBox"),
    ENSEIGNANT: document.getElementById("enseignantBox")
};

const panels = [
    document.getElementById("step1"),
    document.getElementById("step2"),
    document.getElementById("step3")
];

const dots = document.querySelectorAll(".step-dot");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
const submitBtn = document.getElementById("submitBtn");
const confirmSummary = document.getElementById("confirmSummary");

let currentStep = 1;

function selectedRole() {
    const selected = roleSelect.options[roleSelect.selectedIndex];
    return selected ? selected.dataset.role : "";
}

function updateRoleUI() {
    Object.values(boxes).forEach(box => box.style.display = "none");

    const role = selectedRole();

    if (role && boxes[role]) {
        boxes[role].style.display = "block";
    }

    if (isEdit) matPreview.value = editMat;
    else if (role === "SUPER_ADMIN" || role === "ADMIN") matPreview.value = "AD-000X";
    else if (role === "GESTIONNAIRE") matPreview.value = "GE-000X";
    else if (role === "ENSEIGNANT") matPreview.value = "ES-000X";
    else matPreview.value = "";
}

function validateStep1() {
    const required = document.querySelectorAll("#step1 [required]");

    for (const input of required) {
        if (!input.value.trim()) {
            input.focus();
            return false;
        }
    }

    return true;
}

function checkedLabels(selector) {
    return Array.from(document.querySelectorAll(selector + ":checked"))
        .map(input => input.dataset.choiceLabel || input.value);
}

function validateStep2() {
    if (selectedRole() !== "ENSEIGNANT") return true;

    const classes = document.querySelectorAll('input[name="classe_ids[]"]:checked');
    const modules = document.querySelectorAll('input[name="module_ids[]"]:checked');
    const annee = form.annee_scolaire?.value.trim();

    if (!annee) {
        form.annee_scolaire.focus();
        return false;
    }
    if (classes.length === 0) {
        document.querySelector('[data-choice-group="classes"]')?.scrollIntoView({ behavior: "smooth", block: "center" });
        return false;
    }
    if (modules.length === 0) {
        document.querySelector('[data-choice-group="modules"]')?.scrollIntoView({ behavior: "smooth", block: "center" });
        return false;
    }
    return true;
}
function updateStep() {
    panels.forEach(panel => panel.classList.remove("active"));
    panels[currentStep - 1].classList.add("active");

    dots.forEach(dot => {
        const step = Number(dot.dataset.step);
        dot.classList.toggle("active", step === currentStep);
        dot.classList.toggle("done", step < currentStep);
    });
    prevBtn.style.display = currentStep === 1 ? "none" : "inline-flex";
    nextBtn.classList.toggle("hidden", currentStep === 3);
    submitBtn.classList.toggle("hidden", currentStep !== 3);
    if (currentStep === 3) buildSummary();

    lucide.createIcons();
}

function buildSummary() {
    const roleText = roleSelect.options[roleSelect.selectedIndex]?.text || "";
    const nom = form.nom.value;
    const prenom = form.prenom.value;
    const email = form.email.value;
    const classLabels = checkedLabels('input[name="classe_ids[]"]');
    const moduleLabels = checkedLabels('input[name="module_ids[]"]');
    const assignmentSummary = selectedRole() === "ENSEIGNANT" ? `
        <div><strong>Classes :</strong> ${classLabels.length ? classLabels.join(", ") : "Aucune"}</div>
        <div><strong>Matieres :</strong> ${moduleLabels.length ? moduleLabels.join(", ") : "Aucune"}</div>
        <div><strong>Annee scolaire :</strong> ${form.annee_scolaire.value || "Non renseignee"}</div>
    ` : "";
    const tel = form.telephone.value || "Non renseigné";

    confirmSummary.innerHTML = `
        <div><strong>Rôle :</strong> ${roleText}</div>
        <div><strong>Nom complet :</strong> ${prenom} ${nom}</div>
        <div><strong>Email :</strong> ${email}</div>
        <div><strong>Mot de passe :</strong> généré automatiquement</div>
        <div><strong>Téléphone :</strong> ${tel}</div>
        ${assignmentSummary}
        <div><strong>Statut :</strong> ${form.statut.checked ? "Utilisateur actif" : "Utilisateur inactif"}</div>
    `;
}

nextBtn.addEventListener("click", () => {
    if (currentStep === 1 && !validateStep1()) return;
    if (currentStep === 2 && !validateStep2()) return;

    if (currentStep < 3) {
        currentStep++;
        updateStep();
    }
});

prevBtn.addEventListener("click", () => {
    if (currentStep > 1) {
        currentStep--;
        updateStep();
    }
});

roleSelect.addEventListener("change", updateRoleUI);

document.querySelectorAll(".mini-action").forEach(button => {
    button.addEventListener("click", () => {
        const group = document.querySelector(`[data-choice-group="${button.dataset.toggleGroup}"]`);
        const inputs = group ? Array.from(group.querySelectorAll('input[type="checkbox"]')) : [];
        const shouldCheck = inputs.some(input => !input.checked);

        inputs.forEach(input => {
            input.checked = shouldCheck;
        });

        button.textContent = shouldCheck ? "Tout decocher" : "Tout cocher";
    });
});

document.querySelectorAll(".eye-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        const input = document.getElementById(btn.dataset.target);
        input.type = input.type === "password" ? "text" : "password";
    });
});

updateRoleUI();
updateStep();
</script>

</body>
</html>
