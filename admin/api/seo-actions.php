<?php
/**
 * SEO API Actions
 * Handles AJAX requests for SEO operations
 */
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/../includes/permissions.php';

// Return JSON
header('Content-Type: application/json; charset=utf-8');

// Check if admin is logged in
if (!cms_is_admin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'errors' => ['Unauthorized']]);
    exit;
}

require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../controllers/seocontroller.php';

$db = \core\Database::connection();
$controller = new SeoController($db);

$action = $_GET['action'] ?? '';

// For POST requests, validate CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token from header or post
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if (!csrf_validate($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
        exit;
    }
}

switch ($action) {
    case 'regenerate_sitemap':
        $result = $controller->regenerateSitemap();
        echo json_encode($result);
        break;

    case 'analyze':
        $type = $_GET['type'] ?? 'page';
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'errors' => ['Invalid ID']]);
            break;
        }
        $result = $controller->analyze($type, $id);
        echo json_encode($result);
        break;

    case 'get_redirect':
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid ID']);
            break;
        }
        $redirect = $controller->getRedirect($id);
        echo json_encode($redirect ?: ['error' => 'Not found']);
        break;

    case 'dashboard_stats':
        $result = $controller->dashboard();
        echo json_encode($result);
        break;

    case 'crawl_stats':
        $result = $controller->getCrawlStats();
        echo json_encode(['success' => true, 'data' => $result]);
        break;

    case 'clean_crawl_logs':
        $result = $controller->cleanCrawlLogs();
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Unknown action']]);
}
