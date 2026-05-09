<?php
$unreadTopbarAlerts = 0;
$currentRole = $_SESSION["user"]["role"] ?? "";
$alertsHref = $currentRole === "ENSEIGNANT" || $currentRole === "ETUDIANT" ? "dashboard.php" : "alertes.php";
$settingsHref = in_array($currentRole, ["ENSEIGNANT", "ETUDIANT", "GESTIONNAIRE", "SUPER_ADMIN", "ADMIN"], true) ? "profil.php" : "parametres.php";
$roleLabels = [
    "SUPER_ADMIN" => "Super administrateur",
    "ADMIN" => "Administrateur",
    "GESTIONNAIRE" => "Gestionnaire",
    "ENSEIGNANT" => "Enseignant",
    "ETUDIANT" => "Etudiant",
];
$roleLabel = $roleLabels[$currentRole] ?? "Utilisateur";

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $unreadTopbarAlerts = (int) $pdo
            ->query("SELECT COUNT(*) FROM admin_alerts WHERE is_read = 0")
            ->fetchColumn();
    } catch (PDOException $e) {
        $unreadTopbarAlerts = 0;
    }
}
?>

<header class="topbar">
    <button class="menu-btn" type="button" aria-label="Ouvrir ou fermer le menu" aria-expanded="true"><?= ui_icon("menu") ?></button>

    <div class="search-box">
        <?= ui_icon("search") ?>
        <input type="text" placeholder="Rechercher un etudiant, une classe, un module...">
    </div>

    <div class="top-actions">
        <a class="notif" href="<?= htmlspecialchars($alertsHref) ?>" title="Alertes et notifications" aria-label="Alertes et notifications">
            <?= ui_icon("bell") ?>
            <?php if ($unreadTopbarAlerts > 0): ?>
                <span><?= $unreadTopbarAlerts > 99 ? "99+" : $unreadTopbarAlerts ?></span>
            <?php endif; ?>
        </a>

        <a class="settings" href="<?= htmlspecialchars($settingsHref) ?>" title="Parametres" aria-label="Parametres">
            <?= ui_icon("settings") ?>
        </a>

        <div class="admin-profile">
            <div class="avatar"><?= ui_icon("briefcase") ?></div>
            <div>
                <strong><?= $_SESSION["user"]["prenom"] ?? "Admin" ?></strong>
                <small><?= htmlspecialchars($roleLabel) ?></small>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const menuButton = document.querySelector(".menu-btn");

    if (!menuButton) return;

    const savedState = localStorage.getItem("sidebar-collapsed");

    if (savedState === "1") {
        document.body.classList.add("sidebar-collapsed");
        menuButton.setAttribute("aria-expanded", "false");
    }

    menuButton.addEventListener("click", () => {
        const collapsed = document.body.classList.toggle("sidebar-collapsed");
        menuButton.setAttribute("aria-expanded", collapsed ? "false" : "true");
        localStorage.setItem("sidebar-collapsed", collapsed ? "1" : "0");
    });
});
</script>
