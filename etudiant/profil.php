<?php
require_once __DIR__ . "/../app/includes/auth.php";
require_once __DIR__ . "/../app/config/database.php";
require_once __DIR__ . "/../app/helpers/functions.php";

require_auth(["ETUDIANT"]);
ensure_password_changed_column($pdo);
ensure_student_extra_columns($pdo);

$mat = $_SESSION["user"]["MAT"];
$passwordMessage = handle_weekly_password_change($pdo, $mat);

$stmt = $pdo->prepare("
    SELECT
        u.MAT,
        u.nom,
        u.prenom,
        u.date_de_naissance,
        u.email,
        u.statut,
        u.must_change_password,
        u.password_changed_at,
        u.created_at,
        u.updated_at,
        r.name AS role,
        e.annee_etude,
        e.sexe,
        e.tuteur_nom,
        e.tuteur_prenom,
        e.tuteur_contact,
        e.adresse_domicile,
        c.nom AS classe_nom,
        c.niveau
    FROM utilisateur u
    INNER JOIN roles r ON r.id = u.role_id
    INNER JOIN etudiant e ON e.MAT = u.MAT
    LEFT JOIN classe c ON c.ID = e.classe_id
    WHERE u.MAT = ?
    LIMIT 1
");
$stmt->execute([$mat]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: ../public/logout.php");
    exit;
}

$passwordState = password_change_state($student["password_changed_at"] ?? null);
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
<?php include "../app/includes/sidebar-etudiant.php"; ?>

<main class="main-content">
<?php include "../app/includes/topbar.php"; ?>

<section class="dashboard">
    <div class="students-header">
        <div>
            <h1>Mon profil</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Mon profil</strong>
            </div>
        </div>
    </div>

    <div class="students-stats">
        <div class="student-stat-card green">
            <div class="icon"><i data-lucide="graduation-cap"></i></div>
            <div>
                <small>Classe</small>
                <h2><?= htmlspecialchars($student["classe_nom"] ?: "-") ?></h2>
                <span><?= htmlspecialchars($student["niveau"] ?: "Niveau non renseigne") ?></span>
            </div>
        </div>

        <div class="student-stat-card blue">
            <div class="icon"><i data-lucide="calendar"></i></div>
            <div>
                <small>Annee d'etude</small>
                <h2><?= htmlspecialchars($student["annee_etude"]) ?></h2>
                <span>Inscription</span>
            </div>
        </div>

        <div class="student-stat-card orange">
            <div class="icon"><i data-lucide="shield-check"></i></div>
            <div>
                <small>Statut</small>
                <h2><?= $student["statut"] ? "Actif" : "Inactif" ?></h2>
                <span><?= htmlspecialchars($student["role"]) ?></span>
            </div>
        </div>
    </div>

    <div class="settings-layout">
        <div class="settings-main">
            <div class="settings-overview-card">
                <h2>Informations du compte</h2>
                <p>Ces informations sont consultatives. Seul le mot de passe peut etre change depuis ce profil.</p>

                <div class="settings-grid">
                    <div class="settings-box green">
                        <div><i data-lucide="badge-check"></i></div>
                        <h3>Identite</h3>
                        <p><?= htmlspecialchars($student["prenom"] . " " . $student["nom"]) ?></p>
                        <span><?= htmlspecialchars($student["MAT"]) ?></span>
                    </div>

                    <div class="settings-box blue">
                        <div><i data-lucide="mail"></i></div>
                        <h3>Email</h3>
                        <p><?= htmlspecialchars($student["email"] ?: "Non renseigne") ?></p>
                        <span>Adresse de connexion</span>
                    </div>

                    <div class="settings-box orange">
                        <div><i data-lucide="home"></i></div>
                        <h3>Adresse</h3>
                        <p><?= htmlspecialchars($student["adresse_domicile"] ?: "Non renseignee") ?></p>
                        <span>Domicile</span>
                    </div>

                    <div class="settings-box purple">
                        <div><i data-lucide="users"></i></div>
                        <h3>Tuteur</h3>
                        <p><?= htmlspecialchars(trim(($student["tuteur_prenom"] ?? "") . " " . ($student["tuteur_nom"] ?? "")) ?: "Non renseigne") ?></p>
                        <span><?= htmlspecialchars($student["tuteur_contact"] ?: "Contact non renseigne") ?></span>
                    </div>
                </div>
            </div>
        </div>

        <aside class="settings-side">
            <div class="system-card">
                <h3>Details administratifs</h3>
                <div class="system-row"><span>Date de naissance</span><strong><?= !empty($student["date_de_naissance"]) ? date("d/m/Y", strtotime($student["date_de_naissance"])) : "-" ?></strong></div>
                <div class="system-row"><span>Sexe</span><strong><?= $student["sexe"] === "M" ? "Masculin" : ($student["sexe"] === "F" ? "Feminin" : "-") ?></strong></div>
                <div class="system-row"><span>Compte cree</span><strong><?= !empty($student["created_at"]) ? date("d/m/Y", strtotime($student["created_at"])) : "-" ?></strong></div>
                <div class="system-row"><span>Derniere mise a jour</span><strong><?= !empty($student["updated_at"]) ? date("d/m/Y", strtotime($student["updated_at"])) : "-" ?></strong></div>
                <div class="system-row"><span>Dernier mot de passe</span><strong><?= $passwordState["last"] ? date("d/m/Y H:i", strtotime($passwordState["last"])) : "Jamais" ?></strong></div>
            </div>

            <?php render_password_change_card($passwordState, $passwordMessage); ?>
        </aside>
    </div>
</section>
</main>

<script>
lucide.createIcons();
</script>
</body>
</html>
