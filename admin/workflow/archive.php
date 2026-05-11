<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

$adminNavPrefix = '../';
$navPrefix = '../';
require_auth(["SUPER_ADMIN", "ADMIN"]);
ensure_sensitive_action_tables($pdo);

$currentMat = $_SESSION["user"]["MAT"];
$currentRole = $_SESSION["user"]["role"] ?? "";
$error = "";
$success = "";

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && ($_POST["action"] ?? "") === "cancel_archive") {
    $archiveId = (int) ($_POST["archive_id"] ?? 0);
    if ($archiveId > 0) {
        try {
            $pdo->beginTransaction();
            $stmtOne = $pdo->prepare("
                SELECT a.*, rr.name AS requester_role
                FROM action_archive a
                INNER JOIN utilisateur ru ON ru.MAT = a.requested_by
                INNER JOIN roles rr ON rr.id = ru.role_id
                WHERE a.id = ?
                LIMIT 1
            ");
            $stmtOne->execute([$archiveId]);
            $item = $stmtOne->fetch();
            if (!$item) {
                throw new RuntimeException("Archive introuvable.");
            }
            if ($item["execution_status"] !== "success") {
                throw new RuntimeException("Seules les actions executees peuvent etre annulees.");
            }
            if (!can_cancel_archived_action($item["requester_role"], $currentRole)) {
                throw new RuntimeException("Vous ne pouvez pas annuler cette action.");
            }

            $rest = restore_archived_action($pdo, $item, $currentMat);
            if ($rest["status"] !== "success") {
                throw new RuntimeException($rest["message"]);
            }

            $pdo->prepare("
                INSERT INTO action_archive (
                    action_queue_id, action_type, target_type, target_id, payload, requested_by, executed_by, execution_status, execution_message
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'success', ?)
            ")->execute([
                $item["action_queue_id"],
                "CANCEL_" . $item["action_type"],
                $item["target_type"],
                $item["target_id"],
                $item["payload"],
                $item["requested_by"],
                $currentMat,
                "Annulation archive: " . $rest["message"]
            ]);

            notify_admin_and_gestionnaires(
                $pdo,
                "ARCHIVE_CANCELLED",
                "medium",
                "Action annulee depuis archive",
                "L'action archivee #" . $archiveId . " a ete annulee par " . $currentMat . ".",
                $currentMat,
                null
            );

            $pdo->commit();
            $success = "Action annulee avec succes.";
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

$archives = $pdo->query("
    SELECT
        a.id,
        a.action_queue_id,
        a.action_type,
        a.target_type,
        a.target_id,
        a.payload,
        a.execution_status,
        a.execution_message,
        a.created_at,
        ru.nom AS req_nom,
        ru.prenom AS req_prenom,
        rr.name AS requester_role,
        eu.nom AS exec_nom,
        eu.prenom AS exec_prenom
    FROM action_archive a
    INNER JOIN utilisateur ru ON ru.MAT = a.requested_by
    INNER JOIN roles rr ON rr.id = ru.role_id
    LEFT JOIN utilisateur eu ON eu.MAT = a.executed_by
    ORDER BY a.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Archive - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-admin.php"; ?>
<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>
    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Archive</h1>
                <p>Historique des actions sensibles executees.</p>
            </div>
        </div>
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        <th>Cible</th>
                        <th>Demandeur</th>
                        <th>Executee par</th>
                        <th>Statut</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Annuler</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($archives) === 0): ?>
                    <tr><td colspan="9">Aucune archive.</td></tr>
                <?php endif; ?>
                <?php foreach ($archives as $row): ?>
                    <tr>
                        <td>#<?= (int) $row["id"] ?></td>
                        <td><?= htmlspecialchars($row["action_type"]) ?></td>
                        <td><?= htmlspecialchars($row["target_type"] . " " . $row["target_id"]) ?></td>
                        <td><?= htmlspecialchars(trim(($row["req_prenom"] ?? "") . " " . ($row["req_nom"] ?? ""))) ?></td>
                        <td><?= htmlspecialchars(trim(($row["exec_prenom"] ?? "") . " " . ($row["exec_nom"] ?? "")) ?: "-") ?></td>
                        <td><?= htmlspecialchars($row["execution_status"]) ?></td>
                        <td><?= htmlspecialchars($row["execution_message"] ?: "-") ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($row["created_at"])) ?></td>
                        <td>
                            <?php if ($row["execution_status"] === "success" && can_cancel_archived_action($row["requester_role"], $currentRole)): ?>
                                <form method="POST" onsubmit="return confirm('Annuler cette action archivee ?');">
                                    <input type="hidden" name="action" value="cancel_archive">
                                    <input type="hidden" name="archive_id" value="<?= (int) $row["id"] ?>">
                                    <button type="submit" class="delete-btn">Annuler</button>
                                </form>
                            <?php else: ?>
                                <span class="status inactive">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
