<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";
ensure_optional_birthdate_for_personnel($pdo);

$adminNavPrefix = '../';
$navPrefix = '../';
if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../../public/login.php");
    exit;
}

$mat = $_GET["mat"] ?? "";

if (empty($mat)) {
    header("Location: utilisateurs.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.*, r.name AS role_name
    FROM utilisateur u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE u.MAT = ?
    AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: utilisateurs.php");
    exit;
}

$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = clean($_POST["nom"]);
    $prenom = clean($_POST["prenom"]);
    $email = clean($_POST["email"]);
    $telephone = clean($_POST["telephone"] ?? "");
    $role_id = clean($_POST["role_id"]);
    $statut = isset($_POST["statut"]) ? 1 : 0;
    $password = $_POST["password"];

    if (empty($nom) || empty($prenom) || empty($email) || empty($role_id)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();

            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmtUpdate = $pdo->prepare("
                    UPDATE utilisateur
                    SET nom = ?, prenom = ?, email = ?, telephone = ?, role_id = ?, statut = ?, motdepasse = ?, updated_by = ?
                    WHERE MAT = ?
                ");

                $stmtUpdate->execute([
                    $nom,
                    $prenom,
                    $email,
                    $telephone !== "" ? $telephone : null,
                    $role_id,
                    $statut,
                    $hashedPassword,
                    $_SESSION["user"]["MAT"],
                    $mat
                ]);
            } else {
                $stmtUpdate = $pdo->prepare("
                    UPDATE utilisateur
                    SET nom = ?, prenom = ?, email = ?, telephone = ?, role_id = ?, statut = ?, updated_by = ?
                    WHERE MAT = ?
                ");

                $stmtUpdate->execute([
                    $nom,
                    $prenom,
                    $email,
                    $telephone !== "" ? $telephone : null,
                    $role_id,
                    $statut,
                    $_SESSION["user"]["MAT"],
                    $mat
                ]);
            }

            $stmtLog = $pdo->prepare("
                INSERT INTO security_logs (mat_user, action, description)
                VALUES (?, 'UPDATE_USER', ?)
            ");
            $stmtLog->execute([
                $_SESSION["user"]["MAT"],
                "Modification de l'utilisateur $mat"
            ]);

            $payload = json_encode([
                "MAT" => $mat,
                "nom" => $nom,
                "prenom" => $prenom,
                "email" => $email,
                "telephone" => $telephone,
                "role_id" => $role_id,
                "statut" => $statut
            ], JSON_UNESCAPED_UNICODE);

            $stmtBackup = $pdo->prepare("
                INSERT INTO backup_jobs (entity_type, entity_id, action, payload, scheduled_for)
                VALUES ('utilisateur', ?, 'update', ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");
            $stmtBackup->execute([$mat, $payload]);

            $pdo->commit();

            $success = "Utilisateur modifié avec succès.";

            $stmt->execute([$mat]);
            $user = $stmt->fetch();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la modification. Vérifiez les informations.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Modifier utilisateur</h1>
                <p>Mettre à jour les informations du compte <?= htmlspecialchars($user["MAT"]) ?>.</p>
            </div>

            <a href="utilisateurs.php" class="year-btn">← Retour</a>
        </div>

        <div class="form-card">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div>
                        <label>Matricule</label>
                        <input type="text" value="<?= htmlspecialchars($user["MAT"]) ?>" disabled>
                    </div>

                    <div>
                        <label>Rôle</label>
                        <select name="role_id">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role["id"] ?>" <?= $role["id"] == $user["role_id"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($role["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($user["nom"]) ?>">
                    </div>

                    <div>
                        <label>Prénom</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($user["prenom"]) ?>">
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user["email"]) ?>">
                    </div>

                    <div>
                        <label>Téléphone</label>
                        <input type="text" name="telephone" value="<?= htmlspecialchars((string) ($user["telephone"] ?? "")) ?>" placeholder="+223 70 12 34 56">
                    </div>

                    <div>
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="password" placeholder="Laisser vide si inchangé">
                    </div>

                    <div>
                        <label>Statut du compte</label>
                        <div class="switch-box">
                            <input type="checkbox" name="statut" id="statut" <?= $user["statut"] ? "checked" : "" ?>>
                            <label for="statut">Compte actif</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Enregistrer les modifications</button>
            </form>
        </div>
    </section>
</main>

</body>
</html>