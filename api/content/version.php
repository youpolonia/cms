<?php
require_once __DIR__ . '/../../services/contentlifecyclemanager.php';
require_once __DIR__ . '/../../api-gateway/middlewares/authmiddleware.php';

header('Content-Type: application/json');

// Initialize auth middleware
$auth = new AuthMiddleware(['content_editor', 'admin']);

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Route requests
    if (strpos($path, '/api/content/compare-versions') !== false && $method === 'GET') {
        handleCompareVersions();
    } elseif (strpos($path, '/api/content/restore-version') !== false && $method === 'POST') {
        handleRestoreVersion();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleCompareVersions() {
    $auth = $GLOBALS['auth'];
    $auth->authenticate();
    
    if (!isset($_GET['version1_id']) || !isset($_GET['version2_id'])) {
        throw new InvalidArgumentException('Both version IDs are required');
    }

    $version1Id = $_GET['version1_id'];
    $version2Id = $_GET['version2_id'];
    $contentId = $_GET['content_id'] ?? null;

    // In a real implementation, you would fetch the versions and generate a diff
    // This is a simplified example
    $diff = generateDiff($version1Id, $version2Id);
    
    echo json_encode([
        'success' => true,
        'version1' => $version1Id,
        'version2' => $version2Id,
        'diff' => $diff
    ]);
}

function handleRestoreVersion() {
    $auth = $GLOBALS['auth'];
    $auth->authenticate();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['content_id']) || !isset($data['version_id'])) {
        throw new InvalidArgumentException('Content ID and Version ID are required');
    }

    $contentId = $data['content_id'];
    $versionId = $data['version_id'];
    $tenantId = $auth->getTenantId();

    $manager = new ContentLifecycleManager($contentId, $tenantId);
    $success = $manager->restoreVersion($versionId);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Version restored successfully' : 'Restore failed'
    ]);
}

function generateDiff($version1Id, $version2Id) {
    // Simplified diff generation - would use a proper diff library in production
    return "--- Version $version1Id\n+++ Version $version2Id\n";
}
