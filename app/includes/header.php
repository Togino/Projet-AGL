<?php
$user = current_user();
?>
<header class="topbar">
    <strong><?= e($pageTitle ?? 'Scolar Sys') ?></strong>
    <nav>
        <?php if ($user): ?>
            <span><?= e($user['prenom'] . ' ' . $user['nom']) ?></span>
            <a class="button" href="../public/logout.php">Deconnexion</a>
        <?php endif; ?>
    </nav>
</header>
