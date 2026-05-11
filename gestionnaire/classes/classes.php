<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';

$classes = $pdo->query("
    SELECT 
        c.ID,
        c.nom,
        c.niveau,
        COUNT(e.MAT) AS effectif
    FROM classe c
    LEFT JOIN etudiant e ON e.classe_id = c.ID
    GROUP BY c.ID, c.nom, c.niveau
    ORDER BY c.ID DESC
")->fetchAll();

$totalClasses = count($classes);
$totalEtudiants = array_sum(array_column($classes, "effectif"));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Classes - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="app-body">

<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>

<main class="main-content">
<?php include "../../app/includes/topbar.php"; ?>

<section class="dashboard">

    <div class="classes-page-head">
        <div>
            <h1>Classes</h1>
            <div class="breadcrumb">
                <span>Accueil</span>
                <i data-lucide="chevron-right"></i>
                <strong>Classes</strong>
            </div>
        </div>
    </div>

    <?php if (isset($_GET["success"]) && $_GET["success"] === "pending_approval"): ?>
        <div class="alert-success">Demande envoyee au centre d'attente. Les autres gestionnaires doivent confirmer.</div>
    <?php endif; ?>
    <?php if (isset($_GET["info"]) && $_GET["info"] === "pending_request"): ?>
        <div class="alert-error">Une demande de suppression est deja en attente pour cette classe.</div>
    <?php endif; ?>

    <div class="classes-stats-grid">
        <div class="class-stat-box green">
            <div><i data-lucide="school"></i></div>
            <article>
                <h2><?= $totalClasses ?></h2>
                <p>Total classes</p>
                <small>Actives cette annee</small>
            </article>
        </div>

        <div class="class-stat-box blue">
            <div><i data-lucide="users"></i></div>
            <article>
                <h2><?= $totalEtudiants ?></h2>
                <p>Total etudiants</p>
                <small>Toutes les classes</small>
            </article>
        </div>

    </div>

    <div class="classes-card">
        <div class="classes-card-head">
            <div>
                <h2>Liste des classes</h2>
                <p>Consultez les classes de votre etablissement.</p>
            </div>

            <div class="classes-head-actions">
                <a href="ajouter-classe.php" class="add-class-btn">
                    <i data-lucide="plus"></i>
                    Ajouter une classe
                </a>
            </div>
        </div>

        <div class="classes-toolbar">
            <div class="show-entries">
                <span>Afficher</span>
                <select>
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
                <span>entrees</span>
            </div>

            <div class="classes-search-filter">
                <div class="classes-search">
                    <input type="text" placeholder="Rechercher...">
                    <i data-lucide="search"></i>
                </div>

                <button class="filter-class-btn">
                    <i data-lucide="filter"></i>
                    Filtrer
                </button>
            </div>
        </div>

        <div class="classes-table-wrap">
            <table class="classes-table">
                <thead>
                    <tr>
                        <th>Code classe <i data-lucide="chevrons-up-down"></i></th>
                        <th>Nom de la classe <i data-lucide="chevrons-up-down"></i></th>
                        <th>Niveau <i data-lucide="chevrons-up-down"></i></th>
                        <th>Effectif <i data-lucide="chevrons-up-down"></i></th>
                        <th>Annee scolaire <i data-lucide="chevrons-up-down"></i></th>
                        <th>Statut <i data-lucide="chevrons-up-down"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($classes as $classe): ?>
                        <tr>
                            <td class="class-code">
                                <?= "CLS-" . str_pad($classe["ID"], 3, "0", STR_PAD_LEFT) ?>
                            </td>

                            <td><?= htmlspecialchars($classe["nom"]) ?></td>

                            <td><?= htmlspecialchars($classe["niveau"]) ?></td>

                            <td><?= $classe["effectif"] ?></td>

                            <td>2024-2025</td>

                            <td>
                                <span class="status active">Active</span>
                            </td>

                            <td>
                                <div class="class-actions">
                                    <a href="classe-details.php?id=<?= $classe["ID"] ?>">
                                        <i data-lucide="eye"></i>
                                    </a>

                                    <a href="modifier-classe.php?id=<?= $classe["ID"] ?>">
                                        <i data-lucide="pencil"></i>
                                    </a>

                                    <a href="supprimer-classe.php?id=<?= $classe["ID"] ?>" onclick="return confirm('Supprimer cette classe ?')">
                                        <i data-lucide="trash-2"></i>
                                    </a>
                                </div>
                            </td>
                    <?php endforeach; ?>

                    <?php if (count($classes) === 0): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i data-lucide="school"></i>
                                    <h3>Aucune classe enregistree</h3>
                                    <p>Ajoutez votre premiere classe pour commencer.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="classes-pagination">
            <p>Affichage de 1 a <?= count($classes) ?> sur <?= count($classes) ?> entrees</p>

            <div>
                <button><i data-lucide="arrow-left"></i></button>
                <button class="active">1</button>
                <button>2</button>
                <button><i data-lucide="arrow-right"></i></button>
            </div>
        </div>
    </div>

</section>
</main>

<script>
lucide.createIcons();
</script>

</body>
</html>
