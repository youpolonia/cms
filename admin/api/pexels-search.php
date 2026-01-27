<?php
/**
 * Pexels API Proxy
 * Bypasses CORS by making server-side requests to Pexels API
 */

declare(strict_types=1);

define('CMS_ROOT', realpath(__DIR__ . '/../..'));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/admin/models/settingsmodel.php';

// Start admin session for authentication
cms_session_start('admin');

header('Content-Type: application/json');

// Check admin authentication (supports both MVC and legacy session vars)
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - please log in']);
    exit;
}

$query = trim($_GET['query'] ?? '');
if (empty($query)) {
    echo json_encode(['error' => 'Query required']);
    exit;
}

$settingsModel = new SettingsModel();
$apiKey = $settingsModel->getValue('pexels_api_key', '');

if (empty($apiKey)) {
    echo json_encode(['error' => 'Pexels API key not configured']);
    exit;
}

$url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=20';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $apiKey]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['error' => 'API request failed: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['error' => 'API returned status ' . $httpCode]);
    exit;
}

echo $response;
