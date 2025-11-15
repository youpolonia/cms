<?php
/**
 * Debug Error Logs Table
 *
 * This script checks the error_logs table structure and provides diagnostic information.
 */

if (!defined('DEV_MODE')) {
    http_response_code(500);
    echo 'Configuration error';
    return;
}
if (!DEV_MODE) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden in production";
    return;
}

// Bootstrap the application
require_once __DIR__ . '/includes/bootstrap.php';

// Get database connection
$db = \core\Database::connection();

// Output header
header('Content-Type: text/plain');

echo "=== ERROR LOGS TABLE DIAGNOSTIC ===\n\n";

// Check if error_logs table exists
try {
    $stmt = $db->prepare("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'error_logs'
        ) AS table_exists
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['table_exists']) {
        echo "✅ error_logs table exists\n\n";
        
        // Get table structure
        $stmt = $db->prepare("
            SELECT column_name, data_type, character_maximum_length, is_nullable
            FROM information_schema.columns
            WHERE table_schema = 'public' 
            AND table_name = 'error_logs'
            ORDER BY ordinal_position
        ");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Table structure:\n";
        echo str_repeat('-', 80) . "\n";
        echo sprintf("%-20s %-15s %-10s %-10s\n", 'Column', 'Type', 'Length', 'Nullable');
        echo str_repeat('-', 80) . "\n";
        
        foreach ($columns as $column) {
            echo sprintf(
                "%-20s %-15s %-10s %-10s\n",
                $column['column_name'],
                $column['data_type'],
                $column['character_maximum_length'] ?: 'N/A',
                $column['is_nullable']
            );
        }
        
        // Count records
        $stmt = $db->prepare("SELECT COUNT(*) AS count FROM error_logs");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nTotal records: " . $count['count'] . "\n";
        
        // Sample records if any exist
        if ($count['count'] > 0) {
            $stmt = $db->prepare("SELECT * FROM error_logs ORDER BY created_at DESC LIMIT 3");
            $stmt->execute();
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nSample records:\n";
            echo str_repeat('-', 80) . "\n";
            
            foreach ($samples as $sample) {
                echo "ID: " . $sample['id'] . "\n";
                echo "Worker ID: " . $sample['worker_id'] . "\n";
                echo "Severity: " . $sample['severity'] . "\n";
                echo "Message: " . $sample['message'] . "\n";
                echo "Component: " . $sample['component'] . "\n";
                echo "Created: " . $sample['created_at'] . "\n";
                echo str_repeat('-', 40) . "\n";
            }
        }
    } else {
        echo "❌ error_logs table does not exist\n\n";
        
        echo "Suggested SQL to create the table:\n\n";
        echo "CREATE TABLE error_logs (\n";
        echo "    id SERIAL PRIMARY KEY,\n";
        echo "    worker_id INTEGER NOT NULL,\n";
        echo "    severity VARCHAR(20) NOT NULL,\n";
        echo "    message TEXT NOT NULL,\n";
        echo "    component VARCHAR(100) NOT NULL,\n";
        echo "    details TEXT,\n";
        echo "    created_at TIMESTAMP NOT NULL DEFAULT NOW()\n";
        echo ");\n\n";
        
        echo "CREATE INDEX idx_error_logs_worker_id ON error_logs(worker_id);\n";
        echo "CREATE INDEX idx_error_logs_severity ON error_logs(severity);\n";
        echo "CREATE INDEX idx_error_logs_component ON error_logs(component);\n";
        echo "CREATE INDEX idx_error_logs_created_at ON error_logs(created_at);\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "\n";
    
    // Check if database connection is working
    echo "\nTesting database connection:\n";
    try {
        $stmt = $db->prepare("SELECT 1");
        $stmt->execute();
        echo "✅ Database connection is working\n";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
}

echo "\n=== END OF DIAGNOSTIC ===\n";
