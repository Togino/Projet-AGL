<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

ensure_student_extra_columns($pdo);

$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();
$roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'ETUDIANT' LIMIT 1");
$roleStmt->execute();
$roleEtudiant = $roleStmt->fetch();

$error = "";
$success = "";
$temporaryPassword = "";
$nom = "";
$prenom = "";
$dateNaissance = "";
$email = "";
$telephone = "";
$sexe = "";
$tuteurNom = "";
$tuteurPrenom = "";
$tuteurContact = "";
$adresseDomicile = "";
$classeId = "";
$anneeEtude = date("Y");

function generateStudentMatricule($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE MAT LIKE 'ET-%'");
    $stmt->execute();
    $count = $stmt->fetchColumn() + 1;

    return "ET-" . str_pad($count, 4, "0", STR_PAD_LEFT);
}

function generateStudentTemporaryPassword() {
    $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%";
    $password = "";

    for ($i = 0; $i < 12; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $password;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $nom = clean($_POST["nom"] ?? "");
    $prenom = clean($_POST["prenom"] ?? "");
    $dateNaissance = clean($_POST["date_de_naissance"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $telephone = clean($_POST["telephone"] ?? "");
    $sexe = clean($_POST["sexe"] ?? "");
    $tuteurNom = clean($_POST["tuteur_nom"] ?? "");
    $tuteurPrenom = clean($_POST["tuteur_prenom"] ?? "");
    $tuteurContact = clean($_POST["tuteur_contact"] ?? "");
    $adresseDomicile = clean($_POST["adresse_domicile"] ?? "");
    $classeId = clean($_POST["classe_id"] ?? "");
    $anneeEtude = clean($_POST["annee_etude"] ?? "");

    if (!$roleEtudiant) {
        $error = "Le role ETUDIANT est introuvable.";
    } elseif (empty($classes)) {
        $error = "Ajoutez d'abord une classe avant de creer un etudiant.";
    } elseif (empty($nom) || empty($prenom) || empty($dateNaissance) || empty($email) || empty($sexe) || empty($tuteurNom) || empty($tuteurPrenom) || empty($tuteurContact) || empty($adresseDomicile) || empty($classeId) || empty($anneeEtude)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!in_array($sexe, ["M", "F"], true)) {
        $error = "Veuillez selectionner un sexe valide.";
    } elseif (!preg_match('/^\d{4}$/', $anneeEtude)) {
        $error = "L'annee d'etude doit contenir 4 chiffres.";
    } else {
        try {
            $pdo->beginTransaction();

            $mat = generateStudentMatricule($pdo);
            $temporaryPassword = generateStudentTemporaryPassword();
            $hashedPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare("
                INSERT INTO utilisateur
                (MAT, nom, prenom, date_de_naissance, email, telephone, motdepasse, role_id, statut, must_change_password, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, ?)
            ");
            $stmtUser->execute([
                $mat,
                $nom,
                $prenom,
                $dateNaissance,
                $email,
                $telephone !== "" ? $telephone : null,
                $hashedPassword,
                $roleEtudiant["id"],
                $_SESSION["user"]["MAT"]
            ]);

            $stmtEtudiant = $pdo->prepare("
                INSERT INTO etudiant
                (MAT, classe_id, annee_etude, sexe, tuteur_nom, tuteur_prenom, tuteur_contact, adresse_domicile)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtEtudiant->execute([
                $mat,
                $classeId,
                $anneeEtude,
                $sexe,
                $tuteurNom,
                $tuteurPrenom,
                $tuteurContact,
                $adresseDomicile
            ]);

            $pdo->commit();
            $success = "Etudiant cree avec succes. Matricule : " . $mat;

            $nom = "";
            $prenom = "";
            $dateNaissance = "";
            $email = "";
            $telephone = "";
            $sexe = "";
            $tuteurNom = "";
            $tuteurPrenom = "";
            $tuteurContact = "";
            $adresseDomicile = "";
            $classeId = "";
            $anneeEtude = date("Y");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $temporaryPassword = "";
            $error = "Erreur lors de la creation. Verifiez si l'email existe deja.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription etudiant - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head create-head">
            <div>
                <h1>Inscription etudiant</h1>
                <p>Accueil <i data-lucide="chevron-right"></i> Inscriptions <i data-lucide="chevron-right"></i> Nouvel etudiant</p>
            </div>

            <a href="etudiants.php" class="year-btn">Retour</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($temporaryPassword)): ?>
            <div class="temp-password-box">
                <strong>Mot de passe temporaire</strong>
                <code><?= htmlspecialchars($temporaryPassword) ?></code>
                <small>Transmettez ce mot de passe a l'etudiant. Il devra obligatoirement le modifier a sa premiere connexion.</small>
            </div>
        <?php endif; ?>

        <form method="POST" id="createStudentForm" class="admin-form">
            <div class="wizard-layout">
                <div class="wizard-card step-panel active" id="step1">
                    <div class="wizard-title">
                        <span>1</span>
                        <h3>Informations personnelles</h3>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Matricule</label>
                            <input type="text" id="matPreview" placeholder="ET-000X" disabled>
                        </div>

                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="etudiant@email.com" required>
                        </div>

                        <div>
                            <label>Telephone</label>
                            <input type="text" name="telephone" value="<?= htmlspecialchars($telephone) ?>" placeholder="+223 70 12 34 56">
                        </div>

                        <div>
                            <label>Nom</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Ex : Traore" required>
                        </div>

                        <div>
                            <label>Prenom</label>
                            <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Ex : Aminata" required>
                        </div>

                        <div>
                            <label>Date de naissance</label>
                            <input type="date" name="date_de_naissance" value="<?= htmlspecialchars($dateNaissance) ?>" required>
                        </div>

                        <div>
                            <label>Sexe</label>
                            <select name="sexe" required>
                                <option value="">Selectionner</option>
                                <option value="M" <?= $sexe === "M" ? "selected" : "" ?>>Masculin</option>
                                <option value="F" <?= $sexe === "F" ? "selected" : "" ?>>Feminin</option>
                            </select>
                        </div>

                        <div>
                            <label>Nom du tuteur legal</label>
                            <input type="text" name="tuteur_nom" value="<?= htmlspecialchars($tuteurNom) ?>" placeholder="Ex : Coulibaly" required>
                        </div>

                        <div>
                            <label>Prenom du tuteur legal</label>
                            <input type="text" name="tuteur_prenom" value="<?= htmlspecialchars($tuteurPrenom) ?>" placeholder="Ex : Moussa" required>
                        </div>

                        <div>
                            <label>Contact du tuteur</label>
                            <input type="text" name="tuteur_contact" value="<?= htmlspecialchars($tuteurContact) ?>" placeholder="+223 70 12 34 56" required>
                        </div>

                        <div class="full">
                            <label>Adresse du domicile</label>
                            <input type="text" name="adresse_domicile" value="<?= htmlspecialchars($adresseDomicile) ?>" placeholder="Ex : Bamako, ACI 2000, rue 12" required>
                        </div>

                        <div class="full">
                            <div class="info-green small">
                                <i data-lucide="key-round"></i>
                                <p>Le systeme generera automatiquement un mot de passe temporaire pour la premiere connexion.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wizard-card step-panel" id="step2">
                    <div class="wizard-title">
                        <span>2</span>
                        <h3>Informations scolaires</h3>
                    </div>

                    <div class="info-green">
                        <i data-lucide="info"></i>
                        <p>Affectez l'etudiant a une classe et renseignez son annee d'etude.</p>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Classe</label>
                            <select name="classe_id" id="classeSelect" required>
                                <option value="">Selectionner une classe</option>
                                <?php foreach ($classes as $classe): ?>
                                    <option
                                        value="<?= htmlspecialchars($classe["ID"]) ?>"
                                        data-label="<?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>"
                                        <?= (string) $classeId === (string) $classe["ID"] ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Annee d'etude</label>
                            <input type="number" name="annee_etude" min="2000" max="2155" value="<?= htmlspecialchars($anneeEtude) ?>" placeholder="Ex : 2026" required>
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
                        <h2>Verification finale</h2>
                        <p>Confirmez la creation de cet etudiant.</p>
                    </div>

                    <div class="confirm-summary" id="confirmSummary"></div>
                </div>

                <div class="help-card light">
                    <h3><span>?</span> Aide rapide</h3>
                    <p>Vous inscrivez un nouvel etudiant.</p>
                    <p>La classe et l'annee d'etude permettront de retrouver l'etudiant dans les listes et affectations.</p>

                    <h4>Creation du compte</h4>

                    <div class="role-help blue">
                        <div><i data-lucide="graduation-cap"></i></div>
                        <article>
                            <strong>Role etudiant</strong>
                            <small>Le role est attribue automatiquement.</small>
                        </article>
                    </div>

                    <div class="role-help">
                        <div><i data-lucide="school"></i></div>
                        <article>
                            <strong>Affectation</strong>
                            <small>Une classe doit exister avant la creation.</small>
                        </article>
                    </div>

                    <div class="secure-box">
                        <i data-lucide="lock"></i>
                        <span>Le mot de passe temporaire devra etre change par l'etudiant a sa premiere connexion.</span>
                    </div>
                </div>
            </div>

            <div class="wizard-bottom">
                <div class="steps-indicator">
                    <div class="step-dot active" data-step="1"><b>1</b> Informations personnelles</div>
                    <span></span>
                    <div class="step-dot" data-step="2"><b>2</b> Scolarite</div>
                    <span></span>
                    <div class="step-dot" data-step="3"><b>3</b> Confirmation</div>
                </div>

                <div class="bottom-actions">
                    <button type="button" class="cancel-btn" id="prevBtn">Retour</button>
                    <a href="etudiants.php" class="cancel-btn">Annuler</a>
                    <button type="button" class="next-btn" id="nextBtn">Suivant <i data-lucide="arrow-right"></i></button>
                    <button type="submit" class="next-btn hidden" id="submitBtn">Inscrire l'etudiant <i data-lucide="check"></i></button>
                </div>
            </div>
        </form>
    </section>
</main>

<script>
lucide.createIcons();

const form = document.getElementById("createStudentForm");
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
const classeSelect = document.getElementById("classeSelect");
const matPreview = document.getElementById("matPreview");

let currentStep = 1;
matPreview.value = "ET-000X";

function validateStep(step) {
    const required = document.querySelectorAll(`#step${step} [required]`);

    for (const input of required) {
        if (!input.value.trim()) {
            input.focus();
            return false;
        }
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
    const selectedClasse = classeSelect.options[classeSelect.selectedIndex];
    const classeLabel = selectedClasse ? selectedClasse.dataset.label || selectedClasse.text : "";

    confirmSummary.innerHTML = `
        <div><strong>Nom complet :</strong> ${form.prenom.value} ${form.nom.value}</div>
        <div><strong>Email :</strong> ${form.email.value}</div>
        <div><strong>Date de naissance :</strong> ${form.date_de_naissance.value}</div>
        <div><strong>Sexe :</strong> ${form.sexe.options[form.sexe.selectedIndex]?.text || ""}</div>
        <div><strong>Tuteur legal :</strong> ${form.tuteur_prenom.value} ${form.tuteur_nom.value}</div>
        <div><strong>Contact du tuteur :</strong> ${form.tuteur_contact.value}</div>
        <div><strong>Adresse :</strong> ${form.adresse_domicile.value}</div>
        <div><strong>Classe :</strong> ${classeLabel}</div>
        <div><strong>Annee d'etude :</strong> ${form.annee_etude.value}</div>
        <div><strong>Mot de passe :</strong> genere automatiquement</div>
    `;
}

nextBtn.addEventListener("click", () => {
    if (!validateStep(currentStep)) return;

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

updateStep();
</script>

</body>
</html>
