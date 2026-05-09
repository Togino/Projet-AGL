<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

header("Location: etudiants.php?error=readonly");
exit;

ensure_student_extra_columns($pdo);

$mat = $_GET["mat"] ?? "";

if (empty($mat)) {
    header("Location: etudiants.php?error=not_found");
    exit;
}

$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();

$stmt = $pdo->prepare("
    SELECT
        e.MAT,
        e.classe_id,
        e.annee_etude,
        e.sexe,
        e.tuteur_nom,
        e.tuteur_prenom,
        e.tuteur_contact,
        e.adresse_domicile,
        u.nom,
        u.prenom,
        u.email,
        u.date_de_naissance,
        u.statut
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
$dateNaissance = $etudiant["date_de_naissance"];
$sexe = (string) ($etudiant["sexe"] ?? "");
$tuteurNom = (string) ($etudiant["tuteur_nom"] ?? "");
$tuteurPrenom = (string) ($etudiant["tuteur_prenom"] ?? "");
$tuteurContact = (string) ($etudiant["tuteur_contact"] ?? "");
$adresseDomicile = (string) ($etudiant["adresse_domicile"] ?? "");
$classeId = (string) $etudiant["classe_id"];
$anneeEtude = (string) $etudiant["annee_etude"];
$statut = (string) $etudiant["statut"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = clean($_POST["nom"] ?? "");
    $prenom = clean($_POST["prenom"] ?? "");
    $email = clean($_POST["email"] ?? "");
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

            $stmtUser = $pdo->prepare("
                UPDATE utilisateur
                SET nom = ?, prenom = ?, email = ?, date_de_naissance = ?, statut = ?, updated_by = ?
                WHERE MAT = ?
            ");
            $stmtUser->execute([
                $nom,
                $prenom,
                $email,
                $dateNaissance,
                (int) $statut,
                $_SESSION["user"]["MAT"],
                $mat
            ]);

            $stmtEtudiant = $pdo->prepare("
                UPDATE etudiant
                SET classe_id = ?,
                    annee_etude = ?,
                    sexe = ?,
                    tuteur_nom = ?,
                    tuteur_prenom = ?,
                    tuteur_contact = ?,
                    adresse_domicile = ?
                WHERE MAT = ?
            ");
            $stmtEtudiant->execute([
                $classeId,
                $anneeEtude,
                $sexe,
                $tuteurNom,
                $tuteurPrenom,
                $tuteurContact,
                $adresseDomicile,
                $mat
            ]);

            $pdo->commit();

            header("Location: etudiants.php?success=updated");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la modification. Verifiez si l'email existe deja.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier etudiant - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Modifier etudiant</h1>
                <p>Mettre a jour les informations, la classe et l'annee d'etude.</p>
            </div>

            <a href="etudiants.php" class="year-btn">Retour</a>
        </div>

        <div class="form-card">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div>
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>">
                    </div>

                    <div>
                        <label>Prenom</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>">
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    </div>

                    <div>
                        <label>Date de naissance</label>
                        <input type="date" name="date_de_naissance" value="<?= htmlspecialchars($dateNaissance) ?>">
                    </div>

                    <div>
                        <label>Sexe</label>
                        <select name="sexe">
                            <option value="">Selectionner</option>
                            <option value="M" <?= $sexe === "M" ? "selected" : "" ?>>Masculin</option>
                            <option value="F" <?= $sexe === "F" ? "selected" : "" ?>>Feminin</option>
                        </select>
                    </div>

                    <div>
                        <label>Nom du tuteur legal</label>
                        <input type="text" name="tuteur_nom" value="<?= htmlspecialchars($tuteurNom) ?>">
                    </div>

                    <div>
                        <label>Prenom du tuteur legal</label>
                        <input type="text" name="tuteur_prenom" value="<?= htmlspecialchars($tuteurPrenom) ?>">
                    </div>

                    <div>
                        <label>Contact du tuteur</label>
                        <input type="text" name="tuteur_contact" value="<?= htmlspecialchars($tuteurContact) ?>">
                    </div>

                    <div class="full">
                        <label>Adresse du domicile</label>
                        <input type="text" name="adresse_domicile" value="<?= htmlspecialchars($adresseDomicile) ?>">
                    </div>

                    <div>
                        <label>Classe</label>
                        <select name="classe_id">
                            <option value="">Selectionner une classe</option>
                            <?php foreach ($classes as $classe): ?>
                                <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= $classeId === (string) $classe["ID"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Annee d'etude</label>
                        <input type="number" name="annee_etude" min="2000" max="2155" value="<?= htmlspecialchars($anneeEtude) ?>">
                    </div>

                    <div>
                        <label>Statut</label>
                        <select name="statut">
                            <option value="1" <?= $statut === "1" ? "selected" : "" ?>>Actif</option>
                            <option value="0" <?= $statut === "0" ? "selected" : "" ?>>Inactif</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Enregistrer</button>
            </form>
        </div>
    </section>
</main>

</body>
</html>
