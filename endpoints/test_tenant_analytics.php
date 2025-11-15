<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../core/database.php';

header('Content-Type: application/json');

try {
    $db = \core\Database::connection();
    
    // Test if tables exist
    $tables = ['tenant_analytics', 'tenant_analytics_details'];
    $results = [];
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        $results[$table] = ($stmt->rowCount() > 0) ? 'exists' : 'missing';
    }
    
    // Test partition procedure with various cases
    $partitionTests = [
        ['name' => 'valid_partition', 'date' => '2025-12-31'],
        ['name' => 'edge_case', 'date' => '2038-01-19'], // Y2038 edge case
        ['name' => 'invalid_date', 'date' => 'invalid-date']
    ];
    
    foreach ($partitionTests as $test) {
        try {
            $db->query("CALL add_tenant_analytics_partition('{$test['name']}', '{$test['date']}')");
            $results["partition_{$test['name']}"] = 'works';
        } catch (PDOException $e) {
            $results["partition_{$test['name']}"] = 'failed: ' . $e->getMessage();
        }
    }
    
    // Tenant isolation test
    try {
        $tenant1 = 'tenant_' . uniqid();
        $tenant2 = 'tenant_' . uniqid();
        
        // Insert test data for both tenants
        $db->query("INSERT INTO tenant_analytics (tenant_id, event_date) VALUES ('$tenant1', NOW())");
        $db->query("INSERT INTO tenant_analytics (tenant_id, event_date) VALUES ('$tenant2', NOW())");
        
        // Verify data isolation
        $stmt = $db->query("SELECT COUNT(*) FROM tenant_analytics WHERE tenant_id = '$tenant1'");
        $tenant1Count = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM tenant_analytics WHERE tenant_id = '$tenant2'");
        $tenant2Count = $stmt->fetchColumn();
        
        $results['tenant_isolation'] = ($tenant1Count == 1 && $tenant2Count == 1) ? 'valid' : 'failed';
        
        // Cleanup
        $db->query("DELETE FROM tenant_analytics WHERE tenant_id IN ('$tenant1', '$tenant2')");
    } catch (PDOException $e) {
        $results['tenant_isolation'] = 'failed: ' . $e->getMessage();
    }
    
    // Data accuracy test
    try {
        $testData = [
            'event_type' => 'test_event',
            'event_value' => 123.45,
            'metadata' => json_encode(['test' => true])
        ];
        
        $db->query("INSERT INTO tenant_analytics_details SET
            tenant_id = 'test_tenant',
            event_date = NOW(),
            event_type = '{$testData['event_type']}',
            event_value = {$testData['event_value']},
            metadata = '{$testData['metadata']}'");
            
        $stmt = $db->query("SELECT * FROM tenant_analytics_details
            WHERE tenant_id = 'test_tenant'
            ORDER BY id DESC LIMIT 1");
        $retrievedData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $results['data_accuracy'] = (
            $retrievedData['event_type'] == $testData['event_type'] &&
            $retrievedData['event_value'] == $testData['event_value'] &&
            $retrievedData['metadata'] == $testData['metadata']
        ) ? 'valid' : 'failed';
        
        // Cleanup
        $db->query("DELETE FROM tenant_analytics_details WHERE tenant_id = 'test_tenant'");
    } catch (PDOException $e) {
        $results['data_accuracy'] = 'failed: ' . $e->getMessage();
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $results,
        'message' => 'Enhanced tenant analytics validation completed'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
