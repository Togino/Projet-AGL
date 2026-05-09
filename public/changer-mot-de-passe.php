<?php
session_start();

require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

$mat = $_SESSION["user"]["MAT"];

$stmt = $pdo->prepare("SELECT motdepasse, must_change_password FROM utilisateur WHERE MAT = ? LIMIT 1");
$stmt->execute([$mat]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!$user["must_change_password"]) {
    $role = $_SESSION["user"]["role"];

    if ($role === "SUPER_ADMIN" || $role === "ADMIN") {
        header("Location: ../admin/dashboard.php");
        exit;
    } elseif ($role === "GESTIONNAIRE") {
        header("Location: ../gestionnaire/dashboard.php");
        exit;
    } elseif ($role === "ETUDIANT") {
        header("Location: ../etudiant/dashboard.php");
        exit;
    } elseif ($role === "ENSEIGNANT") {
        header("Location: ../enseignant/dashboard.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!password_verify($current_password, $user["motdepasse"])) {
        $error = "Le mot de passe actuel est incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif (strlen($new_password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins une lettre majuscule.";
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins une lettre minuscule.";
    } elseif (!preg_match('/[0-9\W]/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins un chiffre ou un caractère spécial.";
    } else {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        $stmtUpdate = $pdo->prepare("
            UPDATE utilisateur 
            SET motdepasse = ?, must_change_password = FALSE, updated_by = ?
            WHERE MAT = ?
        ");

        $stmtUpdate->execute([
            $hashedPassword,
            $mat,
            $mat
        ]);

        $stmtLog = $pdo->prepare("
            INSERT INTO security_logs (mat_user, action, description)
            VALUES (?, 'CHANGE_PASSWORD', ?)
        ");

        $stmtLog->execute([
            $mat,
            "Changement obligatoire du mot de passe"
        ]);

        $role = $_SESSION["user"]["role"];

        if ($role === "SUPER_ADMIN" || $role === "ADMIN") {
            header("Location: ../admin/dashboard.php");
            exit;
        } elseif ($role === "GESTIONNAIRE") {
            header("Location: ../gestionnaire/dashboard.php");
            exit;
        } elseif ($role === "ETUDIANT") {
            header("Location: ../etudiant/dashboard.php");
            exit;
        } elseif ($role === "ENSEIGNANT") {
            header("Location: ../enseignant/dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe - EDUSYS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="edusys-password-body">

<div class="password-left-panel">

    <div class="password-brand">
        <div class="password-logo-icon">
            <i data-lucide="graduation-cap"></i>
            <i data-lucide="book-open"></i>
        </div>

        <div>
            <h1>EDU<span>SYS</span></h1>
            <p>Système de Gestion Scolaire</p>
        </div>
    </div>

    <div class="password-message">
        <h2>Bienvenue dans <span>EDUSYS !</span></h2>
        <p>
            Pour des raisons de sécurité, vous devez changer votre mot de passe avant d'accéder à votre espace.
        </p>
    </div>

    <div class="password-features">

        <div class="password-feature">
            <div><i data-lucide="shield-check"></i></div>
            <article>
                <h3>Sécurité renforcée</h3>
                <p>Protégez votre compte avec un mot de passe personnel.</p>
            </article>
        </div>

        <div class="password-feature">
            <div><i data-lucide="key-round"></i></div>
            <article>
                <h3>Accès sécurisé</h3>
                <p>Un nouveau mot de passe garantit la sécurité de vos données.</p>
            </article>
        </div>

        <div class="password-feature">
            <div><i data-lucide="users"></i></div>
            <article>
                <h3>Expérience personnalisée</h3>
                <p>Profitez d’un accès sécurisé et personnalisé à EDUSYS.</p>
            </article>
        </div>

    </div>

    <div class="password-note">
        <i data-lucide="shield-check"></i>
        <p>Votre nouveau mot de passe doit être unique et différent des mots de passe précédents.</p>
    </div>

</div>

<div class="password-right-panel">

    <form method="POST" class="change-password-card">

        <div class="password-card-icon">
            <i data-lucide="lock-keyhole"></i>
        </div>

        <div class="password-card-head">
            <h2>Changer votre mot de passe</h2>
            <p>Vous devez définir un nouveau mot de passe pour continuer</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="login-field">
            <label>Mot de passe actuel</label>
            <div class="login-input">
                <i data-lucide="lock"></i>
                <input type="password" name="current_password" id="currentPassword" placeholder="Entrez votre mot de passe actuel">
                <button type="button" class="toggle-password" data-target="currentPassword">
                    <i data-lucide="eye-off"></i>
                </button>
            </div>
        </div>

        <div class="login-field">
            <label>Nouveau mot de passe</label>
            <div class="login-input">
                <i data-lucide="lock"></i>
                <input type="password" name="new_password" id="newPassword" placeholder="Entrez votre nouveau mot de passe">
                <button type="button" class="toggle-password" data-target="newPassword">
                    <i data-lucide="eye-off"></i>
                </button>
            </div>
        </div>

        <div class="login-field">
            <label>Confirmer le nouveau mot de passe</label>
            <div class="login-input">
                <i data-lucide="lock"></i>
                <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirmez votre nouveau mot de passe">
                <button type="button" class="toggle-password" data-target="confirmPassword">
                    <i data-lucide="eye-off"></i>
                </button>
            </div>
        </div>

        <div class="password-rules">
            <h4>Le mot de passe doit contenir :</h4>

            <div class="rules-grid">
                <p><i data-lucide="check-circle"></i> Au moins 8 caractères</p>
                <p><i data-lucide="check-circle"></i> Une lettre majuscule</p>
                <p><i data-lucide="check-circle"></i> Une lettre minuscule</p>
                <p><i data-lucide="check-circle"></i> Un chiffre ou caractère spécial</p>
            </div>
        </div>

        <button type="submit" class="password-submit">
            <i data-lucide="lock"></i>
            Mettre à jour le mot de passe
        </button>

        <div class="secure-login">
            <i data-lucide="shield-check"></i>
            <span>Connexion sécurisée avec chiffrement SSL</span>
        </div>

    </form>

</div>

<script>
lucide.createIcons();

document.querySelectorAll(".toggle-password").forEach(button => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.target);
        input.type = input.type === "password" ? "text" : "password";
    });
});
</script>

</body>
</html>