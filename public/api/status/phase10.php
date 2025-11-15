<?php
/**
 * Phase 10 Status Verification Endpoint
 * GET /api/status/phase10
 */

require_once __DIR__.'/../../../middleware/tenantisolation.php';

header('Content-Type: application/json');

try {
    // Validate tenant context
    $tenantHash = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? '';
    if (empty($tenantHash)) {
        throw new Exception('Missing tenant context header', 400);
    }

    $tenantId = TenantIsolation::getCurrent();
    if (!$tenantId || !TenantIsolation::validate($tenantId, $tenantHash)) {
        throw new Exception('Invalid tenant context', 403);
    }

    require_once __DIR__ . '/../../../core/database.php';
    $pdo = \core\Database::connection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check deployment status
    $stmt = $pdo->prepare("SELECT status FROM deployment_status WHERE tenant_id = ? AND phase = 10");
    $stmt->execute([$tenantId]);
    $status = $stmt->fetchColumn();

    if ($status !== 'completed') {
        throw new Exception('Phase 10 deployment not completed', 400);
    }

    // Verify schema changes
    $requiredColumns = ['phase10_flag', 'phase10_timestamp'];
    $stmt = $pdo->query("DESCRIBE content");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missingColumns = array_diff($requiredColumns, $columns);
    if (!empty($missingColumns)) {
        throw new Exception('Missing required columns: ' . implode(', ', $missingColumns), 500);
    }

    // Verify phase10_metadata table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'phase10_metadata'")->rowCount() > 0;
    if (!$tableExists) {
        throw new Exception('phase10_metadata table missing', 500);
    }

    echo json_encode([
        'status' => 'operational',
        'phase' => 10,
        'timestamp' => date('c'),
        'checks' => [
            'deployment_status' => true,
            'schema_changes' => true,
            'metadata_table' => true
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => [
            'code' => strtoupper(str_replace(' ', '_', $e->getMessage())),
            'message' => $e->getMessage(),
            'tenant_id' => $tenantId ?? '',
            'timestamp' => date('c')
        ]
    ]);
}
