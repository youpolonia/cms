<?php
require_once __DIR__ . '/../../../models/versionmodel.php';
require_once __DIR__ . '/../../../models/contentmodel.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method Not Allowed']));
}

require_once __DIR__ . '/../../../core/csrf.php';
csrf_validate_or_403();


$data = json_decode(file_get_contents('php://input'), true);
$contentId = $data['content_id'] ?? null;
$versionId = $data['version_id'] ?? null;
$tenantId = $data['tenant_id'] ?? null;

if (!$contentId || !$versionId || !$tenantId) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing content_id or version_id']));
}

try {
    $versionModel = new VersionModel($tenantId);
    $contentModel = new ContentModel($tenantId);
    
    // Get version content
    $version = $versionModel->getById($versionId, $tenantId);
    if (!$version || $version['content_id'] != $contentId) {
        http_response_code(404);
        die(json_encode(['error' => 'Version not found']));
    }
    
    // Create new version before restoring
    $currentContent = $contentModel->getById($contentId, $tenantId);
    $versionModel->create([
        'content_id' => $contentId,
        'content' => $currentContent['body'],
        'version_number' => $versionModel->getNextVersionNumber($contentId),
        'status' => $currentContent['status']
    ]);
    
    // Restore version content
    $contentModel->update($contentId, $tenantId, [
        'body' => $version['content'],
        'status' => $version['status'],
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to restore version']);
}
