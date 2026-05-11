<?php
$user = current_user();
$publicPrefix = ($navPrefix ?? "") . "../public/";
?>
<header class="topbar">
    <strong><?= e($pageTitle ?? 'Scolar Sys') ?></strong>
    <nav>
        <?php if ($user): ?>
            <span><?= e($user['prenom'] . ' ' . $user['nom']) ?></span>
            <a class="button" href="<?= e($publicPrefix) ?>logout.php">Deconnexion</a>
        <?php endif; ?>
    </nav>
</header>
