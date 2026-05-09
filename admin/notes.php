<?php
$pageTitle = 'Notes';
require_once __DIR__ . '/../app/includes/auth.php';
require_auth(['SUPER_ADMIN', 'ADMIN']);
render_app_page($pageTitle, 'sidebar-admin.php');
