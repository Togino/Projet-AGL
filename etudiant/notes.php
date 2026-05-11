<?php
$pageTitle = 'Mes notes';
require_once __DIR__ . '/../app/includes/auth.php';
require_auth(['ETUDIANT']);
render_app_page($pageTitle, 'sidebar-etudiant.php');
