<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Admin Users Initialization Script
 * Creates admin_users table and inserts test user if not exists
 */

require_once __DIR__ . '/../core/database.php';

try {
    $pdo = \core\Database::connection();

    // Load database configuration for table prefix
    $dbConfig = []; // legacy alt DB config removed; use \core\Database::connection()
    $mysqlConfig = $dbConfig['connections']['mysql'];
    
    // Check if table exists
    $tableName = $mysqlConfig['prefix'] . 'admin_users';
    $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
    $tableExists = $stmt->fetch() !== false;
    
    if (!$tableExists) {
        // Create table
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET={$mysqlConfig['charset']}");
        $stmt->execute();
        
        echo "Created table $tableName\n";
    }
    
    // Insert test user if not exists
    $username = 'admin';
    $password = 'password';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM $tableName WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch() === false) {
        $stmt = $pdo->prepare("INSERT INTO $tableName (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $passwordHash]);
        echo "Created test user: $username/$password\n";
    } else {
        echo "Test user already exists\n";
    }
    
    echo "Initialization completed successfully\n";
    exit(0);
} catch (\Throwable $e) {
    echo "Error occurred\n";
    error_log($e->getMessage());
    exit(1);
}
