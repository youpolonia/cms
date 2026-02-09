<?php
/* SECURITY HARDENING: headers + session flags */
require_once __DIR__ . '/security_headers.php';
cms_emit_security_headers();
// Only configure session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    // Secure cookie only if HTTPS detected (no proxy assumptions here)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        @ini_set('session.cookie_secure', '1');
    }
    @ini_set('session.cookie_samesite', 'Strict');
    // Harden session cookie parameters explicitly before session_start()
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    } else {
        // Fallback for older PHP: no SameSite support, keep secure+httponly
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

if (file_exists(__DIR__ . '/eventbus.php')) {
    require_once __DIR__ . '/eventbus.php';
} elseif (file_exists(__DIR__ . '/EventBus.php')) {
    require_once __DIR__ . '/EventBus.php';
} else {
    error_log("Missing core/eventbus.php — file not found");
}
require_once __DIR__ . '/pluginsdk.php';
require_once __DIR__ . '/pluginsandbox.php';
require_once __DIR__ . '/controllerregistry.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/moduleregistry.php';

// Load routes (optional - admin panel doesn't require routing)
// Route loading disabled to prevent conflicts with standalone admin entry point

// Initialize event bus
$eventBus = \Core\EventBus::getInstance();

// Register core events
$eventBus->listen('system.init', function() {
    error_log("System initialization complete");
});

// Load plugins
$pluginsDir = __DIR__ . '/../plugins';
if (is_dir($pluginsDir)) {
    foreach (scandir($pluginsDir) as $pluginName) {
        if ($pluginName === '.' || $pluginName === '..') {
            continue;
        }
        
        $pluginPath = $pluginsDir . '/' . $pluginName;
        if (is_dir($pluginPath)) {
            try {
                \Core\ModuleRegistry::registerPlugin($pluginPath);
                $eventBus->dispatch('plugin.loaded', ['plugin' => $pluginName]);
            } catch (\Throwable $e) {
                error_log("Failed to load plugin {$pluginName}: " . $e->getMessage());
                $eventBus->dispatch('plugin.error', [
                    'plugin' => $pluginName,
                    'error' => $e->getMessage()
                ]);
        }
    }
}

// Dispatch system init event
$eventBus->dispatch('system.init');
}
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Initialize workflow components
require_once __DIR__ . '/workflowengine.php';
require_once __DIR__ . '/statustransitionhandler.php';
require_once __DIR__ . '/notificationservice.php';
if (file_exists(__DIR__ . '/content/contentstatehistorylogger.php')) {
    require_once __DIR__ . '/content/contentstatehistorylogger.php';
} else {
    error_log('Missing core/content/contentstatehistorylogger.php');
}
require_once __DIR__ . '/contentstateservice.php';

// Get database connection
require_once __DIR__ . '/../data_models/connection.php';
$db = getDatabaseConnection();

// Initialize services
$workflowEngine = WorkflowEngine::getInstance($db);

try {
    $historyLogger = ContentStateHistoryLogger::getInstance();
} catch (\Exception $e) {
    error_log('Failed to initialize ContentStateHistoryLogger: ' . $e->getMessage());
    $historyLogger = new class {
        public function logStateChange(int $contentId, string $oldState, string $newState): void {
            error_log("ContentStateChange (fallback): content_id=$contentId, old_state=$oldState, new_state=$newState");
        }
        public function getHistory(): array { return []; }
        public function clearHistory(): void {}
    };
}

$contentStateService = new ContentStateService($db, $historyLogger);

// Configure transition handler
StatusTransitionHandler::init($workflowEngine);

// Configure notification service
NotificationService::init([
    'email_enabled' => true,
    'webhook_url' => 'https://api.example.com/notifications'
]);

// Register default notification handler
NotificationService::subscribe('content_state_changed', function(array $data) {
    // Default notification handler
    error_log("Content state changed: {$data['from_state']} → {$data['to_state']}");
});

// Content module removed (2026-02-08) — articles/pages handled directly by admin controllers

// Initialize admin module
// NOTE: AdminModule::init() disabled - it calls non-existent Router::get() static method
// Admin routes are defined in config/routes.php instead
// if (file_exists(__DIR__ . '/../modules/admin/module.php')) {
//     require_once __DIR__ . '/../modules/admin/module.php';
// } else {
//     error_log('Missing modules/admin/module.php');
// }
// AdminModule::init();

// Initialize auth module
// NOTE: AuthModule::init() disabled - it calls non-existent Router::addRoute() static method
// Auth routes are defined in config/routes.php instead
// } else {
// }
// AuthModule::init();
