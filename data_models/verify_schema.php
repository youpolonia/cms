<?php
// Use same connection as run_migrations.php
require_once __DIR__ . '/../core/database.php';

try {
    $pdo = getDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get list of all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Check for tenant_id columns
    $results = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'tenant_id') {
                $results[$table] = $col;
                break;
            }
        }
    }

    echo "Schema Verification Results:\n";
    echo "Tables with tenant_id: " . count($results) . "\n";
    echo "Existing tables: " . implode(', ', $tables) . "\n";
    
} catch (PDOException $e) {
    echo "Verification failed: " . $e->getMessage();
    exit(1);
}
