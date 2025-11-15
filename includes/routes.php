<?php
require_once __DIR__ . '/security/authmiddleware.php';
require_once __DIR__ . '/security/adminmiddleware.php';

// Existing routes...

// Add WebSocket route
$router->addRoute('GET', '/ws-presence', function() {
    $container = require_once __DIR__ . '/container.php';
    return new \CMS\Core\Response('WebSocket connection established', 200);
});

// Admin dashboard route
$router->addRoute('GET', '/admin/dashboard', function() {
    require_once __DIR__ . '/services/admindashboardcontroller.php';
    (new AdminDashboardController())->index();
}, [authMiddleware(), adminMiddleware()]);
