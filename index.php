<?php

session_start();

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Module.php';
require_once __DIR__ . '/core/ModuleRegistry.php';
require_once __DIR__ . '/core/Sidebar.php';
require_once __DIR__ . '/core/Rbac.php';

use Core\Database;
use Core\Auth;
use Core\Module;
use Core\Router;
use Core\ModuleRegistry;
use Core\Rbac;

$router   = new Router();
$auth     = Auth::getInstance();
$registry = ModuleRegistry::getInstance();
$rbac     = new Rbac();

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base === '' || $base === '.') {
            $base = '';
        }

        return $base . '/' . ltrim($path, '/');
    }
}

/* ── BOOT ALL MODULES ───────────────────────────────────────────────── */
Module::bootAll($registry);

/* ── AUTO-MIGRATE TABLE ──────────────────────────────────────────────── */
try {
    Database::autoMigrate();
    $rbac->migrate();
} catch (Throwable $e) {}

/* ── LOGIN CHECK ─────────────────────────────────────────────────────── */
$url = $_GET['url'] ?? 'dashboard';
if (!$auth->check() && $url !== 'login') {
    $url = 'login';
}
if ($auth->check()) {
    $firstSegment = explode('/', trim($url, '/'))[0] ?? '';
    $coreSegments = ['dashboard', 'login', 'logout', 'roles', 'users', 'profile', ''];
    if (!in_array($firstSegment, $coreSegments, true) && !$rbac->canAccessModule($firstSegment)) {
        http_response_code(403);
        echo '403 - Module tidak tersedia untuk role Anda';
        return;
    }
}

/* ── CORE ROUTES ─────────────────────────────────────────────────────── */
$router->get('login', function () use ($auth) {
    if ($auth->check()) { header('Location: ' . app_url('dashboard')); return; }
    require __DIR__ . '/views/login.php';
});

$router->post('login', function () use ($auth) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    header('Content-Type: application/json');
    echo json_encode(
        $auth->attempt($username, $password)
            ? ['success' => true, 'redirect' => app_url('dashboard')]
            : ['success' => false, 'message' => 'Username atau password salah']
    );
});

$router->get('logout', function () use ($auth) {
    $auth->logout();
    header('Location: ' . app_url('login'));
});

$router->get('dashboard', function () use ($registry, $auth) {
    $user = $auth->user();
    require __DIR__ . '/views/dashboard.php';
});

$router->get('roles', function () use ($rbac, $registry) {
    if (!$rbac->canManageRoles()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $roles = $rbac->allRoles();
    $modules = $registry->allModules();
    require __DIR__ . '/views/roles/index.php';
});

$router->get('roles/edit/{id}', function (int $id) use ($rbac, $registry) {
    if (!$rbac->canManageRoles()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $role = $rbac->getRole($id);
    if (!$role) { http_response_code(404); echo 'Role tidak ditemukan'; return; }
    $roles = $rbac->allRoles();
    $modules = $registry->allModules();
    $enabledModules = $rbac->modulesForRole($id);
    $creatableRoleIds = $rbac->creatableRolesForRole($id);
    require __DIR__ . '/views/roles/form.php';
});

$router->post('roles', function () use ($rbac) {
    if (!$rbac->canManageRoles()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $rbac->saveRole(trim($_POST['name'] ?? ''), $_POST['modules'] ?? [], $_POST['creatable_roles'] ?? []);
    header('Location: ' . app_url('roles?created=1'));
});

$router->post('roles/update/{id}', function (int $id) use ($rbac) {
    if (!$rbac->canManageRoles()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $rbac->saveRole(trim($_POST['name'] ?? ''), $_POST['modules'] ?? [], $_POST['creatable_roles'] ?? [], $id);
    header('Location: ' . app_url('roles?updated=1'));
});

$router->post('roles/delete/{id}', function (int $id) use ($rbac) {
    if (!$rbac->canManageRoles()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $ok = $rbac->deleteRole($id);
    header('Location: ' . app_url('roles?' . ($ok ? 'deleted=1' : 'error=delete')));
});

$router->get('users', function () use ($rbac) {
    if (!$rbac->canManageUsers()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $users = $rbac->manageableUsers();
    $roles = $rbac->allRoles();
    require __DIR__ . '/views/users/index.php';
});

$router->post('users', function () use ($rbac) {
    if (!$rbac->canManageUsers()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $ok = $rbac->createUser($_POST);
    header('Location: ' . app_url('users?' . ($ok ? 'created=1' : 'error=role')));
});

$router->get('users/edit/{id}', function (int $id) use ($rbac) {
    if (!$rbac->canManageUsers()) { http_response_code(403); echo '403 - Forbidden'; return; }
    if (!$rbac->canManageUserId($id)) { http_response_code(403); echo '403 - Forbidden'; return; }
    $editUser = $rbac->getUser($id);
    if (!$editUser) { http_response_code(404); echo 'User tidak ditemukan'; return; }
    require __DIR__ . '/views/users/edit.php';
});

$router->post('users/update/{id}', function (int $id) use ($rbac) {
    if (!$rbac->canManageUsers()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $ok = $rbac->updateUserAccount($id, $_POST);
    header('Location: ' . app_url('users?' . ($ok ? 'updated=1' : 'error=update')));
});

$router->post('users/delete/{id}', function (int $id) use ($rbac) {
    if (!$rbac->canManageUsers()) { http_response_code(403); echo '403 - Forbidden'; return; }
    $ok = $rbac->deleteUserAccount($id);
    header('Location: ' . app_url('users?' . ($ok ? 'deleted=1' : 'error=delete')));
});

$router->get('profile', function () use ($auth) {
    $user = $auth->user();
    require __DIR__ . '/views/profile.php';
});

$router->post('profile/password', function () use ($rbac, $auth) {
    $user = $auth->user();
    $ok = $user ? $rbac->updateOwnPassword((int)$user['id'], $_POST['current_password'] ?? '', $_POST['new_password'] ?? '') : false;
    header('Location: ' . app_url('profile?tab=password&' . ($ok ? 'updated=1' : 'error=password')));
});

/* ── MODULE ROUTES ───────────────────────────────────────────────────── */
Module::loadRoutes($router);

/* ── DISPATCH ────────────────────────────────────────────────────────── */
$router->dispatch($url);
