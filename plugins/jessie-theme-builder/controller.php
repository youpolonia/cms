<?php
/**
 * JTB Controller
 * Handles builder page requests for both pages and articles
 *
 * @package JessieThemeBuilder
 *
 * Usage in CMS router:
 *
 * if (preg_match('#^/admin/jtb/edit/(\d+)#', $requestUri, $matches)) {
 *     $_GET['post_id'] = $matches[1];
 *     require_once CMS_ROOT . '/plugins/jessie-theme-builder/controller.php';
 *     exit;
 * }
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Auth check
if (!\Core\Session::isLoggedIn()) {
    header('Location: /admin/login');
    exit;
}

// Get post ID and type
$postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
$postType = isset($_GET['type']) ? $_GET['type'] : 'page';
$importSource = isset($_GET['import']) ? $_GET['import'] : null;

// Validate type
if (!in_array($postType, ['page', 'article'])) {
    $postType = 'page';
}

// Allow post_id=0 for AI Theme Builder imports (content comes from sessionStorage)
$isImportMode = ($importSource === 'ai-theme-builder' && $postId === 0);

if ($postId <= 0 && !$isImportMode) {
    header('Location: /admin/' . ($postType === 'article' ? 'articles' : 'pages'));
    exit;
}

// Load dependencies
$pluginPath = __DIR__;

require_once $pluginPath . '/includes/class-jtb-element.php';
require_once $pluginPath . '/includes/class-jtb-registry.php';
require_once $pluginPath . '/includes/class-jtb-fields.php';
require_once $pluginPath . '/includes/class-jtb-fonts.php';
require_once $pluginPath . '/includes/class-jtb-renderer.php';
require_once $pluginPath . '/includes/class-jtb-css-output.php';
require_once $pluginPath . '/includes/class-jtb-style-system.php';
require_once $pluginPath . '/includes/class-jtb-settings.php';
require_once $pluginPath . '/includes/class-jtb-builder.php';
require_once $pluginPath . '/includes/class-jtb-icons.php';

// Initialize
JTB_Registry::init();
JTB_Fields::init();

// Load modules
$modulesPath = $pluginPath . '/modules';

// All module categories to load
$moduleCategories = ['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth'];

foreach ($moduleCategories as $category) {
    $categoryPath = $modulesPath . '/' . $category;
    if (is_dir($categoryPath)) {
        foreach (glob($categoryPath . '/*.php') as $moduleFile) {
            require_once $moduleFile;
        }
    }
}

// Get post data from correct table (skip for import mode)
$post = null;
if (!$isImportMode) {
    $db = \core\Database::connection();
    $table = ($postType === 'article') ? 'articles' : 'pages';

    $stmt = $db->prepare("SELECT id, title, slug FROM {$table} WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: /admin/' . ($postType === 'article' ? 'articles' : 'pages'));
        exit;
    }
} else {
    // Import mode - use placeholder data (actual title comes from sessionStorage JS)
    $post = [
        'id' => 0,
        'title' => 'AI Import',
        'slug' => ''
    ];
}

// Generate CSRF token
$csrfToken = csrf_token();

// Set variables for view
$postTitle = $post['title'];
$postSlug = $post['slug'] ?? '';

// Render view
require_once $pluginPath . '/views/builder.php';
