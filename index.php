<?php

session_start();

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Module.php';
require_once __DIR__ . '/core/ModuleRegistry.php';
require_once __DIR__ . '/core/Sidebar.php';

use Core\Database;
use Core\Auth;
use Core\Module;
use Core\Router;
use Core\ModuleRegistry;

$router   = new Router();
$auth     = Auth::getInstance();
$registry = ModuleRegistry::getInstance();

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
} catch (Throwable $e) {}

/* ── LOGIN CHECK ─────────────────────────────────────────────────────── */
$url = $_GET['url'] ?? 'dashboard';
if (!$auth->check() && $url !== 'login') {
    $url = 'login';
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

/* ── MODULE ROUTES ───────────────────────────────────────────────────── */
Module::loadRoutes($router);

/* ── DISPATCH ────────────────────────────────────────────────────────── */
$router->dispatch($url);
