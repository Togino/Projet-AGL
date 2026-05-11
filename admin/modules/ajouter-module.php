<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

$adminNavPrefix = '../';
$navPrefix = '../';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = clean($_POST["nom"]);

    if (empty($nom)) {
        $error = "Le nom du module est obligatoire.";
    } else {
        $pdo->prepare("INSERT INTO module(nom) VALUES(?)")
            ->execute([$nom]);

        header("Location: modules.php?success=created");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter module</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

<div class="dashboard-head">
    <div>
        <h1>Ajouter un module</h1>
        <p>Créer un nouveau module académique</p>
    </div>

    <a href="modules.php" class="year-btn">← Retour</a>
</div>

<div class="form-card">

<?php if (!empty($error)): ?>
    <div class="alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="admin-form">

    <div class="form-grid">
        <div>
            <label>Nom du module</label>
            <input type="text" name="nom" placeholder="Ex: Programmation Web">
        </div>
    </div>

    <button class="submit-btn">Créer le module</button>

</form>

</div>

</section>
</main>
</body>
</html>
