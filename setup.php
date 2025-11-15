<?php
/**
 * CMS First-Run Setup Script
 * 
 * This script handles initial configuration and database setup
 */

// Basic security check
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    die('Direct access forbidden');
}

// Check if config exists
if (file_exists(__DIR__ . '/config.php')) {
    die('Setup already completed. Remove this file for security.');
}

// Create default config if sample exists
if (file_exists(__DIR__ . '/config_sample.php')) {
    copy(__DIR__ . '/config_sample.php', __DIR__ . '/config.php');
    echo "Created config.php from sample\n";
}

// Check database connection
require_once __DIR__ . '/includes/config.php';
$config = new Config();

try {
    $db = \core\Database::connection();
    
    // Create tables if they don't exist
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    /* exec disabled: was $db->exec($sql) */
    
    // Create admin user
    $stmt = $db->prepare("INSERT INTO users (username, password, email, role) 
                         VALUES (?, ?, ?, 'admin')");
    $password = password_hash('changeme', PASSWORD_DEFAULT);
    $stmt->execute(['admin', $password, 'admin@example.com']);
    
    echo "Setup completed successfully!\n";
    echo "Admin credentials: admin / changeme\n";
    echo "IMPORTANT: Change password immediately after login\n";
    
    // Remove setup file for security
    if (unlink(__FILE__)) {
        echo "Setup script removed for security\n";
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database error");
}
