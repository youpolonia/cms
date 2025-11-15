<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/contentversioning.php';
require_once __DIR__ . '/../includes/security/authservicewrapper.php';
require_once __DIR__ . '/../includes/content_api.php';
require_once __DIR__ . '/../core/csrf.php';

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Invalid CSRF token');
}

// Check permissions
if (!Auth::hasPermission('edit_content')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Insufficient permissions');
}

$contentId = (int)($_POST['content_id'] ?? 0);
$versionId = $_POST['version_id'] ?? '';

if ($contentId <= 0 || empty($versionId)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid parameters');
}

try {
    $version = ContentVersioning::getVersion($contentId, $versionId);
    $result = ContentAPI::updateContent($contentId, $version['content']);
    
    if ($result) {
        // Secure redirect with sanitized parameters
        $redirectUrl = sprintf(
            '/admin/edit_content.php?content_id=%d&restored=1',
            $contentId
        );
        header('Location: ' . htmlspecialchars($redirectUrl, ENT_QUOTES));
        exit;
    }
    throw new RuntimeException('Failed to restore content');
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Restore failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
}
