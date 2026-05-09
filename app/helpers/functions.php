<?php

function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function hasRole($role) {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === $role;
}

function require_auth(array $roles = []) {
    if (!isLoggedIn()) {
        redirect("../public/login.php");
    }

    if ($roles && !in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
        redirect("../public/login.php");
    }
}

function ensure_password_changed_column(PDO $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'utilisateur'
        AND COLUMN_NAME = 'password_changed_at'
    ");
    $stmt->execute();

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE utilisateur ADD COLUMN password_changed_at DATETIME NULL AFTER must_change_password");
    }
}

function handle_weekly_password_change(PDO $pdo, string $mat): array {
    $result = ["error" => "", "success" => ""];

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST" || ($_POST["action"] ?? "") !== "change_password") {
        return $result;
    }

    ensure_password_changed_column($pdo);

    $stmt = $pdo->prepare("SELECT motdepasse, password_changed_at FROM utilisateur WHERE MAT = ? LIMIT 1");
    $stmt->execute([$mat]);
    $user = $stmt->fetch();

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $lastChange = $user["password_changed_at"] ?? null;
    $nextChangeAt = $lastChange ? strtotime($lastChange . " +7 days") : null;

    if ($nextChangeAt && time() < $nextChangeAt) {
        $result["error"] = "Vous pourrez changer votre mot de passe a partir du " . date("d/m/Y H:i", $nextChangeAt) . ".";
    } elseif (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $result["error"] = "Veuillez remplir tous les champs du mot de passe.";
    } elseif (!$user || !password_verify($currentPassword, $user["motdepasse"])) {
        $result["error"] = "Le mot de passe actuel est incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $result["error"] = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif (password_verify($newPassword, $user["motdepasse"])) {
        $result["error"] = "Le nouveau mot de passe doit etre different de l'ancien.";
    } elseif (strlen($newPassword) < 8) {
        $result["error"] = "Le mot de passe doit contenir au moins 8 caracteres.";
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $result["error"] = "Le mot de passe doit contenir au moins une lettre majuscule.";
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $result["error"] = "Le mot de passe doit contenir au moins une lettre minuscule.";
    } elseif (!preg_match('/[0-9\W]/', $newPassword)) {
        $result["error"] = "Le mot de passe doit contenir au moins un chiffre ou un caractere special.";
    } else {
        $updateStmt = $pdo->prepare("
            UPDATE utilisateur
            SET motdepasse = ?, must_change_password = 0, password_changed_at = NOW(), updated_by = ?
            WHERE MAT = ?
        ");
        $updateStmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $mat, $mat]);
        $_SESSION["user"]["must_change_password"] = 0;
        $result["success"] = "Mot de passe modifie avec succes. Prochain changement possible dans 7 jours.";
    }

    return $result;
}

function password_change_state(?string $lastChange): array {
    $nextChange = $lastChange ? strtotime($lastChange . " +7 days") : null;

    return [
        "last" => $lastChange,
        "next" => $nextChange,
        "can_change" => !$nextChange || time() >= $nextChange,
    ];
}

function render_password_change_card(array $state, array $message = []) {
    $canChange = $state["can_change"];
    ?>
    <div class="help-settings-card">
        <h3>Changer le mot de passe</h3>
        <p>Vous pouvez modifier uniquement votre mot de passe, une seule fois par semaine.</p>

        <?php if (!empty($message["error"])): ?>
            <div class="alert-error"><?= htmlspecialchars($message["error"]) ?></div>
        <?php endif; ?>

        <?php if (!empty($message["success"])): ?>
            <div class="alert-success"><?= htmlspecialchars($message["success"]) ?></div>
        <?php endif; ?>

        <?php if (!$canChange): ?>
            <div class="alert-error">Prochain changement possible le <?= date("d/m/Y H:i", $state["next"]) ?>.</div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="change_password">
            <div class="form-grid">
                <div class="full">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password" <?= $canChange ? "" : "disabled" ?>>
                </div>
                <div class="full">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" <?= $canChange ? "" : "disabled" ?>>
                </div>
                <div class="full">
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_password" <?= $canChange ? "" : "disabled" ?>>
                </div>
            </div>
            <button type="submit" class="submit-btn" <?= $canChange ? "" : "disabled" ?>>
                <?= ui_icon("shield") ?>
                Mettre a jour
            </button>
        </form>
    </div>
    <?php
}

function render_app_page($pageTitle, $sidebarFile) {
    $sidebarPath = __DIR__ . "/../includes/" . basename($sidebarFile);

    if (!is_file($sidebarPath)) {
        die("Sidebar introuvable.");
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title><?= e($pageTitle) ?> - EduManage</title>
        <link rel="stylesheet" href="../public/assets/css/style.css">
    </head>
    <body class="app-body">
        <?php include $sidebarPath; ?>

        <main class="main-content">
            <?php include __DIR__ . "/../includes/header.php"; ?>

            <section class="dashboard">
                <div class="students-header">
                    <div>
                        <h1><?= e($pageTitle) ?></h1>
                        <div class="breadcrumb">
                            <span>Accueil</span>
                            <?= ui_icon("chevron-down") ?>
                            <strong><?= e($pageTitle) ?></strong>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
    </html>
    <?php
}

function redirect_by_role() {
    if (!isset($_SESSION['user']['role'])) {
        redirect("../public/login.php");
    }

    $role = $_SESSION['user']['role'];

    if ($role === "SUPER_ADMIN" || $role === "ADMIN") {
        redirect("../admin/dashboard.php");
    }

    if ($role === "GESTIONNAIRE") {
        redirect("../gestionnaire/dashboard.php");
    }

    if ($role === "ETUDIANT") {
        redirect("../etudiant/dashboard.php");
    }

    if ($role === "ENSEIGNANT") {
        redirect("../enseignant/dashboard.php");
    }

    redirect("../public/login.php");
}

function ensure_student_extra_columns(PDO $pdo) {
    $columns = [
        "sexe" => "ALTER TABLE etudiant ADD COLUMN sexe ENUM('M','F') NULL AFTER annee_etude",
        "tuteur_nom" => "ALTER TABLE etudiant ADD COLUMN tuteur_nom VARCHAR(50) NULL AFTER sexe",
        "tuteur_prenom" => "ALTER TABLE etudiant ADD COLUMN tuteur_prenom VARCHAR(50) NULL AFTER tuteur_nom",
        "tuteur_contact" => "ALTER TABLE etudiant ADD COLUMN tuteur_contact VARCHAR(30) NULL AFTER tuteur_prenom",
        "adresse_domicile" => "ALTER TABLE etudiant ADD COLUMN adresse_domicile VARCHAR(255) NULL AFTER tuteur_contact",
    ];

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'etudiant'
        AND COLUMN_NAME = ?
    ");

    foreach ($columns as $column => $sql) {
        $stmt->execute([$column]);

        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($sql);
        }
    }
}

function ui_icon($name) {
    $icons = [
        "leaf" => '<path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 14-9 0 10-4 14-9 14a7 7 0 0 1-5-2"/><path d="M4 20c4-7 8-9 14-16"/>',
        "home" => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/>',
        "user" => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        "users" => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        "graduation" => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/><path d="M22 10v6"/>',
        "school" => '<path d="M4 22V10l8-6 8 6v12"/><path d="M9 22v-6h6v6"/><path d="M8 12h.01"/><path d="M16 12h.01"/>',
        "book" => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/>',
        "clipboard" => '<rect width="16" height="18" x="4" y="4" rx="2"/><path d="M9 2h6v4H9z"/><path d="M8 12h8"/><path d="M8 16h6"/>',
        "chart" => '<path d="M3 3v18h18"/><path d="M7 16v-5"/><path d="M12 16V7"/><path d="M17 16v-8"/>',
        "bell" => '<path d="M10 21h4"/><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>',
        "shield" => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>',
        "settings" => '<path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1 1.55V21a2 2 0 1 1-4 0v-.08a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1H3a2 2 0 1 1 0-4h.08a1.7 1.7 0 0 0 1.55-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.55V3a2 2 0 1 1 4 0v.08a1.7 1.7 0 0 0 1 1.55 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.28.61.88 1 1.55 1H21a2 2 0 1 1 0 4h-.08a1.7 1.7 0 0 0-1.52 1Z"/>',
        "log-out" => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        "menu" => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        "search" => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        "briefcase" => '<path d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1"/><rect width="20" height="14" x="2" y="6" rx="2"/><path d="M2 12h20"/><path d="M12 12v.01"/>',
        "calendar" => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
        "chevron-down" => '<path d="m6 9 6 6 6-6"/>',
        "alert" => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        "check" => '<path d="M20 6 9 17l-5-5"/>',
        "mail" => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/>',
        "plus" => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        "pin" => '<path d="M12 17v5"/><path d="M9 10.8 4 8l8-6 8 6-5 2.8V17H9v-6.2Z"/>',
        "bolt" => '<path d="M13 2 4 14h7l-1 8 10-13h-7l1-7Z"/>',
    ];

    if (!isset($icons[$name])) {
        return "";
    }

    return '<svg class="icon icon-' . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
}
