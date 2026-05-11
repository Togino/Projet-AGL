<?php
require_once "../../app/includes/auth.php";
require_once "../../app/config/database.php";
require_once "../../app/helpers/functions.php";

require_auth(["GESTIONNAIRE"]);
$gestionnaireNavPrefix = '../';
$navPrefix = '../';
ensure_sensitive_action_tables($pdo);

$currentMat = $_SESSION["user"]["MAT"];
$currentRole = $_SESSION["user"]["role"] ?? "";
$error = "";
$success = "";

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $actionId = (int) ($_POST["action_id"] ?? 0);
    if ($actionId > 0) {
        try {
            $pdo->beginTransaction();

            $stmtQueue = $pdo->prepare("
                SELECT q.*, r.name AS requester_role
                FROM action_queue q
                INNER JOIN utilisateur ur ON ur.MAT = q.requested_by
                INNER JOIN roles r ON r.id = ur.role_id
                WHERE q.id = ?
                  AND q.status = 'pending'
                LIMIT 1
            ");
            $stmtQueue->execute([$actionId]);
            $queue = $stmtQueue->fetch();

            if (!$queue) {
                throw new RuntimeException("Demande introuvable ou deja traitee.");
            }
            if ($queue["requested_by"] === $currentMat) {
                throw new RuntimeException("Vous ne pouvez pas confirmer votre propre demande.");
            }
            if (!can_confirm_sensitive_action($queue["requester_role"], $currentRole)) {
                throw new RuntimeException("Vous n'etes pas autorise a confirmer cette demande.");
            }

            $insertConfirm = $pdo->prepare("
                INSERT INTO action_confirmations (action_queue_id, confirmed_by, decision)
                VALUES (?, ?, 'approve')
            ");
            $insertConfirm->execute([$actionId, $currentMat]);

            $countStmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM action_confirmations
                WHERE action_queue_id = ?
                  AND decision = 'approve'
            ");
            $countStmt->execute([$actionId]);
            $approvalCount = (int) $countStmt->fetchColumn();

            if ($approvalCount >= (int) $queue["required_confirmations"]) {
                $exec = execute_sensitive_action($pdo, $queue, $currentMat);
                $archive = $pdo->prepare("
                    INSERT INTO action_archive (
                        action_queue_id, action_type, target_type, target_id, payload, requested_by, executed_by, execution_status, execution_message
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $archive->execute([
                    $queue["id"],
                    $queue["action_type"],
                    $queue["target_type"],
                    $queue["target_id"],
                    $queue["payload"],
                    $queue["requested_by"],
                    $currentMat,
                    $exec["status"],
                    $exec["message"]
                ]);

                $done = $pdo->prepare("UPDATE action_queue SET status = ?, processed_at = NOW() WHERE id = ?");
                $done->execute([$exec["status"] === "success" ? "executed" : "failed", $queue["id"]]);

                notify_admin_and_gestionnaires(
                    $pdo,
                    "SENSITIVE_ACTION_EXECUTED",
                    "medium",
                    "Action sensible executee",
                    "Une action sensible a ete validee puis traitee (ID demande #" . $queue["id"] . ").",
                    $currentMat,
                    null
                );
            } else {
                notify_admin_and_gestionnaires(
                    $pdo,
                    "SENSITIVE_ACTION_CONFIRMED",
                    "low",
                    "Nouvelle confirmation de demande sensible",
                    "La demande #" . $queue["id"] . " a recu une nouvelle confirmation.",
                    $currentMat,
                    $currentMat
                );
            }

            $pdo->commit();
            $success = "Confirmation enregistree avec succes.";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ((int) $e->getCode() === 23000) {
                $error = "Vous avez deja confirme cette demande.";
            } else {
                $error = "Erreur lors de la confirmation.";
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

$pendingActionsStmt = $pdo->prepare("
    SELECT
        q.id,
        q.action_type,
        q.target_type,
        q.target_id,
        q.required_confirmations,
        q.requested_by,
        r.name AS requester_role,
        q.created_at,
        u.nom,
        u.prenom,
        (SELECT COUNT(*) FROM action_confirmations c WHERE c.action_queue_id = q.id AND c.decision = 'approve') AS confirmations
    FROM action_queue q
    INNER JOIN utilisateur u ON u.MAT = q.requested_by
    INNER JOIN roles r ON r.id = u.role_id
    WHERE q.status = 'pending'
    ORDER BY q.created_at DESC
");
$pendingActionsStmt->execute();
$pendingActions = $pendingActionsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Centre d'attente - EduManage</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="app-body">
<?php include "../../app/includes/sidebar-gestionnaire.php"; ?>
<main class="main-content">
    <?php include "../../app/includes/topbar.php"; ?>
    <section class="dashboard">
        <div class="dashboard-head">
            <div>
                <h1>Centre d'attente</h1>
                <p>Validez les actions sensibles avant execution.</p>
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
                        <th>Confirmations</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($pendingActions) === 0): ?>
                    <tr><td colspan="7">Aucune action en attente.</td></tr>
                <?php endif; ?>
                <?php foreach ($pendingActions as $row): ?>
                    <tr>
                        <td>#<?= (int) $row["id"] ?></td>
                        <td><?= htmlspecialchars($row["action_type"]) ?></td>
                        <td><?= htmlspecialchars($row["target_type"] . " " . $row["target_id"]) ?></td>
                        <td><?= htmlspecialchars($row["prenom"] . " " . $row["nom"]) ?></td>
                        <td><?= (int) $row["confirmations"] ?>/<?= (int) $row["required_confirmations"] ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($row["created_at"])) ?></td>
                        <td>
                            <?php if ($row["requested_by"] === $currentMat): ?>
                                <span class="status inactive">Votre demande</span>
                            <?php elseif (!can_confirm_sensitive_action($row["requester_role"], $currentRole)): ?>
                                <span class="status inactive">Non autorise</span>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="action_id" value="<?= (int) $row["id"] ?>">
                                    <button type="submit" class="edit-btn">Confirmer</button>
                                </form>
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
