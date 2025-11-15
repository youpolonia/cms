<?php
require_once __DIR__.'/../../includes/middleware/apiauthmiddleware.php';
require_once __DIR__.'/../../includes/middleware/tenantvalidator.php';
require_once __DIR__.'/../../core/contentpublisher.php';
use Includes\Middleware\TenantValidator;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    return;
}

try {
    $locked = false;
    // Validate authentication
    $authMiddleware = new ApiAuthMiddleware();
    if ($authMiddleware->handle() !== true) {
        return;
    }

    // Get tenant ID from headers
    $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
    if (empty($tenantId) || !TenantValidator::validateTenantHeader($tenantId)) {
        throw new Exception('Invalid or missing X-Tenant-ID header');
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (!isset($input['content_id'])) {
        throw new Exception('Missing content_id');
    }

    $contentId = Sanitization::filterInt($input['content_id']);
    
    // Validate content ownership
    if (!TenantValidator::validateContentOwnership($contentId, $tenantId)) {
        throw new Exception('Content does not belong to tenant');
    }

    // Lock content during unpublish
    $locked = TenantValidator::lockContent($contentId, $tenantId);
    if (!$locked) {
        throw new Exception('Failed to lock content for unpublishing');
    }

    ContentPublisher::init();
    $result = ContentPublisher::unpublish((int)$contentId);

    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);
} catch (Exception $e) {
    http_response_code(400);
    error_log('unpublish route error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Request failed'
    ]);
} finally {
    if (!empty($locked) && !empty($contentId)) {
        TenantValidator::unlockContent($contentId);
    }
}
