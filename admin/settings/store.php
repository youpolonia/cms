<?php
/**
 * Store New Setting Handler (POST)
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

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

// Load controller
require_once __DIR__ . '/../controllers/settingscontroller.php';

$controller = new SettingsController();
$result = $controller->create();

if ($result['success']) {
    // Redirect to manage page with success message
    header('Location: manage.php?success=1&message=' . urlencode($result['message']));
    exit;
} else {
    // Store errors in session and redirect back to form
    $_SESSION['settings_errors'] = $result['errors'];
    $_SESSION['settings_data'] = $result['data'] ?? null;
    header('Location: create.php');
    exit;
}
