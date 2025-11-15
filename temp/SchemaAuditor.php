<?php
/**
 * Schema Auditor - Pure PDO Implementation
 * Temporary script for tenant isolation verification
 */

// 0. Load database configuration
require_once __DIR__.'/../config.php';

// 1. Establish connection
require_once __DIR__ . '/../core/database.php';
try {
    $pdo = \core\Database::connection();

    // 2. Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // 3. Identify tenant-aware tables (should contain tenant_id)
    $tenantTables = [];
    $issues = [];
    
    foreach ($tables as $table) {
        // Skip tenants table itself
        if ($table === 'tenants') continue;
        
        // Check for tenant_id column
        $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll();
        $hasTenantId = false;
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'tenant_id') {
                $hasTenantId = true;
                
                // Verify column type
                if (strpos($col['Type'], 'int(11)') === false || $col['Null'] === 'YES') {
                    $issues[$table][] = "Invalid tenant_id type: {$col['Type']}";
                }
                break;
            }
        }
        
        if ($hasTenantId) {
            $tenantTables[] = $table;
            
            // Verify foreign key
            $fk = $pdo->query("
                SELECT UPDATE_RULE, DELETE_RULE 
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
                WHERE TABLE_NAME = '$table' 
                AND COLUMN_NAME = 'tenant_id'
            ")->fetch();
            
            if (!$fk) {
                $issues[$table][] = "Missing foreign key to tenants(id)";
            } else {
                if ($fk['UPDATE_RULE'] !== 'CASCADE') {
                    $issues[$table][] = "Invalid UPDATE_RULE: {$fk['UPDATE_RULE']} (should be CASCADE)";
                }
                if ($fk['DELETE_RULE'] !== 'RESTRICT') {
                    $issues[$table][] = "Invalid DELETE_RULE: {$fk['DELETE_RULE']} (should be RESTRICT)";
                }
            }
            
            // Verify index
            $index = $pdo->query("SHOW INDEX FROM `$table` WHERE Column_name = 'tenant_id'")->fetch();
            if (!$index) {
                $issues[$table][] = "Missing index on tenant_id";
            }
        }
    }

    // 4. Output results
    echo "## Tenant Isolation Audit Results\n\n";
    echo "### Tenant-Aware Tables (" . count($tenantTables) . " found)\n";
    echo "```php\n";
    echo "\$tenantTables = [\n";
    foreach ($tenantTables as $table) {
        echo "    '$table',\n";
    }
    echo "];\n";
    echo "```\n\n";
    
    if (!empty($issues)) {
        echo "### Schema Issues\n";
        foreach ($issues as $table => $tableIssues) {
            echo "- **$table**:\n";
            foreach ($tableIssues as $issue) {
                echo "  - $issue\n";
            }
        }
    } else {
        echo "No schema issues detected.\n";
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
