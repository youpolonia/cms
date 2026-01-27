<?php
/**
 * Admin Routes
 */

// Dev Tools Routes
if (isset($_GET['action']) && $_GET['action'] === 'dev-tools') {
    require_once __DIR__ . '/controllers/devtoolscontroller.php';
    $controller = new Admin\Controllers\DevToolsController();
    $controller->eventMonitor();
    exit;
}
