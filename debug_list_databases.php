<?php
// DEV_MODE gate - only allow in development mode
require_once __DIR__ . '/config.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    die("This debug script is only available in development mode.\n");
}

echo "Database List Debug Script\n";
echo "==========================\n";

try {
    $pdo = \core\Database::connection();
    
    // List all databases
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Available databases:\n";
    foreach ($databases as $db) {
        echo "- {$db}\n";
    }
    
    echo "\nConnection successful using standardized Database::connection()\n";
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Database error, please try again later.\n";
}
