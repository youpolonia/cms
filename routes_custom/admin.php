<?php
/**
 * Consolidated Admin Routes
 */

require_once __DIR__ . '/../includes/core/auth.php';
require_once __DIR__ . '/../includes/core/router.php';
require_once __DIR__ . '/../includes/core/middleware/adminauth.php';

$router = new Router();

// Apply middleware stack to all admin routes
$router->middleware([
    'CsrfMiddleware::verify',
    'AdminAuth::handle'
]);

// Base admin route redirect
$router->addRoute('GET', '/admin', function() {
    header('Location: /admin/dashboard.php');
    exit;
});

// Dashboard
$router->addRoute('GET', '/admin/dashboard', function() {
    require_once __DIR__ . '/../admin/admincontroller.php';
    $controller = new AdminController();
    $controller->dashboard();
});

// Users
$router->addRoute('GET', '/admin/users', function() {
    require_once __DIR__ . '/../admin/admincontroller.php';
    $controller = new AdminController();
    $controller->listUsers();
});

// Settings
$router->addRoute('GET', '/admin/settings', function() {
    require_once __DIR__ . '/../admin/admincontroller.php';
    $controller = new AdminController();
    $controller->systemSettings();
});
