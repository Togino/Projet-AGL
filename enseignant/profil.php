<?php
require_once __DIR__ . "/../app/includes/auth.php";
require_once __DIR__ . "/../app/config/database.php";
require_once __DIR__ . "/../app/helpers/functions.php";

require_auth(["ENSEIGNANT"]);

$mat = $_SESSION["user"]["MAT"];
$passwordError = "";
$passwordSuccess = "";

$passwordColumnStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'utilisateur'
    AND COLUMN_NAME = 'password_changed_at'
");
$passwordColumnStmt->execute();

if ((int) $passwordColumnStmt->fetchColumn() === 0) {
    $pdo->exec("ALTER TABLE utilisateur ADD COLUMN password_changed_at DATETIME NULL AFTER must_change_password");
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && ($_POST["action"] ?? "") === "change_password") {
    $passwordStmt = $pdo->prepare("
        SELECT motdepasse, password_changed_at
        FROM utilisateur
        WHERE MAT = ?
        LIMIT 1
    ");
    $passwordStmt->execute([$mat]);
    $passwordUser = $passwordStmt->fetch();

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $lastChange = $passwordUser["password_changed_at"] ?? null;
    $nextChangeAt = $lastChange ? strtotime($lastChange . " +7 days") : null;

    if ($nextChangeAt && time() < $nextChangeAt) {
        $passwordError = "Vous pourrez changer votre mot de passe a partir du " . date("d/m/Y H:i", $nextChangeAt) . ".";
    } elseif (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = "Veuillez remplir tous les champs du mot de passe.";
    } elseif (!$passwordUser || !password_verify($currentPassword, $passwordUser["motdepasse"])) {
        $passwordError = "Le mot de passe actuel est incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif (password_verify($newPassword, $passwordUser["motdepasse"])) {
        $passwordError = "Le nouveau mot de passe doit etre different de l'ancien.";
    } elseif (strlen($newPassword) < 8) {
        $passwordError = "Le mot de passe doit contenir au moins 8 caracteres.";
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $passwordError = "Le mot de passe doit contenir au moins une lettre majuscule.";
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $passwordError = "Le mot de passe doit contenir au moins une lettre minuscule.";
    } elseif (!preg_match('/[0-9\W]/', $newPassword)) {
        $passwordError = "Le mot de passe doit contenir au moins un chiffre ou un caractere special.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("
            UPDATE utilisateur
            SET motdepasse = ?, must_change_password = 0, password_changed_at = NOW(), updated_by = ?
            WHERE MAT = ?
        ");
        $updateStmt->execute([$hashedPassword, $mat, $mat]);

        $_SESSION["user"]["must_change_password"] = 0;
        $passwordSuccess = "Mot de passe modifie avec succes. Prochain changement possible dans 7 jours.";
    }
}

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
        e.specialisation
    FROM utilisateur u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN enseignant e ON e.MAT = u.MAT
    WHERE u.MAT = ?
    LIMIT 1
");
$stmt->execute([$mat]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: ../public/logout.php");
    exit;
}

$affectationsStmt = $pdo->prepare("
    SELECT
        a.annee_scolaire,
        c.nom AS classe_nom,
        c.niveau,
        m.nom AS module_nom
    FROM enseignement_affectation a
    INNER JOIN classe c ON c.ID = a.classe_id
    INNER JOIN module m ON m.ID = a.module_id
    WHERE a.MAT_enseignant = ?
    ORDER BY a.annee_scolaire DESC, c.niveau ASC, c.nom ASC, m.nom ASC
");
$affectationsStmt->execute([$mat]);
$affectations = $affectationsStmt->fetchAll();

$totalClasses = count(array_unique(array_map(function ($affectation) {
    return $affectation["classe_nom"] . "|" . $affectation["niveau"];
}, $affectations)));

$totalModules = count(array_unique(array_column($affectations, "module_nom")));
$totalAnnees = count(array_unique(array_column($affectations, "annee_scolaire")));
$lastPasswordChange = $teacher["password_changed_at"] ?? null;
$nextPasswordChange = $lastPasswordChange ? strtotime($lastPasswordChange . " +7 days") : null;
$canChangePassword = !$nextPasswordChange || time() >= $nextPasswordChange;
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
<?php include "../app/includes/sidebar-enseignant.php"; ?>

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
            <div class="icon"><i data-lucide="school"></i></div>
            <div>
                <small>Classes</small>
                <h2><?= $totalClasses ?></h2>
                <span>Classes affectees</span>
            </div>
        </div>

        <div class="student-stat-card blue">
            <div class="icon"><i data-lucide="book-open"></i></div>
            <div>
                <small>Modules</small>
                <h2><?= $totalModules ?></h2>
                <span>Modules affectes</span>
            </div>
        </div>

        <div class="student-stat-card orange">
            <div class="icon"><i data-lucide="calendar-days"></i></div>
            <div>
                <small>Annees</small>
                <h2><?= $totalAnnees ?></h2>
                <span>Annees scolaires</span>
            </div>
        </div>
    </div>

    <div class="settings-layout">
        <div class="settings-main">
            <div class="settings-overview-card">
                <h2>Informations du compte</h2>
                <p>Ces informations sont consultatives. Toute modification doit etre faite par l'administration.</p>

                <div class="settings-grid">
                    <div class="settings-box green">
                        <div><i data-lucide="badge-check"></i></div>
                        <h3>Identite</h3>
                        <p><?= htmlspecialchars($teacher["prenom"] . " " . $teacher["nom"]) ?></p>
                        <span><?= htmlspecialchars($teacher["MAT"]) ?></span>
                    </div>

                    <div class="settings-box blue">
                        <div><i data-lucide="mail"></i></div>
                        <h3>Email</h3>
                        <p><?= htmlspecialchars($teacher["email"] ?: "Non renseigne") ?></p>
                        <span>Adresse de connexion</span>
                    </div>

                    <div class="settings-box orange">
                        <div><i data-lucide="graduation-cap"></i></div>
                        <h3>Specialisation</h3>
                        <p><?= htmlspecialchars($teacher["specialisation"] ?: "Non renseignee") ?></p>
                        <span>Profil enseignant</span>
                    </div>

                    <div class="settings-box purple">
                        <div><i data-lucide="shield-check"></i></div>
                        <h3>Statut</h3>
                        <p><?= $teacher["statut"] ? "Actif" : "Inactif" ?></p>
                        <span><?= htmlspecialchars($teacher["role"]) ?></span>
                    </div>
                </div>
            </div>

            <div class="students-table-card">
                <div class="classes-card-head">
                    <div>
                        <h2>Affectations pedagogiques</h2>
                        <p>Classes et modules actuellement rattaches a votre compte.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Annee scolaire</th>
                                <th>Classe</th>
                                <th>Niveau</th>
                                <th>Module</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($affectations) === 0): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i data-lucide="school"></i>
                                            <h3>Aucune affectation</h3>
                                            <p>Aucune classe ou module n'est encore rattache a ce compte.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($affectations as $index => $affectation): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($affectation["annee_scolaire"]) ?></td>
                                    <td><?= htmlspecialchars($affectation["classe_nom"]) ?></td>
                                    <td><?= htmlspecialchars($affectation["niveau"]) ?></td>
                                    <td><?= htmlspecialchars($affectation["module_nom"]) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="settings-side">
            <div class="system-card">
                <h3>Details administratifs</h3>

                <div class="system-row">
                    <span>Date de naissance</span>
                    <strong><?= !empty($teacher["date_de_naissance"]) ? date("d/m/Y", strtotime($teacher["date_de_naissance"])) : "-" ?></strong>
                </div>

                <div class="system-row">
                    <span>Compte cree</span>
                    <strong><?= !empty($teacher["created_at"]) ? date("d/m/Y", strtotime($teacher["created_at"])) : "-" ?></strong>
                </div>

                <div class="system-row">
                    <span>Derniere mise a jour</span>
                    <strong><?= !empty($teacher["updated_at"]) ? date("d/m/Y", strtotime($teacher["updated_at"])) : "-" ?></strong>
                </div>

                <div class="system-row">
                    <span>Mot de passe</span>
                    <strong><?= $teacher["must_change_password"] ? "A changer" : "A jour" ?></strong>
                </div>

                <div class="system-row">
                    <span>Dernier changement</span>
                    <strong><?= $lastPasswordChange ? date("d/m/Y H:i", strtotime($lastPasswordChange)) : "Jamais" ?></strong>
                </div>
            </div>

            <div class="help-settings-card">
                <h3>Changer le mot de passe</h3>
                <p>Vous pouvez modifier uniquement votre mot de passe, une seule fois par semaine.</p>

                <?php if (!empty($passwordError)): ?>
                    <div class="alert-error"><?= htmlspecialchars($passwordError) ?></div>
                <?php endif; ?>

                <?php if (!empty($passwordSuccess)): ?>
                    <div class="alert-success"><?= htmlspecialchars($passwordSuccess) ?></div>
                <?php endif; ?>

                <?php if (!$canChangePassword): ?>
                    <div class="alert-error">
                        Prochain changement possible le <?= date("d/m/Y H:i", $nextPasswordChange) ?>.
                    </div>
                <?php endif; ?>

                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-grid">
                        <div class="full">
                            <label>Mot de passe actuel</label>
                            <input type="password" name="current_password" <?= $canChangePassword ? "" : "disabled" ?>>
                        </div>

                        <div class="full">
                            <label>Nouveau mot de passe</label>
                            <input type="password" name="new_password" <?= $canChangePassword ? "" : "disabled" ?>>
                        </div>

                        <div class="full">
                            <label>Confirmer le nouveau mot de passe</label>
                            <input type="password" name="confirm_password" <?= $canChangePassword ? "" : "disabled" ?>>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" <?= $canChangePassword ? "" : "disabled" ?>>
                        <i data-lucide="lock"></i>
                        Mettre a jour
                    </button>
                </form>
            </div>
        </aside>
    </div>
</section>
</main>

<script>
lucide.createIcons();
</script>
</body>
</html>
