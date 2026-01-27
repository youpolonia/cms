<?php
/**
 * Create New Setting Entry Point
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

$controller = new SettingsController();
$model = $controller->getModel();

// Get groups for form
$groups = $model->getGroups();

// Check for form repopulation data from session
$errors = $_SESSION['settings_errors'] ?? [];
$data = $_SESSION['settings_data'] ?? null;
unset($_SESSION['settings_errors'], $_SESSION['settings_data']);

// Header and navigation
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>

<main class="container">
    <?php require_once __DIR__ . '/../views/settings/create.php'; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
