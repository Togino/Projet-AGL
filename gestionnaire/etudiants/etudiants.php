<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

ensure_student_extra_columns($pdo);

$search = trim($_GET["search"] ?? "");
$classeId = $_GET["classe_id"] ?? "";
$niveau = $_GET["niveau"] ?? "";
$statut = $_GET["statut"] ?? "";
$classes = $pdo->query("SELECT ID, nom, niveau FROM classe ORDER BY niveau ASC, nom ASC")->fetchAll();
$niveaux = $pdo->query("SELECT DISTINCT niveau FROM classe WHERE niveau IS NOT NULL AND niveau <> '' ORDER BY niveau ASC")->fetchAll(PDO::FETCH_COLUMN);

$query = "
SELECT 
    e.MAT,
    e.annee_etude,
    e.sexe,
    e.tuteur_contact,
    e.adresse_domicile,
    u.nom,
    u.prenom,
    u.email,
    u.telephone,
    u.created_at,
    c.nom AS nom_classe,
    c.niveau,
    u.statut
FROM etudiant e
INNER JOIN utilisateur u ON e.MAT = u.MAT
LEFT JOIN classe c ON e.classe_id = c.ID
";

$conditions = [];
$params = [];

if ($search !== "") {
    $conditions[] = "(u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR e.MAT LIKE :search)";
    $params["search"] = "%$search%";
}

if ($classeId !== "") {
    $conditions[] = "e.classe_id = :classe_id";
    $params["classe_id"] = $classeId;
}

if ($niveau !== "") {
    $conditions[] = "c.niveau = :niveau";
    $params["niveau"] = $niveau;
}

if ($statut !== "" && in_array($statut, ["0", "1"], true)) {
    $conditions[] = "u.statut = :statut";
    $params["statut"] = $statut;
}

if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$etudiants = $stmt->fetchAll();

$total = count($etudiants);
$nouveauxInscrits = 0;
$totalGarcons = 0;
$totalFilles = 0;

foreach ($etudiants as $etudiant) {
    if (!empty($etudiant["created_at"]) && date("Y-m") === date("Y-m", strtotime($etudiant["created_at"]))) {
        $nouveauxInscrits++;
    }

    if ($etudiant["sexe"] === "M") {
        $totalGarcons++;
    }

    if ($etudiant["sexe"] === "F") {
        $totalFilles++;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des étudiants - EduManage</title>

    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">

<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="students-header">

        <div>
            <h1>Liste des étudiants</h1>

            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <span>Étudiants</span>
                <i data-lucide="chevron-right"></i>
                <strong>Liste des étudiants</strong>
            </div>
        </div>

        <a href="inscriptions.php" class="add-student-btn">
            <i data-lucide="plus"></i>
            Ajouter un étudiant
        </a>

    </div>

    <?php if (isset($_GET["success"]) && $_GET["success"] === "updated"): ?>
        <div class="alert-success">Etudiant modifie avec succes.</div>
    <?php endif; ?>
    <?php if (isset($_GET["success"]) && $_GET["success"] === "pending_approval"): ?>
        <div class="alert-success">Demande de desactivation envoyee au centre d'attente.</div>
    <?php endif; ?>
    <?php if (isset($_GET["error"]) && $_GET["error"] === "not_found"): ?>
        <div class="alert-error">Etudiant introuvable.</div>
    <?php endif; ?>
    <?php if (isset($_GET["error"]) && $_GET["error"] === "delete_failed"): ?>
        <div class="alert-error">Erreur lors de l'envoi de la demande de desactivation.</div>
    <?php endif; ?>

    <div class="students-stats">

        <div class="student-stat-card green">
            <div class="icon">
                <i data-lucide="users"></i>
            </div>

            <div>
                <small>Total étudiants</small>
                <h2><?= $total ?></h2>
                <span>Effectif actuel</span>
            </div>
        </div>

        <div class="student-stat-card blue">
            <div class="icon">
                <i data-lucide="mars"></i>
            </div>

            <div>
                <small>Garçons</small>
                <h2><?= $totalGarcons ?></h2>
                <span>Sexe masculin</span>
            </div>
        </div>

        <div class="student-stat-card pink">
            <div class="icon">
                <i data-lucide="venus"></i>
            </div>

            <div>
                <small>Filles</small>
                <h2><?= $totalFilles ?></h2>
                <span>Sexe feminin</span>
            </div>
        </div>

        <div class="student-stat-card orange">
            <div class="icon">
                <i data-lucide="graduation-cap"></i>
            </div>

            <div>
                <small>Nouveaux inscrits</small>
                <h2><?= $nouveauxInscrits ?></h2>
                <span>Ce mois</span>
            </div>
        </div>

    </div>

    <div class="students-table-card">

        <div class="students-filters">

            <form method="GET" class="search-form">

                <div class="search-input">
                    <i data-lucide="search"></i>

                    <input 
                        type="text"
                        name="search"
                        placeholder="Rechercher par nom, prénom, matricule, email..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>

                <select name="classe_id">
                    <option value="">Toutes les classes</option>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= htmlspecialchars($classe["ID"]) ?>" <?= (string) $classeId === (string) $classe["ID"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($classe["nom"] . " - " . $classe["niveau"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="niveau">
                    <option value="">Tous les niveaux</option>
                    <?php foreach ($niveaux as $niveauOption): ?>
                        <option value="<?= htmlspecialchars($niveauOption) ?>" <?= $niveau === $niveauOption ? "selected" : "" ?>>
                            <?= htmlspecialchars($niveauOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="1" <?= $statut === "1" ? "selected" : "" ?>>Actifs</option>
                    <option value="0" <?= $statut === "0" ? "selected" : "" ?>>Inactifs</option>
                </select>

                <button type="submit" class="filter-btn">
                    <i data-lucide="filter"></i>
                    Filtres
                </button>

            </form>

        </div>

        <div class="table-responsive">

            <table class="students-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Matricule</th>
                        <th>Nom & prénom</th>
                        <th>Classe</th>
                        <th>Niveau</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($etudiants) > 0): ?>

                    <?php foreach ($etudiants as $index => $etu): ?>

                        <tr>

                            <td><?= $index + 1 ?></td>

                            <td>
                                <div class="student-avatar">

                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($etu["prenom"], 0, 1)) ?>
                                    </div>

                                </div>
                            </td>

                            <td><?= htmlspecialchars($etu["MAT"]) ?></td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($etu["prenom"] . " " . $etu["nom"]) ?>
                                </strong>
                            </td>

                            <td><?= htmlspecialchars($etu["nom_classe"] ?? "-") ?></td>

                            <td><?= htmlspecialchars($etu["niveau"] ?? "-") ?></td>

                            <td><?= htmlspecialchars($etu["email"]) ?></td>

                            <td><?= htmlspecialchars($etu["telephone"] ?: "-") ?></td>

                            <td>

                                <?php if ($etu["statut"] == 1): ?>

                                    <span class="status active">Actif</span>

                                <?php else: ?>

                                    <span class="status inactive">Inactif</span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= !empty($etu["created_at"]) ? date("d/m/Y", strtotime($etu["created_at"])) : "-" ?>
                            </td>

                            <td>

                                <div class="table-actions">

                                    <a href="fiche-etudiant.php?mat=<?= urlencode($etu["MAT"]) ?>" title="Fiche etudiant">
                                        <i data-lucide="eye"></i>
                                    </a>

                                    <a href="modifier-etudiant.php?mat=<?= urlencode($etu["MAT"]) ?>" title="Modifier">
                                        <i data-lucide="square-pen"></i>
                                    </a>

                                    <a href="supprimer-etudiant.php?mat=<?= urlencode($etu["MAT"]) ?>" title="Desactiver" onclick="return confirm('Envoyer une demande de desactivation pour cet etudiant ?')">
                                        <i data-lucide="user-x"></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="11">

                            <div class="empty-state">

                                <i data-lucide="users"></i>

                                <h3>Aucun étudiant trouvé</h3>

                                <p>
                                    Aucun étudiant ne correspond à votre recherche.
                                </p>

                            </div>

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

</main>

<script>
lucide.createIcons();
</script>

</body>
</html>
