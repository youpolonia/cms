<?php
/**
 * SEO Redirects Management Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/controllers/seocontroller.php';

$db = \core\Database::connection();
$controller = new SeoController($db);

$success = '';
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Delete redirect
    if (isset($_POST['delete_id'])) {
        $result = $controller->deleteRedirect((int) $_POST['delete_id']);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $errors = $result['errors'];
        }
    }
    // Save redirect
    elseif (isset($_POST['source_url'])) {
        $result = $controller->saveRedirect($_POST);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $errors = $result['errors'];
        }
    }
}

// Get redirect list
$data = $controller->listRedirects();
$data['success'] = $success;
$data['errors'] = $errors;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

echo '<main class="container">';
require_once __DIR__ . '/views/seo/redirects.php';
echo '</main>';

require_once __DIR__ . '/includes/footer.php';
