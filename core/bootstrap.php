<?php
/* SECURITY HARDENING: headers + session flags */
require_once __DIR__ . '/security_headers.php';
cms_emit_security_headers();
// Only configure session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        @ini_set('session.cookie_secure', '1');
    }
    @ini_set('session.cookie_samesite', 'Strict');
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    } else {
        @session_set_cookie_params(0, '/; HttpOnly', '', true, true);
    }
}

// Define BASE_URL for asset and link normalization
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    define('BASE_URL', $protocol . $host . $base);
}

// Core classes
if (file_exists(__DIR__ . '/eventbus.php')) {
    require_once __DIR__ . '/eventbus.php';
} elseif (file_exists(__DIR__ . '/EventBus.php')) {
    require_once __DIR__ . '/EventBus.php';
}
require_once __DIR__ . '/pluginsdk.php';
require_once __DIR__ . '/pluginsandbox.php';
require_once __DIR__ . '/controllerregistry.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/moduleregistry.php';

// Initialize event bus
$eventBus = \Core\EventBus::getInstance();
$eventBus->listen('system.init', function() {
    error_log("System initialization complete");
});

// Load plugins
$pluginsDir = __DIR__ . '/../plugins';
if (is_dir($pluginsDir)) {
    foreach (scandir($pluginsDir) as $pluginName) {
        if ($pluginName === '.' || $pluginName === '..') continue;
        $pluginPath = $pluginsDir . '/' . $pluginName;
        if (is_dir($pluginPath)) {
            try {
                \Core\ModuleRegistry::registerPlugin($pluginPath);
                $eventBus->dispatch('plugin.loaded', ['plugin' => $pluginName]);
            } catch (\Throwable $e) {
                error_log("Failed to load plugin {$pluginName}: " . $e->getMessage());
            }
        }
    }
}

// Dispatch system init event
$eventBus->dispatch('system.init');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Database connection helper
require_once __DIR__ . '/database.php';
$db = \core\Database::connection();
