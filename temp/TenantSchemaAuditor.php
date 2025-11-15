<?php
/**
 * Tenant Schema Auditor
 * Performs read-only audit of database tables for tenant isolation compliance
 */

// Load database credentials
require_once __DIR__ . '/../config.php';
if (!defined('DB_PORT')) {
    $envPort = getenv('DB_PORT');
    define('DB_PORT', ($envPort !== false && $envPort !== '') ? (int)$envPort : 3306);
}
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_HOSTNAME')) {
    define('DB_HOSTNAME', DB_HOST);
}

// Establish connection
require_once __DIR__ . '/../core/database.php';
try {
    $pdo = \core\Database::connection();
    
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Tables that should have tenant_id
    $tenantTables = ['users', 'content', 'media', 'settings', 'workflows'];
    $auditResults = [];
    
    foreach ($tables as $table) {
        // Skip tenants table itself
        if ($table === 'tenants') continue;
        
        // Check if table should have tenant_id
        $shouldHaveTenantId = in_array($table, $tenantTables);
        
        // Get table structure
        $columns = $pdo->query("DESCRIBE `$table`")->fetchAll();
        $hasTenantId = false;
        $tenantIdType = null;
        $hasForeignKey = false;
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'tenant_id') {
                $hasTenantId = true;
                $tenantIdType = $col['Type'];
                break;
            }
        }
        
        // Check foreign keys if tenant_id exists
        if ($hasTenantId) {
            $foreignKeys = $pdo->query("
                SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = '".DB_NAME."' 
                AND TABLE_NAME = '$table' 
                AND COLUMN_NAME = 'tenant_id'
                AND REFERENCED_TABLE_NAME = 'tenants'
            ")->fetchAll();
            
            $hasForeignKey = !empty($foreignKeys);
        }
        
        $auditResults[$table] = [
            'should_have_tenant_id' => $shouldHaveTenantId,
            'has_tenant_id' => $hasTenantId,
            'tenant_id_type' => $tenantIdType,
            'has_foreign_key' => $hasForeignKey,
            'status' => $shouldHaveTenantId 
                ? ($hasTenantId ? ($hasForeignKey ? 'valid' : 'missing_fk') : 'missing_column')
                : ($hasTenantId ? 'unexpected_column' : 'n/a')
        ];
    }
    
    // Output results
    echo "Tenant Isolation Audit Results:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($auditResults as $table => $result) {
        echo "Table: $table\n";
        echo "Should have tenant_id: " . ($result['should_have_tenant_id'] ? 'YES' : 'NO') . "\n";
        if ($result['has_tenant_id']) {
            echo "tenant_id type: " . $result['tenant_id_type'] . "\n";
            echo "Has foreign key: " . ($result['has_foreign_key'] ? 'YES' : 'NO') . "\n";
        }
        echo "Status: " . strtoupper($result['status']) . "\n";
        echo str_repeat("-", 50) . "\n";
    }
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Returns list of table names that should have tenant_id column
 * Read-only function for use by migration scripts
 */
function tenant_schema_audit_list(PDO $pdo): array {
    return ['users', 'content', 'media', 'settings', 'workflows'];
}
