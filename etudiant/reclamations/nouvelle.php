<?php
require_once __DIR__ . '/../../app/includes/auth.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/helpers/reclamations.php';

require_auth(['ETUDIANT']);
$etudiantNavPrefix = '../';
$navPrefix = '../';
ensure_reclamations_schema($pdo);

$mat = $_SESSION["user"]["MAT"];
$error = "";
$sujet = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sujet = trim($_POST["sujet"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($sujet === "" || $message === "") {
        $error = "Veuillez remplir le sujet et la reclamation.";
    } elseif (strlen($sujet) > 120) {
        $error = "Le sujet ne doit pas depasser 120 caracteres.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reclamations (MAT_etudiant, sujet, message)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$mat, $sujet, $message]);

        header("Location: index.php?success=created");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle reclamation - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-etudiant.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Nouvelle reclamation</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Reclamations</strong>
            </div>
        </div>
    </div>

    <div class="form-card">
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-grid">
                <div class="full">
                    <label>Sujet</label>
                    <input type="text" name="sujet" value="<?= htmlspecialchars($sujet) ?>" maxlength="120" required>
                </div>

                <div class="full">
                    <label>Reclamation</label>
                    <textarea name="message" rows="6" required><?= htmlspecialchars($message) ?></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">Envoyer la reclamation</button>
        </form>
    </div>
</section>
</main>

<script>lucide.createIcons();</script>
</body>
</html>
