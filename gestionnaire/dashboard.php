<?php
$pageTitle = 'Tableau de bord gestionnaire';
require_once __DIR__ . '/../app/includes/auth.php';
require_auth(['GESTIONNAIRE']);
render_app_page($pageTitle, 'sidebar-gestionnaire.php');
