<?php
/**
 * Settings Management - Full CRUD Interface
 * Lists all settings with search, filter, and CRUD operations
 */

// Bootstrap
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// RBAC
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

// CSRF
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

// Load controller
require_once __DIR__ . '/../controllers/settingscontroller.php';

// Get controller data
$controller = new SettingsController();
$data = $controller->index();

// Extract variables for view
$settings = $data['settings'];
$groups = $data['groups'];
$currentGroup = $data['currentGroup'];
$search = $data['search'];
$totalCount = $data['totalCount'];

// Header and navigation
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>

<main class="container">
    <?php require_once __DIR__ . '/../views/settings/index.php'; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
