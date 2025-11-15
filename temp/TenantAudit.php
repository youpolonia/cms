<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DBSchemaAuditor.php';

/**
 * Tenant Table Audit Script
 * Temporary utility for tenant isolation verification
 */

try {
    // Verify tenants table exists
    $tables = DBSchemaAuditor::getTables();
    if (!in_array('tenants', $tables)) {
        throw new Exception("Tenants table not found in database");
    }

    // Get table structure
    $structure = DBSchemaAuditor::getTableStructure('tenants');
    
    // Output markdown report
    echo "# Tenant Table Audit Report\n\n";
    echo "## Table Structure\n\n";
    
    // Columns
    echo "### Columns\n";
    echo "| Name | Type | Null | Key | Default | Extra |\n";
    echo "|------|------|------|-----|---------|-------|\n";
    foreach ($structure['columns'] as $col) {
        printf("| %s | %s | %s | %s | %s | %s |\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'] ?? 'NULL',
            $col['Extra']
        );
    }
    echo "\n";

    // Indexes
    if (!empty($structure['indexes'])) {
        echo "### Indexes\n";
        $indexes = [];
        foreach ($structure['indexes'] as $idx) {
            $indexes[$idx['Key_name']][] = $idx['Column_name'];
        }
        
        foreach ($indexes as $name => $columns) {
            echo "- **$name**: " . implode(', ', $columns) . "\n";
        }
        echo "\n";
    }

    // Foreign Keys
    if (!empty($structure['foreign_keys'])) {
        echo "### Foreign Keys\n";
        foreach ($structure['foreign_keys'] as $fk) {
            echo "- **{$fk['COLUMN_NAME']}** references ";
            echo "{$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']}) ";
            echo "[ON UPDATE {$fk['UPDATE_RULE']}, ON DELETE {$fk['DELETE_RULE']}]\n";
        }
    }

} catch (Exception $e) {
    echo "## Audit Failed\n";
    echo "Error: " . $e->getMessage();
} finally {
    DBSchemaAuditor::closeConnection();
}
