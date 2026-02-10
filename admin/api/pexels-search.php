<?php
/**
 * Pexels Stock Photo Search Proxy
 * 
 * Used by JTB Media Gallery (media-gallery.js) for stock photo tab.
 * Proxies requests to Pexels API via JTB_AI_Pexels class.
 *
 * GET /admin/api/pexels-search.php?query=nature&per_page=15&page=1
 * Returns: { photos: [...], total_results: N }
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';

// Start session & verify admin
cms_session_start('admin');
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$query = trim($_GET['query'] ?? '');
if (empty($query)) {
    echo json_encode(['error' => 'Missing query parameter']);
    exit;
}

$perPage = min(30, max(1, (int)($_GET['per_page'] ?? 15)));
$page = max(1, (int)($_GET['page'] ?? 0));

// Load database + JTB Pexels class
$pexelsClass = CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-pexels.php';
if (!file_exists($pexelsClass)) {
    echo json_encode(['error' => 'Pexels integration not available']);
    exit;
}

require_once CMS_ROOT . '/core/database.php';
require_once $pexelsClass;

$options = ['per_page' => $perPage];
if ($page > 0) {
    $options['page'] = $page;
}
if (!empty($_GET['orientation'])) {
    $options['orientation'] = $_GET['orientation'];
}

$result = \JessieThemeBuilder\JTB_AI_Pexels::searchPhotos($query, $options);

if (!$result['ok']) {
    echo json_encode(['error' => $result['error'] ?? 'Search failed']);
    exit;
}

// Return in format expected by media-gallery.js
echo json_encode([
    'photos' => $result['photos'] ?? [],
    'total_results' => $result['total_results'] ?? 0,
    'page' => $result['page'] ?? 1
]);
