<?php
session_start();

require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = clean($_POST["identifier"]);
    $password = $_POST["password"];

    if (empty($identifier) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                u.MAT, u.nom, u.prenom, u.email, u.motdepasse, u.statut, u.must_change_password,
                r.name AS role
            FROM utilisateur u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE (u.email = :identifier OR u.MAT = :identifier)
            AND u.deleted_at IS NULL
            LIMIT 1
        ");

        $stmt->execute(["identifier" => $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["motdepasse"])) {
            if (!$user["statut"]) {
                $error = "Votre compte est désactivé.";
            } else {
                $_SESSION["user"] = [
                    "MAT" => $user["MAT"],
                    "nom" => $user["nom"],
                    "prenom" => $user["prenom"],
                    "email" => $user["email"],
                    "role" => $user["role"],
                    "must_change_password" => (int) $user["must_change_password"]
                ];

                if ((int) $user["must_change_password"] === 1) {
                    header("Location: changer-mot-de-passe.php");
                    exit;
                }

                if ($user["role"] === "SUPER_ADMIN" || $user["role"] === "ADMIN") {
                    header("Location: ../admin/dashboard.php");
                    exit;
                }

                if ($user["role"] === "GESTIONNAIRE") {
                    header("Location: ../gestionnaire/dashboard.php");
                    exit;
                }

                if ($user["role"] === "ETUDIANT") {
                    header("Location: ../etudiant/dashboard.php");
                    exit;
                }

                if ($user["role"] === "ENSEIGNANT") {
                    header("Location: ../enseignant/dashboard.php");
                    exit;
                }
            }
        } else {
            $error = "Email, matricule ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - EDUSYS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="edusys-login-body">

<div class="login-left-panel">

    <div class="login-brand">
        <div class="login-logo-icon">
            <i data-lucide="graduation-cap"></i>
            <i data-lucide="book-open"></i>
        </div>

        <div>
            <h1>EDU<span>SYS</span></h1>
            <p>Système de Gestion Scolaire</p>
        </div>
    </div>

    <div class="login-slogan">
        <h2>La gestion scolaire<br><span>simplifiée & intelligente</span></h2>
        <p>
            EDUSYS vous accompagne dans la gestion des étudiants,
            des enseignants, des classes, des notes et bien plus encore.
        </p>
    </div>

    <div class="login-features">

        <div class="login-feature">
            <div><i data-lucide="users"></i></div>
            <article>
                <h3>Gestion complète</h3>
                <p>Étudiants, enseignants, classes, matières et évaluations.</p>
            </article>
        </div>

        <div class="login-feature">
            <div><i data-lucide="shield-check"></i></div>
            <article>
                <h3>Sécurisé & fiable</h3>
                <p>Vos données sont protégées avec les meilleures pratiques de sécurité.</p>
            </article>
        </div>

        <div class="login-feature">
            <div><i data-lucide="bar-chart-3"></i></div>
            <article>
                <h3>Suivi en temps réel</h3>
                <p>Tableaux de bord, statistiques et rapports instantanés.</p>
            </article>
        </div>

        <div class="login-feature">
            <div><i data-lucide="bell"></i></div>
            <article>
                <h3>Notifications</h3>
                <p>Restez informé grâce aux alertes et notifications intelligentes.</p>
            </article>
        </div>

    </div>

    <div class="login-quote">
        <i data-lucide="quote"></i>
        <p>EDUSYS, la solution idéale pour une gestion scolaire efficace et moderne.</p>
    </div>

</div>

<div class="login-right-panel">

    <form method="POST" class="edusys-login-card">

        <div class="login-card-head">
            <h2>Bienvenue ! 👋</h2>
            <p>Connectez-vous à votre espace EDUSYS</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="login-field">
            <label>Email ou matricule</label>
            <div class="login-input">
                <i data-lucide="user"></i>
                <input 
                    type="text" 
                    name="identifier" 
                    placeholder="Entrez votre email ou matricule"
                    required
                >
            </div>
        </div>

        <div class="login-field">
            <label>Mot de passe</label>
            <div class="login-input">
                <i data-lucide="lock"></i>
                <input 
                    type="password" 
                    name="password" 
                    id="loginPassword"
                    placeholder="Entrez votre mot de passe"
                    required
                >
                <button type="button" id="toggleLoginPassword">
                    <i data-lucide="eye-off"></i>
                </button>
            </div>
        </div>

        <div class="login-options">
            <label>
                <input type="checkbox" checked>
                <span>Se souvenir de moi</span>
            </label>

            <a href="#">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="login-submit">
            <i data-lucide="lock"></i>
            Se connecter
        </button>

        <div class="login-separator">
            <span></span>
            <p>ou</p>
            <span></span>
        </div>

        <button type="button" class="guest-btn">
            <i data-lucide="users"></i>
            Accéder en tant qu'invité
        </button>

        <div class="secure-login">
            <i data-lucide="shield-check"></i>
            <span>Connexion sécurisée avec chiffrement SSL</span>
        </div>

    </form>

</div>

<script>
lucide.createIcons();

const toggleBtn = document.getElementById("toggleLoginPassword");
const passwordInput = document.getElementById("loginPassword");

toggleBtn.addEventListener("click", () => {
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
});
</script>

</body>
</html>
