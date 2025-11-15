<?php
/**
 * Admin User Verification and Creation Script
 * Ensures admin user exists with password 'admin123'
 */

// Use root config for database configuration
require_once dirname(__DIR__, 2) . '/config.php';

try {
    $db = \core\Database::connection();
    
    // Verify admin user exists with password 'admin123'
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = SHA2(CONCAT(:password, salt), 256) AND is_admin = 1");
    $stmt->execute([':username' => 'admin', ':password' => 'admin123']);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        // Create admin user if not found
        $salt = bin2hex(random_bytes(16));
        $hashedPassword = "SHA2(CONCAT('admin123', '$salt'), 256)";
        
        $db->beginTransaction();
        
        // First delete any existing admin user without correct password
        $stmt = $db->prepare("DELETE FROM users WHERE username = 'admin'");
        $stmt->execute();
        
        // Insert new admin user
        $stmt = $db->prepare("
            INSERT INTO users 
            (username, password, salt, is_admin, created_at, updated_at) 
            VALUES 
            (:username, $hashedPassword, :salt, 1, NOW(), NOW())
        ");
        $stmt->execute([':username' => 'admin', ':salt' => $salt]);
        
        $db->commit();
        
        echo "Admin user created successfully\n";
    } else {
        echo "Admin user already exists with correct credentials\n";
    }
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    error_log($e->getMessage());
    exit;
}
