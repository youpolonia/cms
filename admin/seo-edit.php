<?php
/**
 * SEO Metadata Edit Page
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

// Get entity type and ID from query string
$entityType = isset($_GET['type']) ? trim($_GET['type']) : 'page';
$entityId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Validate entity type
if (!in_array($entityType, ['page', 'article', 'category', 'custom'], true)) {
    $entityType = 'page';
}

if ($entityId <= 0) {
    $_SESSION['flash_error'] = 'Invalid entity ID';
    header('Location: seo-dashboard.php');
    exit;
}

$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Check for delete action
    if (isset($_POST['delete']) && $_POST['delete'] === '1') {
        require_once __DIR__ . '/../core/models/seomodel.php';
        $seoModel = new SeoModel($db);
        $metadata = $seoModel->getMetadata($entityType, $entityId);
        if ($metadata) {
            $seoModel->deleteMetadata((int) $metadata['id']);
            $_SESSION['flash_success'] = 'SEO data deleted';
            header('Location: seo-dashboard.php');
            exit;
        }
    }

    // Save SEO data
    $result = $controller->save($entityType, $entityId, $_POST);

    if ($result['success']) {
        $success = $result['message'] ?? 'Settings saved';
    } else {
        $errors = $result['errors'] ?? ['An error occurred'];
    }
}

// Get current data
$data = $controller->edit($entityType, $entityId);
$data['errors'] = $errors;
$data['success'] = $success;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

echo '<main class="container">';
require_once __DIR__ . '/views/seo/edit.php';
echo '</main>';

require_once __DIR__ . '/includes/footer.php';
