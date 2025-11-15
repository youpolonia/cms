<?php
require_once __DIR__ . '/../core/diffengine.php';
require_once __DIR__ . '/../core/VersionManager.php';

header('Content-Type: application/json');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle version listing
if ($requestUri === '/api/analytics/versions' && $requestMethod === 'GET') {
    $versions = VersionManager::getVersions();
    echo json_encode([
        'status' => 'success',
        'data' => array_map(function($version) {
            return [
                'id' => $version['id'],
                'label' => $version['label'],
                'date' => $version['created_at']
            ];
        }, $versions)
    ]);
    exit;
}

// Handle version comparison
if ($requestUri === '/api/analytics/compare' && $requestMethod === 'GET') {
    $oldVersionId = $_GET['old'] ?? null;
    $newVersionId = $_GET['new'] ?? null;
    
    if (!$oldVersionId || !$newVersionId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing version parameters']);
        exit;
    }

    $oldContent = VersionManager::getVersionContent($oldVersionId);
    $newContent = VersionManager::getVersionContent($newVersionId);
    
    $diff = DiffEngine::lineDiff($oldContent, $newContent);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'oldText' => $oldContent,
            'newText' => $newContent,
            'diff' => $diff
        ]
    ]);
    exit;
}

// Handle version restoration
if ($requestUri === '/api/analytics/restore' && $requestMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $versionId = $input['version'] ?? null;
    
    if (!$versionId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing version parameter']);
        exit;
    }

    try {
        VersionManager::restoreVersion($versionId);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(404);
echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
