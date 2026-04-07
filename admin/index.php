<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RoleMiddleware;
use App\Models\AdminAlert;
use App\Models\ClassRoom;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SecurityLog;
use App\Models\User;
use App\Services\AuthService;
use App\Services\AdminAlertService;
use App\Services\BackupService;
use App\Services\RoleService;
use App\Services\SecurityLogService;
use App\Services\UserService;

$appConfig = require __DIR__ . '/../app/config/app.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

session_name($appConfig['session_name']);
session_start();

$pdo = Database::getConnection();

$userModel = new User($pdo);
$roleModel = new Role($pdo);
$classRoomModel = new ClassRoom($pdo);
$adminAlertModel = new AdminAlert($pdo);
$permissionModel = new Permission($pdo);
$securityLogModel = new SecurityLog($pdo);

$backupService = new BackupService($pdo, $appConfig);
$securityLogService = new SecurityLogService($securityLogModel);
$adminAlertService = new AdminAlertService($adminAlertModel);
$roleService = new RoleService($roleModel, $permissionModel, $securityLogService);
$authService = new AuthService($userModel, $roleService, $securityLogService);
$userService = new UserService($userModel, $roleModel, $classRoomModel, $backupService, $securityLogService, $adminAlertService);

$authController = new AuthController($authService);
$userController = new UserController($userService);
$adminController = new AdminController($roleService, $adminAlertService);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/login' && $method === 'POST') {
    $authController->login();
    return;
}

if ($path === '/logout' && $method === 'POST') {
    AuthMiddleware::handle();
    CsrfMiddleware::handle($appConfig['csrf_header']);
    $authController->logout();
    return;
}

if ($path === '/me' && $method === 'GET') {
    AuthMiddleware::handle();
    $authController->me();
    return;
}

if ($path === '/admin/roles' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('roles.manage');
    $adminController->roles();
    return;
}

if ($path === '/admin/permissions' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('roles.manage');
    $adminController->permissions();
    return;
}

if ($path === '/admin/security-logs' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('security.logs.read');
    $adminController->securityLogs();
    return;
}

if ($path === '/admin/alerts' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('security.logs.read');
    $adminController->alerts();
    return;
}

if ($path === '/admin/classes' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('users.read');
    $adminController->classes($userService->listClasses());
    return;
}

if (preg_match('#^/admin/roles/(\d+)/permissions$#', $path, $matches)) {
    AuthMiddleware::handle();
    RoleMiddleware::handle('roles.manage');
    $roleId = (int) $matches[1];

    if ($method === 'GET') {
        $adminController->rolePermissions($roleId);
        return;
    }

    if ($method === 'PUT' || $method === 'PATCH' || $method === 'POST') {
        CsrfMiddleware::handle($appConfig['csrf_header']);
        $adminController->updateRolePermissions($roleId);
        return;
    }
}

if ($path === '/users' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('users.read');
    $userController->index();
    return;
}

if ($path === '/users/next-matricule' && $method === 'GET') {
    AuthMiddleware::handle();
    RoleMiddleware::handle('users.create');
    $userController->nextMatricule();
    return;
}

if ($path === '/users' && $method === 'POST') {
    AuthMiddleware::handle();
    CsrfMiddleware::handle($appConfig['csrf_header']);
    RoleMiddleware::handle('users.create');
    $userController->store();
    return;
}

if (preg_match('#^/users/([A-Z]{2}-[0-9A-Z]+)$#', $path, $matches)) {
    AuthMiddleware::handle();
    $matricule = $matches[1];

    if ($method === 'GET') {
        RoleMiddleware::handle('users.read');
        $userController->show($matricule);
        return;
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        CsrfMiddleware::handle($appConfig['csrf_header']);
        RoleMiddleware::handle('users.update');
        $userController->update($matricule);
        return;
    }

    if ($method === 'DELETE') {
        CsrfMiddleware::handle($appConfig['csrf_header']);
        RoleMiddleware::handle('users.delete');
        $userController->destroy($matricule);
        return;
    }
}

if ($path === '/favicon.ico') {
    http_response_code(204);
    return;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['message' => 'Route introuvable.'], JSON_UNESCAPED_UNICODE);
