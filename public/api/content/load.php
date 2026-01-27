<?php
require_once __DIR__ . '/../../../handlers/contentfilehandler.php';

header('Content-Type: application/json');

try {
    $path = $_GET['path'] ?? null;
    $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? null;
    
    if (empty($path)) {
        throw new InvalidArgumentException('Content path is required');
    }
    if (empty($tenantId)) {
        throw new InvalidArgumentException('X-Tenant-ID header is required');
    }

    // Check for Lock header (shared or exclusive)
    $lock = false;
    if (isset($_SERVER['HTTP_LOCK'])) {
        $lockType = strtolower($_SERVER['HTTP_LOCK']);
        $lock = in_array($lockType, ['shared', 'exclusive']);
    }

    $content = ContentFileHandler::loadContent($path, $lock, $tenantId);
    $version = ContentFileHandler::getVersion($path, $tenantId);
    
    if ($content === null) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Content not found',
            'code' => 'not_found',
            'success' => false
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'content' => $content,
        'version' => $version,
        'path' => $path
    ]);
} catch (ContentFileException $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => 'file_error', 
        'success' => false
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => 'server_error',
        'success' => false
    ]);
}
