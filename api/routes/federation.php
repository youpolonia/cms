<?php
require_once __DIR__.'/../../includes/database/middleware/tenantisolation.php';
require_once __DIR__.'/../../utilities/tenantvalidator.php';
require_once __DIR__.'/../../services/contentsharer.php';
require_once __DIR__.'/../../services/versionsynchronizer.php';

function handleFederationRequest($request) {
    TenantIsolation::handle($request);
    
    $action = $request['action'] ?? null;
    $tenantId = getCurrentTenant();
    $content = $request['content'] ?? null;

    switch ($action) {
        case 'share':
            if (!TenantValidator::canShare($tenantId, $content)) {
                http_response_code(403);
                exit;
            }
            $sharedId = ContentSharer::share($tenantId, $content);
            return ['shared_id' => $sharedId];

        case 'sync':
            $version = $request['version'] ?? null;
            return VersionSynchronizer::getUpdates($tenantId, $version);

        default:
            http_response_code(400);
            exit;
    }
}
// API routes for content federation
require_once __DIR__.'/../../includes/database/middleware/tenantisolation.php';

// Tenant identification
route('GET', '/api/tenant/{tenant_id}', function($tenantId) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'tenant' => $tenantId,
        'valid' => TenantValidator::validate($tenantId)
    ]);
});

// Content sharing endpoint
route('POST', '/api/federation/share', function() {
    $tenant = getCurrentTenant();
    $content = json_decode(file_get_contents('php://input'), true);
    
    if (!ContentValidator::canShare($tenant, $content)) {
        return errorResponse('Sharing not allowed', 403);
    }

    $sharedId = ContentSharer::share($tenant, $content);
    return jsonResponse(['shared_id' => $sharedId]);
});

// Version sync endpoint
require_once __DIR__.'/../../includes/user/authentication.php';
require_once __DIR__.'/../../includes/services/tenantmanager.php';

route('GET', '/api/federation/sync', function() {
    try {
        // Validate tenant context
        $tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? null;
        if (!$tenantId || !validateTenant($tenantId)) {
            throw new Exception('Invalid tenant context', 403);
        }
        
        // Check permissions
        $auth = new Authentication();
        $auth->requirePermission('federation:sync');
        
        // Process request
        $params = $_GET;
        if (empty($params['version'])) {
            throw new Exception('Version parameter required', 400);
        }
        
        $versions = VersionSynchronizer::getUpdates(
            $tenantId,
            $params['version']
        );
        
        return jsonResponse([
            'success' => true,
            'data' => $versions
        ]);
    } catch (Exception $e) {
        return jsonResponse([
            'success' => false,
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]
        ], $e->getCode() ?: 500);
    }
});

// Conflict resolution
route('POST', '/api/federation/resolve', function() {
    $conflict = json_decode(file_get_contents('php://input'), true);
    $resolution = ConflictResolver::resolve(
        getCurrentTenant(),
        $conflict
    );
    return jsonResponse($resolution);
});

// Content retrieval endpoint
route('GET', '/api/federation/content', function() {
    header('Content-Type: application/json');
    try {
        $tenant = getCurrentTenant();
        if (!$tenant) {
            throw new Exception('Invalid tenant context', 403);
        }
        
        $content = FederationService::getContentForTenant($tenant);
        
        echo json_encode([
            'data' => $content,
            'meta' => [
                'timestamp' => date('c'),
                'version' => '1.0'
            ],
            'errors' => []
        ]);
    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            'error' => [
                'code' => 'FEDERATION_ERROR',
                'message' => $e->getMessage(),
                'tenant_id' => getCurrentTenant(),
                'timestamp' => date('c'),
                'details' => []
            ]
        ]);
    }
});

// Test endpoint
route('GET', '/api/federation/test', function() {
    if ($_GET['test'] === 'tenant-isolation') {
        return jsonResponse([
            'current_tenant' => getCurrentTenant(),
            'is_valid' => TenantValidator::validate(getCurrentTenant())
        ]);
    }
    return errorResponse('Invalid test', 400);
});

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($message, $code) {
    return jsonResponse([
        'error' => [
            'code' => $code,
            'message' => $message,
            'tenant_id' => getCurrentTenant(),
            'timestamp' => gmdate('c')
        ]
    ], $code);
}
