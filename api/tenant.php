<?php
/**
 * Tenant API Endpoint (Phase 3 Implementation)
 *
 * GET /api/tenant/{id}
 * Headers:
 *   X-Tenant-Context: {tenant_hash}
 *   X-API-Version: 1.0
 */

require_once __DIR__.'/../middleware/tenantisolation.php';
require_once __DIR__.'/../middleware/ratelimiter.php';
require_once __DIR__.'/../api-gateway/middlewares/authmiddleware.php';

header('Content-Type: application/json');

try {
    // Initialize middleware
    $rateLimiter = new RateLimiter('tenant_api', 60, 60); // 60 requests per minute
    $authMiddleware = new AuthMiddleware(['admin', 'tenant_manager']);
    
    // Get tenant ID from path
    $pathParts = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    $tenantId = $pathParts[3] ?? '';
    $tenantHash = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? '';
    
    // Apply middleware
    $authMiddleware->authenticate();
    $rateLimiter->check();
    
    if (empty($tenantId) || empty($tenantHash)) {
        throw new Exception('Missing tenant parameters', 400);
    }

    // Validate tenant
    $isValid = TenantIsolation::validate($tenantId, $tenantHash);
    
    if (!$isValid) {
        throw new Exception('Invalid tenant context', 403);
    }

    // Standardized response format
    $response = [
        'data' => [
            'tenant_id' => $tenantId,
            'status' => 'valid',
            'timestamp' => date('c'),
            'rate_limit' => $rateLimiter->getRemaining()
        ],
        'meta' => [
            'api_version' => $_SERVER['HTTP_X_API_VERSION'] ?? '1.0'
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => [
            'code' => 'TENANT_API_ERROR',
            'message' => $e->getMessage(),
            'details' => [
                'tenant_id' => $tenantId ?? '',
                'timestamp' => date('c'),
                'documentation' => '/api-docs/tenant'
            ]
        ]
    ], JSON_PRETTY_PRINT);
}
