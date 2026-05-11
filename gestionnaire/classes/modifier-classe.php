<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: classes.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM classe WHERE ID=?");
$stmt->execute([$id]);
$classe = $stmt->fetch();

if (!$classe) {
    header("Location: classes.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = clean($_POST["nom"]);
    $niveau = clean($_POST["niveau"]);

    if (empty($nom) || empty($niveau)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("UPDATE classe SET nom=?, niveau=? WHERE ID=?");
        $stmt->execute([$nom, $niveau, $id]);

        header("Location: classes.php?success=updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier classe - Gestionnaire</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">

<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

<div class="dashboard-head">
    <div>
        <h1>Modifier une classe</h1>
        <p>Modifier les informations de la classe</p>
    </div>

    <a href="classes.php" class="year-btn"><- Retour</a>
</div>

<div class="form-card">

<?php if (!empty($error)): ?>
    <div class="alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="admin-form">

    <div class="form-grid">

        <div>
            <label>Nom de la classe</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($classe["nom"]) ?>" placeholder="Ex: L1 Informatique">
        </div>

        <div>
            <label>Niveau</label>
            <input type="text" name="niveau" value="<?= htmlspecialchars($classe["niveau"]) ?>" placeholder="Ex: Licence 1">
        </div>

    </div>

    <button type="submit" class="submit-btn">
        Modifier la classe
    </button>

</form>

</div>

</section>
</main>
</body>
</html>