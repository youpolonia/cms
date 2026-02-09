<?php

/**
 * CMS Main Entry Point
 * Main entry point — MVC routing via config/routes.php
 */


use Core\Router as router;

// ============================================================
// BOOTSTRAP AND CONSTANTS
// ============================================================
if (file_exists(__DIR__ . '/core/bootstrap.php')) {
    require_once __DIR__ . '/core/bootstrap.php';
} else {
    error_log("Missing core/bootstrap.php — file not found");
}

require_once __DIR__ . '/core/error_handler.php';
cms_register_error_handlers();

// Define MVC constants if not already defined
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', __DIR__);
}
if (!defined('CMS_APP')) {
    define('CMS_APP', __DIR__ . '/app');
}
if (!defined('CMS_CORE')) {
    define('CMS_CORE', __DIR__ . '/core');
}
if (!defined('CMS_CONFIG')) {
    define('CMS_CONFIG', __DIR__ . '/config');
}

// Required includes
require_once __DIR__ . '/version.php';
require_once __DIR__ . '/models/settingsmodel.php';
require_once __DIR__ . '/includes/thememanager.php';
require_once __DIR__ . '/core/controllerregistry.php';
require_once __DIR__ . '/core/router.php';
require_once __DIR__ . '/includes/middleware/validationmiddleware.php';

/**
 * Get JSON input data from POST request
 * Handles the php://input read-once limitation by caching in globals
 */
if (!function_exists('get_json_input')) {
    function get_json_input(): ?array {
        // Return cached data if available (set by CSRF middleware)
        if (isset($GLOBALS['_JSON_DATA']) && is_array($GLOBALS['_JSON_DATA'])) {
            return $GLOBALS['_JSON_DATA'];
        }
        // Fallback: try to read php://input directly
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $GLOBALS['_JSON_DATA'] = $data;
                $GLOBALS['_JSON_INPUT'] = $raw;
                return $data;
            }
        }
        return null;
    }
}

// ============================================================
// MVC SUPPORT
// ============================================================
// Load MVC support classes
require_once __DIR__ . '/core/request.php';
require_once __DIR__ . '/core/response.php';
require_once __DIR__ . '/core/session.php';
require_once __DIR__ . '/core/csrf.php';

// Load MVC helpers (db(), esc(), render(), etc.)
if (file_exists(CMS_APP . '/helpers/functions.php')) {
    require_once CMS_APP . '/helpers/functions.php';
}

// Load menu helper for render_menu() function
if (file_exists(__DIR__ . '/includes/helpers/menu.php')) {
    require_once __DIR__ . '/includes/helpers/menu.php';
}

// Autoloader for Admin MVC controllers
spl_autoload_register(function($class) {
    // Skip if class is null or not a string
    if (!is_string($class) || $class === '') {
        return;
    }

    // Admin\SomeController -> app/controllers/admin/somecontroller.php
    if (str_starts_with($class, 'Admin\\')) {
        $controllerName = substr($class, 6); // Remove "Admin\"
        $file = CMS_APP . '/controllers/admin/' . strtolower($controllerName) . '.php';
        if (file_exists($file)) {
            require_once $file;
            // Class uses different namespace - alias it
            if (class_exists('App\\Controllers\\Admin\\' . $controllerName)) {
                class_alias('App\\Controllers\\Admin\\' . $controllerName, $class);
            }
        }
    }
    
    // Front\SomeController -> app/controllers/front/somecontroller.php
    if (str_starts_with($class, 'Front\\')) {
        $controllerName = substr($class, 6); // Remove "Front\"
        $file = CMS_APP . '/controllers/front/' . strtolower($controllerName) . '.php';
        if (file_exists($file)) {
            require_once $file;
            // Class uses different namespace - alias it
            if (class_exists('App\\Controllers\\Front\\' . $controllerName)) {
                class_alias('App\\Controllers\\Front\\' . $controllerName, $class);
            }
        }
    }
});

// Start session for MVC if not started
if (session_status() === PHP_SESSION_NONE) {
    \Core\Session::start();
}



// ============================================================
// ROUTE REGISTRATION
// ============================================================
// Helper functions for route definitions
if (!function_exists('get')) {
    function get(string $path, callable $handler): void {
        \Core\Router::get($path, $handler);
    }
}
if (!function_exists('router_get')) {
    function router_get(string $path, callable $handler): void {
        \Core\Router::get($path, $handler);
    }
}



// Load routes from config/routes.php
$mvcRoutesFile = CMS_CONFIG . '/routes.php';
if (file_exists($mvcRoutesFile)) {
    $mvcRoutes = require $mvcRoutesFile;
    if (is_array($mvcRoutes)) {
        foreach ($mvcRoutes as $key => $handler) {
            // Parse "METHOD /path" format
            $parts = explode(' ', $key, 2);
            if (count($parts) === 2) {
                $method = $parts[0];
                $path = $parts[1];

                // Extract middleware options if present (3rd array element)
                $middlewares = [];
                if (is_array($handler) && count($handler) === 3) {
                    $options = $handler[2];
                    // Build middleware array based on options
                    if (!empty($options['auth'])) {
                        $middlewares[] = function() {
                            if (!\Core\Session::isLoggedIn()) {
                                // Return JSON for AJAX requests instead of redirect
                                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                                $xhrHeader = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
                                $isAjax = stripos($contentType, 'application/json') !== false
                                       || strtolower($xhrHeader) === 'xmlhttprequest';
                                if ($isAjax) {
                                    http_response_code(401);
                                    header('Content-Type: application/json');
                                    echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']);
                                    exit;
                                }
                                \Core\Response::redirect('/admin/login');
                                exit;
                            }
                        };
                    }
                    if (!empty($options['csrf'])) {
                        $middlewares[] = function() {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                // Check header first
                                $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
                                // Then check POST (for form submissions)
                                if (empty($token)) {
                                    $token = $_POST['_token'] ?? $_POST['csrf_token'] ?? '';
                                }
                                // Finally check JSON body (for AJAX requests)
                                if (empty($token)) {
                                    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                                    if (stripos($contentType, 'application/json') !== false) {
                                        $rawInput = file_get_contents('php://input');
                                        $data = json_decode($rawInput, true);
                                        if (is_array($data)) {
                                            $token = $data['_token'] ?? $data['csrf_token'] ?? '';
                                            // Store raw input for controller to reuse (php://input can only be read once)
                                            $GLOBALS['_JSON_INPUT'] = $rawInput;
                                            $GLOBALS['_JSON_DATA'] = $data;
                                        }
                                    }
                                }
                                if (!csrf_validate($token)) {
                                    http_response_code(403);
                                    header('Content-Type: application/json');
                                    echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
                                    exit;
                                }
                            }
                        };
                    }
                    $handler = [$handler[0], $handler[1]];
                }

                // Set middleware and add route
                if (!empty($middlewares)) {
                    \Core\Router::middleware($middlewares);
                }
                \Core\Router::addRoute($method, $path, $handler);
                if (!empty($middlewares)) {
                    \Core\Router::middleware([]); // Reset middleware
                }
            }
        }
    }
}


// Guarded optional load of custom web routes (DEV only)
if (defined('DEV_MODE') && DEV_MODE === true) {
    $p = __DIR__ . '/routes_custom/web.php';
    if (is_file($p)) { require_once $p; }
}

// ============================================================
// REQUEST DISPATCH
// ============================================================
$validator = new ValidationMiddleware(null);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$qpos = strpos($uri, '?');
if ($qpos !== false) { $uri = substr($uri, 0, $qpos); }
if ($uri === '' || $uri === '/index.php') { $uri = '/'; }
$validation_ok = $validator->validate($method, $uri);
if (!$validation_ok) { http_response_code(400); }



// ============================================================
// JTB (JESSIE THEME BUILDER) ROUTES
// ============================================================
$jtbUri = $_SERVER["REQUEST_URI"] ?? "/";
$jtbQpos = strpos($jtbUri, "?");
if ($jtbQpos !== false) { $jtbUri = substr($jtbUri, 0, $jtbQpos); }

// JTB API Routes
if (preg_match('#^/api/jtb/(?:ai/)?([\w-]+)(?:/(\d+))?$#', $jtbUri, $jtbMatches)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/api/router.php';
    exit;
}

// JTB Admin List
if (preg_match('#^/admin/jessie-theme-builder/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/admin.php';
    exit;
}

// JTB Builder Page
if (preg_match('#^/admin/jessie-theme-builder/edit/(\d+)$#', $jtbUri, $jtbMatches)) {
    $_GET['post_id'] = $jtbMatches[1];
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controller.php';
    exit;
}


// JTB Theme Builder - Templates Dashboard
if (preg_match('#^/admin/jtb/templates/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->index();
    exit;
}

// JTB Theme Builder - Template Editor
if (preg_match('#^/admin/jtb/template/edit(?:/(\d+))?$#', $jtbUri, $jtbMatches)) {
    $_GET['template_id'] = $jtbMatches[1] ?? null;
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->edit($_GET['template_id'] ?? null);
    exit;
}

// JTB Theme Builder - Theme Settings
if (preg_match('#^/admin/jtb/theme-settings/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->themeSettings();
    exit;
}

// JTB Theme Builder - Library
if (preg_match('#^/admin/jtb/library/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->library();
    exit;
}

// JTB Theme Builder - Global Modules
if (preg_match('#^/admin/jtb/global-modules/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->globalModules();
    exit;
}


// JTB Website Builder - Unified Theme Builder
if (preg_match('#^/admin/jtb/website-builder/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/controllers/template-controller.php';
    (new \JessieThemeBuilder\TemplateController())->websiteBuilder();
    exit;
}


// JTB Website Editor - Unified click-to-edit editor (NEW 2026-02-07)
if (preg_match('#^/admin/jtb/website-editor/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/views/website-editor.php';
    exit;
}

// JTB Website Preview
if (preg_match('#^/preview/website/?$#', $uri)) {
    require_once CMS_ROOT . '/plugins/jessie-theme-builder/preview-website.php';
    exit;
}





if (class_exists(router::class) && method_exists(router::class, 'dispatch')) {
    try {
        router::dispatch();
    } catch (\Throwable $e) {
        error_log("[CMS] Dispatch error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "
" . $e->getTraceAsString());
        http_response_code(500);
    }
} else {
    http_response_code(404);
}
