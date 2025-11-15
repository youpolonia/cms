<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

try {
    // Connect to the database
    $pdo = \core\Database::connection();
    
    echo "<h1>Database Table Check</h1>";
    
    // Check if content_items table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'content_items'");
    $content_items_exists = $stmt->rowCount() > 0;
    echo "<p>content_items table exists: " . ($content_items_exists ? "Yes" : "No") . "</p>";
    
    // Check if content_schedules table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'content_schedules'");
    $content_schedules_exists = $stmt->rowCount() > 0;
    echo "<p>content_schedules table exists: " . ($content_schedules_exists ? "Yes" : "No") . "</p>";
    
    // Check if versions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'versions'");
    $versions_exists = $stmt->rowCount() > 0;
    echo "<p>versions table exists: " . ($versions_exists ? "Yes" : "No") . "</p>";
    
    // List all tables in the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>All Tables in Database:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (\Throwable $e) {
    echo "<h1>Database Connection Error</h1>";
    echo "<p>Database check failed</p>";
}