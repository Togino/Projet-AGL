<?php
session_start();

require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

if (!isset($_SESSION["user"])) {
    redirect("public/login.php");
}

$error = "";
$success = "";

$mat = $_SESSION["user"]["MAT"];

$stmt = $pdo->prepare("SELECT motdepasse, must_change_password FROM utilisateur WHERE MAT = ? LIMIT 1");
$stmt->execute([$mat]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect("public/login.php");
}

if (!$user["must_change_password"]) {
    $role = $_SESSION["user"]["role"];

    if ($role === "SUPER_ADMIN" || $role === "ADMIN") {
        redirect("admin/dashboard.php");
    } elseif ($role === "GESTIONNAIRE") {
        redirect("gestionnaire/dashboard.php");
    } elseif ($role === "ETUDIANT") {
        redirect("etudiant/dashboard.php");
    } elseif ($role === "ENSEIGNANT") {
        redirect("enseignant/dashboard.php");
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
        $error = "Le mot de passe doit contenir au moins 8 caracteres.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins une lettre majuscule.";
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins une lettre minuscule.";
    } elseif (!preg_match('/[0-9\W]/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins un chiffre ou un caractere special.";
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
            redirect("admin/dashboard.php");
        } elseif ($role === "GESTIONNAIRE") {
            redirect("gestionnaire/dashboard.php");
        } elseif ($role === "ETUDIANT") {
            redirect("etudiant/dashboard.php");
        } elseif ($role === "ENSEIGNANT") {
            redirect("enseignant/dashboard.php");
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
            <p>Systeme de Gestion Scolaire</p>
        </div>
    </div>

    <div class="password-message">
        <h2>Bienvenue dans <span>EDUSYS !</span></h2>
        <p>
            Pour des raisons de securite, vous devez changer votre mot de passe avant d'acceder a votre espace.
        </p>
    </div>

    <div class="password-features">

        <div class="password-feature">
            <div><i data-lucide="shield-check"></i></div>
            <article>
                <h3>Securite renforcee</h3>
                <p>Protegez votre compte avec un mot de passe personnel.</p>
            </article>
        </div>

        <div class="password-feature">
            <div><i data-lucide="key-round"></i></div>
            <article>
                <h3>Acces securise</h3>
                <p>Un nouveau mot de passe garantit la securite de vos donnees.</p>
            </article>
        </div>

        <div class="password-feature">
            <div><i data-lucide="users"></i></div>
            <article>
                <h3>Experience personnalisee</h3>
                <p>Profitez d'un acces securise et personnalise a EDUSYS.</p>
            </article>
        </div>

    </div>

    <div class="password-note">
        <i data-lucide="shield-check"></i>
        <p>Votre nouveau mot de passe doit etre unique et different des mots de passe precedents.</p>
    </div>

</div>

<div class="password-right-panel">

    <form method="POST" class="change-password-card">

        <div class="password-card-icon">
            <i data-lucide="lock-keyhole"></i>
        </div>

        <div class="password-card-head">
            <h2>Changer votre mot de passe</h2>
            <p>Vous devez definir un nouveau mot de passe pour continuer</p>
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
                <p><i data-lucide="check-circle"></i> Au moins 8 caracteres</p>
                <p><i data-lucide="check-circle"></i> Une lettre majuscule</p>
                <p><i data-lucide="check-circle"></i> Une lettre minuscule</p>
                <p><i data-lucide="check-circle"></i> Un chiffre ou caractere special</p>
            </div>
        </div>

        <button type="submit" class="password-submit">
            <i data-lucide="lock"></i>
            Mettre a jour le mot de passe
        </button>

        <div class="secure-login">
            <i data-lucide="shield-check"></i>
            <span>Connexion securisee avec chiffrement SSL</span>
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
