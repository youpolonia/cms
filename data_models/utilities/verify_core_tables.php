<?php
/**
 * Core Tables Verification Script
 * Checks existence, structure and test data for users, sessions, password_resets tables
 */

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/database.php';

try {
    $pdo = \core\Database::connection();

    $tablesToCheck = ['users', 'sessions', 'password_resets'];
    $results = [];

    foreach ($tablesToCheck as $table) {
        // Check table existence
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        
        if (!$exists) {
            $results[$table] = ['exists' => false];
            continue;
        }

        // Get column structure
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        
        // Count rows
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();

        $results[$table] = [
            'exists' => true,
            'columns' => array_column($columns, 'Field'),
            'row_count' => (int)$count
        ];
    }

    // Output results
    echo "Core Tables Verification Results:\n";
    echo str_repeat('-', 40) . "\n";
    
    foreach ($results as $table => $data) {
        echo "Table: $table\n";
        echo "Exists: " . ($data['exists'] ? 'Yes' : 'No') . "\n";
        
        if ($data['exists']) {
            echo "Columns: " . implode(', ', $data['columns']) . "\n";
            echo "Row Count: " . $data['row_count'] . "\n";
        }
        
        echo str_repeat('-', 40) . "\n";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
