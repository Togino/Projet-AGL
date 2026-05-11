<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = clean($_POST["nom"]);
    $niveau = clean($_POST["niveau"]);

    if (empty($nom) || empty($niveau)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO classe(nom, niveau) VALUES(?, ?)");
        $stmt->execute([$nom, $niveau]);

        header("Location: classes.php?success=created");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter classe - Gestionnaire</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">

<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

<div class="dashboard-head">
    <div>
        <h1>Ajouter une classe</h1>
        <p>Créer une nouvelle classe dans le système</p>
    </div>

    <a href="classes.php" class="year-btn">← Retour</a>
</div>

<div class="form-card">

<?php if (!empty($error)): ?>
    <div class="alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="admin-form">

    <div class="form-grid">

        <div>
            <label>Nom de la classe</label>
            <input type="text" name="nom" placeholder="Ex: L1 Informatique">
        </div>

        <div>
            <label>Niveau</label>
            <input type="text" name="niveau" placeholder="Ex: Licence 1">
        </div>

    </div>

    <button type="submit" class="submit-btn">
        Créer la classe
    </button>

</form>

</div>

</section>
</main>
</body>
</html>