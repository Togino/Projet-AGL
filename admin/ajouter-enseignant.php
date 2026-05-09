<?php
require_once "../app/includes/auth.php";
require_once "../app/config/database.php";
require_once "../app/helpers/functions.php";

if ($_SESSION["user"]["role"] !== "SUPER_ADMIN" && $_SESSION["user"]["role"] !== "ADMIN") {
    header("Location: ../public/login.php");
    exit;
}

$roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'ENSEIGNANT' LIMIT 1");
$roleStmt->execute();
$roleEnseignant = $roleStmt->fetch();

$error = "";
$nom = "";
$prenom = "";
$dateNaissance = "";
$email = "";
$specialisation = "";
$password = "enseignant123";

function generateTeacherMatricule($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE MAT LIKE 'ES-%'");
    $stmt->execute();
    $count = $stmt->fetchColumn() + 1;

    return "ES-" . str_pad($count, 4, "0", STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = clean($_POST["nom"] ?? "");
    $prenom = clean($_POST["prenom"] ?? "");
    $dateNaissance = clean($_POST["date_de_naissance"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $specialisation = clean($_POST["specialisation"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$roleEnseignant) {
        $error = "Le role ENSEIGNANT est introuvable.";
    } elseif (empty($nom) || empty($prenom) || empty($dateNaissance) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();

            $mat = generateTeacherMatricule($pdo);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare("
                INSERT INTO utilisateur
                (MAT, nom, prenom, date_de_naissance, email, motdepasse, role_id, statut, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)
            ");
            $stmtUser->execute([
                $mat,
                $nom,
                $prenom,
                $dateNaissance,
                $email,
                $hashedPassword,
                $roleEnseignant["id"],
                $_SESSION["user"]["MAT"]
            ]);

            $stmtTeacher = $pdo->prepare("INSERT INTO enseignant (MAT, specialisation) VALUES (?, ?)");
            $stmtTeacher->execute([$mat, $specialisation ?: null]);

            $stmtLog = $pdo->prepare("
                INSERT INTO security_logs (mat_user, action, description)
                VALUES (?, 'CREATE_TEACHER', ?)
            ");
            $stmtLog->execute([
                $_SESSION["user"]["MAT"],
                "Creation de l'enseignant " . $mat
            ]);

            $pdo->commit();

            header("Location: enseignants.php?success=created");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la creation. Verifiez si l'email existe deja.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter enseignant - EduManage</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="app-body">

<?php include "../app/includes/sidebar-admin.php"; ?>

<main class="main-content">
    <?php include "../app/includes/topbar.php"; ?>

    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Nouvel enseignant</h1>
                <p>Creer un compte enseignant avec sa specialisation.</p>
            </div>

            <a href="enseignants.php" class="year-btn">Retour</a>
        </div>

        <div class="form-card">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <div class="form-grid">
                    <div>
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Ex : Diallo">
                    </div>

                    <div>
                        <label>Prenom</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Ex : Mamadou">
                    </div>

                    <div>
                        <label>Date de naissance</label>
                        <input type="date" name="date_de_naissance" value="<?= htmlspecialchars($dateNaissance) ?>">
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="enseignant@email.com">
                    </div>

                    <div>
                        <label>Specialisation</label>
                        <input type="text" name="specialisation" value="<?= htmlspecialchars($specialisation) ?>" placeholder="Ex : Algorithmique">
                    </div>

                    <div>
                        <label>Mot de passe temporaire</label>
                        <input type="text" name="password" value="<?= htmlspecialchars($password) ?>" placeholder="Mot de passe temporaire">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Creer l'enseignant</button>
            </form>
        </div>
    </section>
</main>

</body>
</html>
