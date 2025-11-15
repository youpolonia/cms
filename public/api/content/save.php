<?php
require_once __DIR__ . '/../../../config.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/../../../core/csrf.php';
    csrf_validate_or_403();
}

require_once __DIR__ . '/../../../models/contentmodel.php';
require_once __DIR__ . '/../../../models/versionmodel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

$contentId = $_POST['content_id'] ?? null;
$content = $_POST['content'] ?? '';
$status = $_POST['status'] ?? 'draft'; // draft, pending_review, published
$tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? null;

if (empty($content)) {
    http_response_code(400);
    die(json_encode(['error' => 'Content cannot be empty']));
}
if (empty($tenantId)) {
    http_response_code(400);
    die(json_encode(['error' => 'X-Tenant-ID header is required']));
}

try {
    $contentModel = new ContentModel($tenantId);
    $versionModel = new VersionModel($tenantId);
    
    // Save or update content
    if ($contentId) {
        // Get current content for versioning
        $currentContent = $contentModel->getById($contentId);
        
        // Create version before updating
        $versionModel->create([
            'content_id' => $contentId,
            'content' => $currentContent['body'],
            'version_number' => $versionModel->getNextVersionNumber($contentId),
            'status' => $currentContent['status']
        ]);
        
        // Update content
        $contentModel->update($contentId, [
            'body' => $content,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        $contentId = $contentModel->create([
            'body' => $content,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'content_id' => $contentId,
        'status' => $status
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save content']);
}
