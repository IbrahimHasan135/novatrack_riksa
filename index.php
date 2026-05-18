<?php

session_start();

require_once 'core/Database.php';
require_once 'core/Router.php';
require_once 'core/Auth.php';
require_once 'core/Module.php';

$router = new Router();
$auth = new Auth();

/**
 * LOGIN CHECK
 */
$url = $_GET['url'] ?? 'dashboard';

if (!$auth->check() && $url !== 'login') {
    $url = 'login';
}

/**
 * CORE PAGES
 */

$router->get('login', function () {
    include 'views/login.php';
});

$router->get('dashboard', function () {
    include 'views/dashboard.php';
});

/**
 * LOAD MODULE ROUTES
 */

Module::loadRoutes($router);

/**
 * RUN ROUTER
 */

$router->dispatch($url);