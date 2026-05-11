<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

$adminNavPrefix = '../';
$navPrefix = '../';
require_auth(["SUPER_ADMIN", "ADMIN"]);
ensure_student_extra_columns($pdo);

$mat = $_GET["mat"] ?? "";
if ($mat === "") {
    header("Location: etudiants.php?error=not_found");
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.MAT, e.annee_etude, e.sexe, e.tuteur_nom, e.tuteur_prenom, e.tuteur_contact, e.adresse_domicile,
           c.nom AS nom_classe, c.niveau,
           u.nom, u.prenom, u.email, u.telephone, u.date_de_naissance, u.statut
    FROM etudiant e
    INNER JOIN utilisateur u ON u.MAT = e.MAT
    LEFT JOIN classe c ON c.ID = e.classe_id
    WHERE e.MAT = ? AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$mat]);
$etu = $stmt->fetch();
if (!$etu) {
    header("Location: etudiants.php?error=not_found");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche etudiant</title>
    <style>
        body { background:#f2f2f2; margin:0; font-family: Arial, sans-serif; color:#000; }
        .sheet { width: 850px; max-width: calc(100% - 40px); margin: 24px auto; background:#fff; color:#000; padding:32px; border:1px solid #ddd; }
        h1 { margin:0 0 18px 0; font-size: 26px; }
        .grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px 20px; }
        .full { grid-column: 1 / -1; }
        .line { border-bottom: 1px solid #eee; padding:6px 0; }
        .lbl { font-weight:700; display:inline-block; min-width:170px; }
        .actions { width:850px; max-width: calc(100% - 40px); margin: 0 auto 24px; }
        a { color:#000; text-decoration:none; border:1px solid #000; padding:8px 12px; display:inline-block; background:#fff; }
    </style>
</head>
<body>
<div class="actions"><a href="etudiants.php">Retour</a></div>
<div class="sheet">
    <h1>Fiche etudiant</h1>
    <div class="grid">
        <div class="line"><span class="lbl">Matricule:</span><?= htmlspecialchars($etu["MAT"]) ?></div>
        <div class="line"><span class="lbl">Statut:</span><?= ((int) $etu["statut"] === 1) ? "Actif" : "Inactif" ?></div>
        <div class="line"><span class="lbl">Nom:</span><?= htmlspecialchars($etu["nom"]) ?></div>
        <div class="line"><span class="lbl">Prenom:</span><?= htmlspecialchars($etu["prenom"]) ?></div>
        <div class="line"><span class="lbl">Date de naissance:</span><?= htmlspecialchars($etu["date_de_naissance"]) ?></div>
        <div class="line"><span class="lbl">Sexe:</span><?= htmlspecialchars($etu["sexe"] ?: "-") ?></div>
        <div class="line"><span class="lbl">Email:</span><?= htmlspecialchars($etu["email"]) ?></div>
        <div class="line"><span class="lbl">Telephone:</span><?= htmlspecialchars($etu["telephone"] ?: "-") ?></div>
        <div class="line"><span class="lbl">Classe:</span><?= htmlspecialchars(($etu["nom_classe"] ?? "-") . " - " . ($etu["niveau"] ?? "-")) ?></div>
        <div class="line"><span class="lbl">Annee d'etude:</span><?= htmlspecialchars((string) $etu["annee_etude"]) ?></div>
        <div class="line"><span class="lbl">Nom du parent/tuteur:</span><?= htmlspecialchars($etu["tuteur_nom"] ?: "-") ?></div>
        <div class="line"><span class="lbl">Prenom du parent/tuteur:</span><?= htmlspecialchars($etu["tuteur_prenom"] ?: "-") ?></div>
        <div class="line"><span class="lbl">Contact du parent/tuteur:</span><?= htmlspecialchars($etu["tuteur_contact"] ?: "-") ?></div>
        <div class="line full"><span class="lbl">Adresse:</span><?= htmlspecialchars($etu["adresse_domicile"] ?: "-") ?></div>
    </div>
</div>
</body>
</html>
