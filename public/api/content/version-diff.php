<?php
require_once __DIR__ . '/../../../includes/versioncomparator.php';
require_once __DIR__ . '/../../../models/versionmodel.php';
require_once __DIR__ . '/../../../models/contentmodel.php';

header('Content-Type: text/plain');

$contentId = $_GET['content_id'] ?? null;
$versionId = $_GET['version_id'] ?? null;
$tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? null;

if (!$contentId || !$versionId) {
    http_response_code(400);
    die('Missing content_id or version_id');
}
if (empty($tenantId)) {
    http_response_code(400);
    die('X-Tenant-ID header is required');
}

try {
    $versionModel = new VersionModel($tenantId);
    $contentModel = new ContentModel($tenantId);
    
    // Get current content
    $currentContent = $contentModel->getById($contentId);
    if (!$currentContent) {
        http_response_code(404);
        die('Content not found');
    }
    
    // Get version content
    $version = $versionModel->getById($versionId);
    if (!$version || $version['content_id'] != $contentId) {
        http_response_code(404);
        die('Version not found');
    }
    
    // Compare versions
    $comparator = new VersionComparator();
    $diff = $comparator->compare($version['content'], $currentContent['body']);
    
    echo $diff;
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error generating diff';
}
