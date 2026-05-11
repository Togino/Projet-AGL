<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
ensure_student_extra_columns($pdo);

$mat = $_GET["mat"] ?? "";
if ($mat === "") {
    header("Location: etudiants.php?error=not_found");
    exit;
}

$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();
$stmt = $pdo->prepare("
    SELECT e.MAT, e.classe_id, e.annee_etude, e.sexe, e.tuteur_nom, e.tuteur_prenom, e.tuteur_contact, e.adresse_domicile,
           u.nom, u.prenom, u.email, u.telephone, u.date_de_naissance, u.statut
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE e.MAT = ? AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$etudiant = $stmt->fetch();
if (!$etudiant) {
    header("Location: etudiants.php?error=not_found");
    exit;
}

$error = "";
$nom = $etudiant["nom"];
$prenom = $etudiant["prenom"];
$email = $etudiant["email"];
$telephone = (string) ($etudiant["telephone"] ?? "");
$dateNaissance = $etudiant["date_de_naissance"];
$sexe = (string) ($etudiant["sexe"] ?? "");
$tuteurNom = (string) ($etudiant["tuteur_nom"] ?? "");
$tuteurPrenom = (string) ($etudiant["tuteur_prenom"] ?? "");
$tuteurContact = (string) ($etudiant["tuteur_contact"] ?? "");
$adresseDomicile = (string) ($etudiant["adresse_domicile"] ?? "");
$classeId = (string) $etudiant["classe_id"];
$anneeEtude = (string) $etudiant["annee_etude"];
$statut = (string) $etudiant["statut"];

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $nom = clean($_POST["nom"] ?? "");
    $prenom = clean($_POST["prenom"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $telephone = clean($_POST["telephone"] ?? "");
    $dateNaissance = clean($_POST["date_de_naissance"] ?? "");
    $sexe = clean($_POST["sexe"] ?? "");
    $tuteurNom = clean($_POST["tuteur_nom"] ?? "");
    $tuteurPrenom = clean($_POST["tuteur_prenom"] ?? "");
    $tuteurContact = clean($_POST["tuteur_contact"] ?? "");
    $adresseDomicile = clean($_POST["adresse_domicile"] ?? "");
    $classeId = clean($_POST["classe_id"] ?? "");
    $anneeEtude = clean($_POST["annee_etude"] ?? "");
    $statut = clean($_POST["statut"] ?? "0");

    if (empty($nom) || empty($prenom) || empty($email) || empty($dateNaissance) || empty($sexe) || empty($tuteurNom) || empty($tuteurPrenom) || empty($tuteurContact) || empty($adresseDomicile) || empty($classeId) || empty($anneeEtude)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!in_array($sexe, ["M", "F"], true)) {
        $error = "Veuillez selectionner un sexe valide.";
    } elseif (!preg_match('/^\d{4}$/', $anneeEtude)) {
        $error = "L'annee d'etude doit contenir 4 chiffres.";
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("
                UPDATE utilisateur
                SET nom = ?, prenom = ?, email = ?, telephone = ?, date_de_naissance = ?, statut = ?, updated_by = ?
                WHERE MAT = ?
            ")->execute([
                $nom, $prenom, $email, $telephone !== "" ? $telephone : null, $dateNaissance, (int) $statut, $_SESSION["user"]["MAT"], $mat
            ]);

            $pdo->prepare("
                UPDATE etudiant
                SET classe_id = ?, annee_etude = ?, sexe = ?, tuteur_nom = ?, tuteur_prenom = ?, tuteur_contact = ?, adresse_domicile = ?
                WHERE MAT = ?
            ")->execute([$classeId, $anneeEtude, $sexe, $tuteurNom, $tuteurPrenom, $tuteurContact, $adresseDomicile, $mat]);

            notify_admin_and_gestionnaires(
                $pdo,
                "PROFILE_UPDATED",
                "low",
                "Etudiant modifie",
                "Le gestionnaire a modifie le compte etudiant " . $mat . ".",
                $_SESSION["user"]["MAT"],
                $_SESSION["user"]["MAT"]
            );

            $pdo->commit();
            header("Location: etudiants.php?success=updated");
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Erreur lors de la modification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier etudiant - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>
<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>
    <section class="dashboard">
        <div class="dashboard-head"><div><h1>Modifier etudiant</h1></div><a href="etudiants.php" class="year-btn">Retour</a></div>
        <div class="form-card">
            <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>"></div>
                    <div><label>Prenom</label><input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>"></div>
                    <div><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($email) ?>"></div>
                    <div><label>Telephone</label><input type="text" name="telephone" value="<?= htmlspecialchars($telephone) ?>"></div>
                    <div><label>Date de naissance</label><input type="date" name="date_de_naissance" value="<?= htmlspecialchars($dateNaissance) ?>"></div>
                    <div><label>Sexe</label><select name="sexe"><option value="M" <?= $sexe === "M" ? "selected" : "" ?>>Masculin</option><option value="F" <?= $sexe === "F" ? "selected" : "" ?>>Feminin</option></select></div>
                    <div><label>Tuteur nom</label><input type="text" name="tuteur_nom" value="<?= htmlspecialchars($tuteurNom) ?>"></div>
                    <div><label>Tuteur prenom</label><input type="text" name="tuteur_prenom" value="<?= htmlspecialchars($tuteurPrenom) ?>"></div>
                    <div><label>Tuteur contact</label><input type="text" name="tuteur_contact" value="<?= htmlspecialchars($tuteurContact) ?>"></div>
                    <div class="full"><label>Adresse</label><input type="text" name="adresse_domicile" value="<?= htmlspecialchars($adresseDomicile) ?>"></div>
                    <div><label>Classe</label><select name="classe_id"><?php foreach ($classes as $classe): ?><option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= $classeId === (string) $classe["ID"] ? "selected" : "" ?>><?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?></option><?php endforeach; ?></select></div>
                    <div><label>Annee d'etude</label><input type="number" name="annee_etude" value="<?= htmlspecialchars($anneeEtude) ?>"></div>
                    <div><label>Statut</label><select name="statut"><option value="1" <?= $statut === "1" ? "selected" : "" ?>>Actif</option><option value="0" <?= $statut === "0" ? "selected" : "" ?>>Inactif</option></select></div>
                </div>
                <button type="submit" class="submit-btn">Enregistrer</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
