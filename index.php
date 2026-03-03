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
    require_once CMS_ROOT . '/core/i18n.php';
    require_once CMS_ROOT . '/core/white-label.php';
    require_once CMS_ROOT . '/core/theme-customizer.php';
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
    

    // Api\SomeController -> app/controllers/api/somecontroller.php
    if (str_starts_with($class, 'Api\\')) {
        $controllerName = substr($class, 4); // Remove "Api\"
        $file = CMS_APP . '/controllers/api/' . strtolower($controllerName) . '.php';
        if (file_exists($file)) {
            require_once $file;
            if (class_exists('App\\Controllers\\Api\\' . $controllerName)) {
                class_alias('App\\Controllers\\Api\\' . $controllerName, $class);
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

// Ensure CSRF token exists in session (needed for all admin routes)
if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
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
                    // Role-based access control
                    if (!empty($options['role'])) {
                        $requiredRole = $options['role'];
                        $middlewares[] = function() use ($requiredRole) {
                            $userRole = $_SESSION['admin_role'] ?? 'viewer';
                            $roleHierarchy = ['admin' => 3, 'editor' => 2, 'viewer' => 1];
                            $userLevel = $roleHierarchy[$userRole] ?? 0;
                            $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
                            if ($userLevel < $requiredLevel) {
                                http_response_code(403);
                                if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                                    header('Content-Type: application/json');
                                    echo json_encode(['error' => 'Insufficient permissions']);
                                } else {
                                    echo '<h1>403 — Access Denied</h1><p>You need ' . htmlspecialchars($requiredRole) . ' role or higher.</p>';
                                }
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
                                // Always parse JSON body so controllers can access it
                                // (php://input can only be read once — must cache here)
                                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                                if (stripos($contentType, 'application/json') !== false && !isset($GLOBALS['_JSON_DATA'])) {
                                    $rawInput = file_get_contents('php://input');
                                    if (!empty($rawInput)) {
                                        $data = json_decode($rawInput, true);
                                        if (is_array($data)) {
                                            if (empty($token)) {
                                                $token = $data['_token'] ?? $data['csrf_token'] ?? '';
                                            }
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
if (preg_match('#^/admin/(?:jtb/)?website-builder/?$#', $jtbUri)) {
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

// ─── Jessie Restaurant Plugin ───
if (preg_match('#^/api/restaurant/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-restaurant/api/router.php';
    exit;
}
if (preg_match('#^/admin/restaurant(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-restaurant/admin-router.php';
    exit;
}
if ($jtbUri === '/order' || $jtbUri === '/order/' || $jtbUri === '/menu' || $jtbUri === '/menu/') {
    require_once CMS_ROOT . '/plugins/jessie-restaurant/views/frontend/menu.php';
    exit;
}

// ─── Jessie Real Estate Plugin ───
if (preg_match('#^/api/realestate/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-realestate/api/router.php';
    exit;
}
if (preg_match('#^/admin/realestate(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-realestate/admin-router.php';
    exit;
}
if ($jtbUri === '/properties' || $jtbUri === '/properties/') {
    require_once CMS_ROOT . '/plugins/jessie-realestate/views/frontend/browse.php';
    exit;
}
if (preg_match('#^/properties/([\w-]+)$#', $jtbUri, $m)) {
    $propertySlug = $m[1];
    require_once CMS_ROOT . '/plugins/jessie-realestate/views/frontend/detail.php';
    exit;
}

// ─── Jessie Jobs Plugin ───
if (preg_match('#^/api/jobs/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-jobs/api/router.php';
    exit;
}
if (preg_match('#^/admin/jobs(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-jobs/admin-router.php';
    exit;
}
// Jobs public frontend
if ($jtbUri === '/jobs' || $jtbUri === '/jobs/') {
    require_once CMS_ROOT . '/plugins/jessie-jobs/views/frontend/browse.php';
    exit;
}
if (preg_match('#^/jobs/([\w-]+)$#', $jtbUri, $m)) {
    $jobSlug = $m[1];
    require_once CMS_ROOT . '/plugins/jessie-jobs/views/frontend/detail.php';
    exit;
}
// ─── Jessie Portfolio Plugin ───
if (preg_match('#^/api/portfolio/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-portfolio/api/router.php';
    exit;
}
if (preg_match('#^/admin/portfolio(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-portfolio/admin-router.php';
    exit;
}
if ($jtbUri === '/portfolio' || $jtbUri === '/portfolio/') {
    require_once CMS_ROOT . '/plugins/jessie-portfolio/views/frontend/portfolio.php';
    exit;
}
if (preg_match('#^/portfolio/([\w-]+)$#', $jtbUri, $m)) {
    $portfolioSlug = $m[1];
    require_once CMS_ROOT . '/plugins/jessie-portfolio/views/frontend/project-detail.php';
    exit;
}

// ─── Jessie Affiliate Plugin ───
if (preg_match('#^/api/affiliate/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-affiliate/api/router.php';
    exit;
}
if (preg_match('#^/admin/affiliate(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-affiliate/admin-router.php';
    exit;
}
// Affiliate frontend
if ($jtbUri === '/affiliate/register') {
    require_once CMS_ROOT . '/plugins/jessie-affiliate/views/frontend/register.php';
    exit;
}
if ($jtbUri === '/affiliate/dashboard') {
    require_once CMS_ROOT . '/plugins/jessie-affiliate/views/frontend/dashboard.php';
    exit;
}
// Referral tracking: ?ref=CODE redirect handler
if (!empty($_GET['ref']) && strlen($_GET['ref']) <= 50) {
    defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__));
    require_once CMS_ROOT . '/db.php';
    require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate-program.php';
    require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate.php';
    \Affiliate::trackClick($_GET['ref']);
    $aff = \Affiliate::getByCode($_GET['ref']);
    if ($aff) {
        $cookieDays = (int)($aff['cookie_days'] ?? 30);
        setcookie('aff_ref', $_GET['ref'], time() + ($cookieDays * 86400), '/', '', false, true);
    }
}

// ─── Jessie Events Plugin ───
if (preg_match('#^/api/events/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-events/api/router.php';
    exit;
}
if (preg_match('#^/admin/events(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-events/admin-router.php';
    exit;
}
if ($jtbUri === '/events' || $jtbUri === '/events/') {
    require_once CMS_ROOT . '/plugins/jessie-events/views/frontend/browse.php';
    exit;
}
if (preg_match('#^/events/payment-(success|cancel)/?$#', $jtbUri, $evPay)) {
    $eventPaymentAction = $evPay[1];
    require_once CMS_ROOT . '/plugins/jessie-events/views/frontend/payment-callback.php';
    exit;
}
if (preg_match('#^/events/([\w-]+)$#', $jtbUri, $m)) {
    $eventSlug = $m[1];
    require_once CMS_ROOT . '/plugins/jessie-events/views/frontend/detail.php';
    exit;
}

// ─── Jessie Directory Plugin ───
if (preg_match('#^/api/directory/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-directory/api/router.php';
    exit;
}
if (preg_match('#^/admin/directory(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-directory/admin-router.php';
    exit;
}
// Directory public frontend
if ($jtbUri === '/directory' || $jtbUri === '/directory/') {
    require_once CMS_ROOT . '/plugins/jessie-directory/views/frontend/browse.php';
    exit;
}
if ($jtbUri === '/directory/submit') {
    require_once CMS_ROOT . '/plugins/jessie-directory/views/frontend/submit.php';
    exit;
}
if (preg_match('#^/directory/([\w-]+)/claim$#', $jtbUri, $m)) {
    $directorySlug = $m[1];
    require_once CMS_ROOT . '/plugins/jessie-directory/views/frontend/claim.php';
    exit;
}
if (preg_match('#^/directory/([\w-]+)$#', $jtbUri, $m)) {
    $directorySlug = $m[1];
    require_once CMS_ROOT . '/plugins/jessie-directory/views/frontend/detail.php';
    exit;
}

// ─── Jessie LMS Plugin ───
if (preg_match('#^/api/lms/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-lms/api/router.php';
    exit;
}
if (preg_match('#^/admin/lms(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-lms/admin-router.php';
    exit;
}
if (preg_match('#^/courses/([a-z0-9-]+)$#', $jtbUri, $routeMatch)) {
    $routeParams = ['slug' => $routeMatch[1]];
    require_once CMS_ROOT . '/plugins/jessie-lms/views/frontend/course.php';
    exit;
}
if (preg_match('#^/courses/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-lms/views/frontend/catalog.php';
    exit;
}

// ─── Jessie Membership Plugin ───
if (preg_match('#^/api/membership/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-membership/api/router.php';
    exit;
}
if (preg_match('#^/admin/membership(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-membership/admin-router.php';
    exit;
}
if (preg_match('#^/membership/signup(?:/(success|cancel))?/?$#', $jtbUri, $msMatch)) {
    $membershipAction = $msMatch[1] ?? '';
    require_once CMS_ROOT . '/plugins/jessie-membership/views/frontend/signup.php';
    exit;
}
if (preg_match('#^/membership/portal/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-membership/views/frontend/portal.php';
    exit;
}
if (preg_match('#^/(membership/pricing|pricing)/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-membership/views/frontend/pricing.php';
    exit;
}

// ─── Jessie Newsletter+ Plugin ───
if (preg_match('#^/api/newsletter/([\w-]+)(?:/(\w+))?(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-newsletter/api/router.php';
    exit;
}
if (preg_match('#^/admin/newsletter(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-newsletter/admin-router.php';
    exit;
}
if (preg_match('#^/newsletter/preferences/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-newsletter/views/frontend/preferences.php';
    exit;
}
if (preg_match('#^/newsletter/unsubscribe/?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-newsletter/views/frontend/unsubscribe.php';
    exit;
}
if (preg_match('#^/newsletter/(subscribe)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-newsletter/views/frontend/subscribe-widget.php';
    exit;
}

// ─── Jessie Booking Plugin ───
if (preg_match('#^/api/booking/([\w-]+)(?:/(\d+))?$#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-booking/api/router.php';
    exit;
}
if (preg_match('#^/admin/booking(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-booking/admin-router.php';
    exit;
}
// ── SaaS Core routes ──
if (preg_match('#^/api/saas(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-saas-core/api/router.php';
    exit;
}
if (preg_match('#^/admin/saas(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-saas-core/admin-router.php';
    exit;
}
if (preg_match('#^/saas(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-saas-core/frontend-router.php';
    exit;
}
// ── SEO Writer routes ──
if (preg_match('#^/api/seowriter(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-seowriter/api/router.php';
    exit;
}
if (preg_match('#^/admin/seowriter(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-seowriter/admin-router.php';
    exit;
}
// ── Copywriter routes ──
if (preg_match('#^/api/copywriter(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-copywriter/api/router.php';
    exit;
}
if (preg_match('#^/admin/copywriter(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-copywriter/admin-router.php';
    exit;
}
// ── Image Studio routes ──
if (preg_match('#^/api/imagestudio(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-imagestudio/api/router.php';
    exit;
}
if (preg_match('#^/admin/imagestudio(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-imagestudio/admin-router.php';
    exit;
}
// ── Social Media routes ──
if (preg_match('#^/api/social(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-social/api/router.php';
    exit;
}
if (preg_match('#^/admin/social(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-social/admin-router.php';
    exit;
}
// ── Email Marketing routes ──
if (preg_match('#^/api/emailmarketing(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-emailmarketing/api/router.php';
    exit;
}
if (preg_match('#^/admin/emailmarketing(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-emailmarketing/admin-router.php';
    exit;
}
// ── Analytics routes ──
if (preg_match('#^/api/analytics(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-analytics/api/router.php';
    exit;
}
if (preg_match('#^/admin/analytics(?:/|$)#', $jtbUri)) {
    require_once CMS_ROOT . '/plugins/jessie-analytics/admin-router.php';
    exit;
}
// ─── Payment Callbacks ───
if ($jtbUri === '/checkout/success') {
    require_once CMS_ROOT . '/core/payment-gateway.php';
    require_once CMS_ROOT . '/core/shop.php';

    $provider = $_SESSION['pending_payment_provider'] ?? '';
    $pendingOrderId = (int)($_SESSION['pending_payment_order'] ?? 0);

    if ($provider === 'stripe' && !empty($_GET['session_id'])) {
        $result = PaymentGateway::verifyAndComplete('stripe', ['session_id' => $_GET['session_id']]);
        if (!empty($result['success']) && $pendingOrderId > 0) {
            PaymentGateway::markOrderPaid($pendingOrderId, $result['transaction_id'] ?? '', 'stripe');
            unset($_SESSION['pending_payment_order'], $_SESSION['pending_payment_provider']);
            $order = Shop::getOrder($pendingOrderId);
            header('Location: /order/thank-you/' . ($order['order_number'] ?? '') . '?payment=success');
            exit;
        }
    }

    // Fallback — redirect to order page
    $orderNum = $_GET['order'] ?? '';
    if ($orderNum) {
        header('Location: /order/thank-you/' . urlencode($orderNum) . '?payment=pending');
    } else {
        header('Location: /shop');
    }
    exit;
}

if ($jtbUri === '/checkout/paypal-return') {
    require_once CMS_ROOT . '/core/payment-gateway.php';
    require_once CMS_ROOT . '/core/shop.php';

    $ppOrderId = $_GET['token'] ?? '';
    $pendingOrderId = (int)($_SESSION['pending_payment_order'] ?? 0);

    if ($ppOrderId && $pendingOrderId > 0) {
        $result = PaymentGateway::verifyAndComplete('paypal', ['order_id' => $ppOrderId]);
        if (!empty($result['success'])) {
            PaymentGateway::markOrderPaid($pendingOrderId, $result['transaction_id'] ?? '', 'paypal');
            unset($_SESSION['pending_payment_order'], $_SESSION['pending_payment_provider']);
            $order = Shop::getOrder($pendingOrderId);
            header('Location: /order/thank-you/' . ($order['order_number'] ?? '') . '?payment=success');
            exit;
        }
    }

    header('Location: /shop');
    exit;
}

if ($jtbUri === '/checkout/cancel') {
    $pendingOrderId = (int)($_SESSION['pending_payment_order'] ?? 0);
    if ($pendingOrderId > 0) {
        require_once CMS_ROOT . '/core/shop.php';
        $order = Shop::getOrder($pendingOrderId);
        header('Location: /order/thank-you/' . ($order['order_number'] ?? '') . '?payment=cancelled');
        exit;
    }
    header('Location: /shop');
    exit;
}

// ─── Stripe Webhook ───
if ($jtbUri === '/webhook/stripe') {
    require_once CMS_ROOT . '/core/payment-gateway.php';
    require_once CMS_ROOT . '/core/shop.php';
    header('Content-Type: application/json');

    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    $event = PaymentGateway::stripeHandleWebhook($payload, $signature);
    if (!empty($event['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $event['error']]);
        exit;
    }

    $eventData = $event['event'] ?? [];
    $type = $eventData['type'] ?? '';

    if ($type === 'checkout.session.completed') {
        $session = $eventData['data']['object'] ?? [];
        $orderId = (int)($session['metadata']['order_id'] ?? $session['client_reference_id'] ?? 0);
        if ($orderId > 0 && ($session['payment_status'] ?? '') === 'paid') {
            PaymentGateway::markOrderPaid($orderId, $session['payment_intent'] ?? '', 'stripe');
        }
    }

    echo json_encode(['received' => true]);
    exit;
}

if (preg_match('#^/booking(?:/(success|cancel))?/?$#', $jtbUri, $bkMatch)) {
    $bookingAction = $bkMatch[1] ?? '';
    require_once CMS_ROOT . '/plugins/jessie-booking/views/frontend/booking-page.php';
    exit;
}





if (class_exists(router::class) && method_exists(router::class, 'dispatch')) {
    try {
        router::dispatch();
    } catch (\Throwable $e) {
        error_log("[CMS] Dispatch error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
        $error = (defined('CMS_DEBUG') && CMS_DEBUG) ? $e->getMessage() : '';
        require CMS_APP . '/views/front/500.php';
        exit;
    }
} else {
    // Check for ErrorDocument _error param
    $errorCode = (int)($_GET['_error'] ?? 0);
    if ($errorCode === 403) {
        require CMS_APP . '/views/front/403.php';
        exit;
    }
    http_response_code(404);
    $page = ['title' => 'Page Not Found', 'slug' => '404', 'meta_description' => ''];
    require CMS_APP . '/views/front/404.php';
    exit;
}
