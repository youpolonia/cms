<?php
/**
 * JTB API Router
 * Routes API requests to appropriate endpoint files
 *
 * Include this file in your CMS router to handle /api/jtb/* requests
 *
 * @package JessieThemeBuilder
 *
 * Usage in CMS index.php or router:
 *
 * if (preg_match('#^/api/jtb/([\w-]+)(?:/(\d+))?#', $requestUri, $matches)) {
 *     require_once CMS_ROOT . '/plugins/jessie-theme-builder/api/router.php';
 *     exit;
 * }
 */

namespace JessieThemeBuilder;

// VERY EARLY LOG - before anything else

defined('CMS_ROOT') or die('Direct access not allowed');

// Global esc() fallback - define if CMS doesn't provide it
if (!function_exists('esc')) {
    function esc(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// Use CMS session boot (same as other admin APIs)
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// Get action from URL early for auth check (supports ai/ subdirectory)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$actionMatch = [];
preg_match('#^/api/jtb/(?:(ai)/)?([\w-]+)#', $requestUri, $actionMatch);
$aiPrefix = !empty($actionMatch[1]) ? 'ai/' : '';
$currentAction = $aiPrefix . ($actionMatch[2] ?? '');

// Endpoints that don't require authentication (for iframe preview and testing)
$noAuthEndpoints = ['library-preview', 'ai/test-compose', 'ai/test-pipeline', 'test-validate', 'test-render'];

// Check authentication (same as /admin/api/tb4.php)

if (!in_array($currentAction, $noAuthEndpoints)) {
    if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }
}

// CSRF validation helper for JTB API (returns JSON instead of plain text)
function jtb_csrf_validate(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Unified JSON response helper
 * Always returns BOTH 'ok' AND 'success' keys for backward compatibility
 * AI endpoints use 'ok', standard endpoints use 'success'
 * FIX 2026-02-04: Normalize API response format
 *
 * @param bool $status Success/failure status
 * @param array $data Additional data to include
 * @param string|null $error Error message (if $status is false)
 * @param int $httpCode HTTP response code
 */
function jtb_json_response(bool $status, array $data = [], ?string $error = null, int $httpCode = 200): void {
    http_response_code($httpCode);
    header('Content-Type: application/json');

    $response = [
        'success' => $status,  // Standard format
        'ok' => $status,       // AI format (backward compat)
    ];

    if ($error !== null) {
        $response['error'] = $error;
    }

    // Merge additional data
    $response = array_merge($response, $data);

    echo json_encode($response);
}

// Validate CSRF for all POST requests (except noAuth endpoints)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($currentAction, $noAuthEndpoints)) {
    if (!jtb_csrf_validate()) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
        exit;
    }
}

// Get action and ID from URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$matches = [];

// Support both regular endpoints and ai/ subdirectory endpoints
// Pattern: /api/jtb/action or /api/jtb/ai/action or /api/jtb/action/123
if (!preg_match('#^/api/jtb/(?:(ai)/)?([\w-]+)(?:/(\d+))?#', $requestUri, $matches)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid API endpoint']);
    exit;
}

$aiPrefix = !empty($matches[1]) ? 'ai/' : '';
$action = $aiPrefix . ($matches[2] ?? '');
$id = $matches[3] ?? null;

// Debug router log

// Set ID in GET for endpoints that need it
if ($id) {
    $_GET['post_id'] = (int) $id;
}

// Plugin path
$pluginPath = dirname(__DIR__);

// Load dependencies
require_once $pluginPath . '/includes/class-jtb-element.php';
require_once $pluginPath . '/includes/class-jtb-registry.php';
require_once $pluginPath . '/includes/class-jtb-fields.php';
require_once $pluginPath . '/includes/class-jtb-fonts.php';
require_once $pluginPath . '/includes/class-jtb-default-styles.php';
require_once $pluginPath . '/includes/class-jtb-global-settings.php';  // Must be before renderer!
require_once $pluginPath . '/includes/class-jtb-renderer.php';
require_once $pluginPath . '/includes/class-jtb-css-output.php';
require_once $pluginPath . '/includes/class-jtb-settings.php';
require_once $pluginPath . '/includes/class-jtb-builder.php';
require_once $pluginPath . '/includes/class-jtb-icons.php';

// Theme Builder classes
require_once $pluginPath . '/includes/class-jtb-templates.php';
require_once $pluginPath . '/includes/class-jtb-template-conditions.php';
require_once $pluginPath . '/includes/class-jtb-global-modules.php';
require_once $pluginPath . '/includes/class-jtb-theme-settings.php';
require_once $pluginPath . '/includes/class-jtb-css-generator.php';
require_once $pluginPath . '/includes/class-jtb-style-system.php'; // Unified style system (2026-02-03)
require_once $pluginPath . '/includes/class-jtb-dynamic-context.php'; // Dynamic data for theme modules
require_once $pluginPath . '/includes/class-jtb-seo.php'; // SEO Engine (2026-02-04)
require_once $pluginPath . '/includes/class-jtb-html-parser.php'; // HTML Parser (2026-02-07)

// Template Library
require_once $pluginPath . '/includes/class-jtb-library.php';
require_once $pluginPath . '/includes/class-jtb-library-seeder.php';
require_once $pluginPath . '/includes/class-jtb-layout-library.php';

// Initialize registry
JTB_Registry::init();
JTB_Fields::init();

// Load modules
$modulesPath = $pluginPath . '/modules';

// All module categories to load
$moduleCategories = ['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth', 'theme'];

foreach ($moduleCategories as $category) {
    $categoryPath = $modulesPath . '/' . $category;
    if (is_dir($categoryPath)) {
        foreach (glob($categoryPath . '/*.php') as $moduleFile) {
            require_once $moduleFile;
        }
    }
}

// Valid endpoints
$validEndpoints = [
    // Page Builder
    'save', 'load', 'render', 'modules', 'upload', 'media-list', 'media-delete', 'create-post',
    'get-original-content', 'parse-content', 'article-layouts', 'parse-html',
    // Theme Builder - Templates
    'templates', 'template-get', 'template-save', 'template-delete',
    'template-duplicate', 'template-set-default', 'template-preview',
    // Theme Builder - Conditions
    'conditions', 'conditions-objects',
    // Theme Builder - Global Modules
    'global-modules', 'global-module-get', 'global-module-save', 'global-module-delete',
    // Theme Settings
    'theme-settings',
    // Media Browser
    'media-browser',
    // Template Library
    'library', 'library-get', 'library-save', 'library-delete',
    'library-duplicate', 'library-export', 'library-import', 'library-categories',
    'library-seed', 'library-preview', 'library-reseed',
    // Layout Gallery
    'layouts', 'layout-get', 'layout-save', 'layout-delete',
    // Layout Library (Page & Section layouts)
    'layout-library',
    // Theme Builder Layouts (header, footer, body)
    'library-theme-builder',
    // Testing
    'test-validate',
    'test-render'
];

if (!in_array($action, $validEndpoints)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $action]);
    exit;
}

// Route to API file
$apiFile = $pluginPath . '/api/' . $action . '.php';

if (file_exists($apiFile)) {
    require_once $apiFile;
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'API file not found']);
}
