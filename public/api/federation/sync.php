<?php
require_once __DIR__ . '/../../../core/contentfederator.php';
require_once __DIR__ . '/../../../core/tenantmanager.php';
require_once __DIR__ . '/../../../core/responsehandler.php';
require_once __DIR__ . '/../../../includes/database/connection.php';

header('Content-Type: application/json');

try {
    // Validate tenant context
    $tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? null;
    if (!$tenantId || !TenantManager::validateTenant($pdo, $tenantId)) {
        ResponseHandler::error('Invalid tenant context', 403, [
            'required_scope' => 'federation:sync',
            'tenant_id' => $tenantId
        ]);
        exit;
    }

    // Set current tenant
    TenantManager::setCurrentTenant($tenantId);

    // Validate input
    $contentId = $_GET['content_id'] ?? null;
    $targetTenants = isset($_GET['tenants']) ? explode(',', $_GET['tenants']) : [];
    
    if (!$contentId || empty($targetTenants)) {
        ResponseHandler::error('Missing required parameters', 400, [
            'required_params' => ['content_id', 'tenants']
        ]);
        exit;
    }

    // Execute sync
    $results = ContentFederator::syncVersions($pdo, $contentId, $targetTenants);

    // Return response
    ResponseHandler::success([
        'content_id' => $contentId,
        'source_tenant' => $tenantId,
        'results' => $results,
        'timestamp' => date('c')
    ]);

} catch (Exception $e) {
    ResponseHandler::error(
        $e->getMessage(), 
        500, 
        ['trace_id' => uniqid('sync_')]
    );
}
