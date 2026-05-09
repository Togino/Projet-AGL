<?php
require_once __DIR__ . "/../app/includes/auth.php";
require_once __DIR__ . "/../app/config/database.php";
require_once __DIR__ . "/../app/helpers/functions.php";

require_auth(["SUPER_ADMIN", "ADMIN"]);
ensure_password_changed_column($pdo);

$mat = $_SESSION["user"]["MAT"];
$passwordMessage = handle_weekly_password_change($pdo, $mat);

$stmt = $pdo->prepare("
    SELECT u.MAT, u.nom, u.prenom, u.date_de_naissance, u.email, u.statut,
           u.must_change_password, u.password_changed_at, u.created_at, u.updated_at,
           r.name AS role, pa.post
    FROM utilisateur u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN pa ON pa.MAT = u.MAT
    WHERE u.MAT = ?
    LIMIT 1
");
$stmt->execute([$mat]);
$account = $stmt->fetch();
$passwordState = password_change_state($account["password_changed_at"] ?? null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="app-body">
<?php include "../app/includes/sidebar-admin.php"; ?>
<main class="main-content">
<?php include "../app/includes/topbar.php"; ?>
<section class="dashboard">
    <div class="students-header"><div><h1>Mon profil</h1><div class="breadcrumb"><span>Accueil</span><i data-lucide="chevron-right"></i><strong>Mon profil</strong></div></div></div>
    <div class="settings-layout">
        <div class="settings-main">
            <div class="settings-overview-card">
                <h2>Informations du compte</h2>
                <p>Ces informations sont consultatives. Seul le mot de passe peut etre change depuis ce profil.</p>
                <div class="settings-grid">
                    <div class="settings-box green"><div><i data-lucide="badge-check"></i></div><h3>Identite</h3><p><?= htmlspecialchars($account["prenom"] . " " . $account["nom"]) ?></p><span><?= htmlspecialchars($account["MAT"]) ?></span></div>
                    <div class="settings-box blue"><div><i data-lucide="mail"></i></div><h3>Email</h3><p><?= htmlspecialchars($account["email"] ?: "Non renseigne") ?></p><span>Adresse de connexion</span></div>
                    <div class="settings-box orange"><div><i data-lucide="shield-check"></i></div><h3>Role</h3><p><?= htmlspecialchars($account["role"]) ?></p><span><?= htmlspecialchars($account["post"] ?: "Administration") ?></span></div>
                    <div class="settings-box purple"><div><i data-lucide="activity"></i></div><h3>Statut</h3><p><?= $account["statut"] ? "Actif" : "Inactif" ?></p><span>Compte administratif</span></div>
                </div>
            </div>
        </div>
        <aside class="settings-side">
            <div class="system-card">
                <h3>Details administratifs</h3>
                <div class="system-row"><span>Date de naissance</span><strong><?= !empty($account["date_de_naissance"]) ? date("d/m/Y", strtotime($account["date_de_naissance"])) : "-" ?></strong></div>
                <div class="system-row"><span>Compte cree</span><strong><?= !empty($account["created_at"]) ? date("d/m/Y", strtotime($account["created_at"])) : "-" ?></strong></div>
                <div class="system-row"><span>Derniere mise a jour</span><strong><?= !empty($account["updated_at"]) ? date("d/m/Y", strtotime($account["updated_at"])) : "-" ?></strong></div>
                <div class="system-row"><span>Dernier mot de passe</span><strong><?= $passwordState["last"] ? date("d/m/Y H:i", strtotime($passwordState["last"])) : "Jamais" ?></strong></div>
            </div>
            <?php render_password_change_card($passwordState, $passwordMessage); ?>
        </aside>
    </div>
</section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
