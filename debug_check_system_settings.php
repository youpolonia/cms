<?php

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

// Load configuration
$config = require_once 'includes/config.php';

if (!isset($config['db_user'], $config['db_pass'])) {
    die("Error: Database credentials not found in config\n");
}

try {
    echo "Attempting centralized database connection\n";
    
    try {
        require_once __DIR__ . '/core/database.php';
        $db = \core\Database::connection();

        // Check if system_settings table exists
        $stmt = $db->query("SHOW TABLES LIKE 'system_settings'");
        $tableExists = $stmt->fetch();

        if ($tableExists) {
            // Get table schema
            $schema = $db->query("DESCRIBE system_settings")->fetchAll();
            echo "system_settings table exists with schema:\n";
            print_r($schema);
        } else {
            echo "system_settings table does not exist\n";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
