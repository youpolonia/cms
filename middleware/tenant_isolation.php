<?php
/**
 * Tenant Isolation Middleware
 * 
 * Validates and sets tenant context for API requests
 */

function validateTenant($tenantId) {
    // In a real implementation, this would check against a tenant database
    return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $tenantId);
}

function setCurrentTenant($tenantId) {
    // Store tenant in request context
    $_SERVER['CURRENT_TENANT'] = $tenantId;
}

function getCurrentTenant() {
    return $_SERVER['CURRENT_TENANT'] ?? null;
}

// Middleware execution
if (php_sapi_name() !== 'cli') {
    $tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? null;
    
    if (!$tenantId) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'error' => [
                'code' => 'TENANT_REQUIRED',
                'message' => 'X-Tenant-Context header is required',
                'timestamp' => date('c')
            ]
        ]);
        exit;
    }
    
    if (!validateTenant($tenantId)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'error' => [
                'code' => 'INVALID_TENANT',
                'message' => 'Invalid tenant identifier',
                'tenant_id' => $tenantId,
                'timestamp' => date('c')
            ]
        ]);
        exit;
    }
    
    setCurrentTenant($tenantId);
}
