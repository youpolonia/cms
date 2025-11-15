<?php
/**
 * Admin access control bootstrap file
 */
require_once __DIR__ . '/../../core/permissionregistry.php';
require_once __DIR__.'/../../middleware/PermissionMiddleware.php';
require_once __DIR__.'/../../core/database.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

// Initialize permission middleware
$db = \core\Database::connection();
$middleware = new PermissionMiddleware($db, [
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'post' => $_POST,
    'is_ajax' => !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
]);

// Map routes to permissions
$routePermissions = [
    '/admin/dashboard' => 'system.manage',
    '/admin/content' => 'content.manage',
    '/admin/users' => 'users.manage',
    '/admin/plugins' => 'plugins.manage',
    '/admin/media' => 'media.manage',
    '/admin/settings' => 'system.settings'
];

// Get current route
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check permission if route is mapped
if (isset($routePermissions[$route])) {
    try {
        $middleware->handle($routePermissions[$route]);
    } catch (Exception $e) {
        error_log("Permission check failed: " . $e->getMessage());
        header('Location: /admin/login?error=access_denied');
        exit;
    }
}

// Generate CSRF token if not exists
$cache = new SessionCacheAdapter();
if (!$cache->has('csrf_token')) {
    $cache->set('csrf_token', bin2hex(random_bytes(32)));
}
