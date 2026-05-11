<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";
ensure_optional_birthdate_for_personnel($pdo);

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

$mat = $_GET["mat"] ?? "";
if ($mat === "") {
    header("Location: enseignants.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.MAT, e.specialisation, u.nom, u.prenom, u.email, u.telephone, u.date_de_naissance, u.statut
    FROM enseignant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    WHERE e.MAT = ? AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$enseignant = $stmt->fetch();
if (!$enseignant) {
    header("Location: enseignants.php?error=not_found");
    exit;
}

$error = "";
$nom = $enseignant["nom"];
$prenom = $enseignant["prenom"];
$email = $enseignant["email"];
$telephone = (string) ($enseignant["telephone"] ?? "");
$specialisation = (string) ($enseignant["specialisation"] ?? "");
$statut = (string) $enseignant["statut"];

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $nom = clean($_POST["nom"] ?? "");
    $prenom = clean($_POST["prenom"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $telephone = clean($_POST["telephone"] ?? "");
    $specialisation = clean($_POST["specialisation"] ?? "");
    $statut = clean($_POST["statut"] ?? "0");

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();

            $pdo->prepare("
                UPDATE utilisateur
                SET nom = ?, prenom = ?, email = ?, telephone = ?, statut = ?, updated_by = ?
                WHERE MAT = ?
            ")->execute([
                $nom,
                $prenom,
                $email,
                $telephone !== "" ? $telephone : null,
                (int) $statut,
                $_SESSION["user"]["MAT"],
                $mat
            ]);

            $pdo->prepare("UPDATE enseignant SET specialisation = ? WHERE MAT = ?")
                ->execute([$specialisation !== "" ? $specialisation : null, $mat]);

            notify_admin_and_gestionnaires(
                $pdo,
                "PROFILE_UPDATED",
                "low",
                "Enseignant modifie",
                "Le gestionnaire a modifie le compte enseignant " . $mat . ".",
                $_SESSION["user"]["MAT"],
                $_SESSION["user"]["MAT"]
            );

            $pdo->commit();
            header("Location: enseignants.php?success=updated");
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
    <title>Modifier enseignant - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>
<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>
    <section class="dashboard">
        <div class="dashboard-head"><div><h1>Modifier enseignant</h1></div><a href="enseignants.php" class="year-btn">Retour</a></div>
        <div class="form-card">
            <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>"></div>
                    <div><label>Prenom</label><input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>"></div>
                    <div><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($email) ?>"></div>
                    <div><label>Telephone</label><input type="text" name="telephone" value="<?= htmlspecialchars($telephone) ?>"></div>
                    <div><label>Specialisation</label><input type="text" name="specialisation" value="<?= htmlspecialchars($specialisation) ?>"></div>
                    <div><label>Statut</label><select name="statut"><option value="1" <?= $statut === "1" ? "selected" : "" ?>>Actif</option><option value="0" <?= $statut === "0" ? "selected" : "" ?>>Inactif</option></select></div>
                </div>
                <button type="submit" class="submit-btn">Enregistrer</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
