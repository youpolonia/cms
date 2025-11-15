<?php
/**
 * Admin Verification Script
 * Securely verifies admin credentials against database
 */

// Use root config for database configuration
require_once dirname(__DIR__, 2) . '/config.php';

// Create secure database connection
try {
    $db = \core\Database::connection();
    
    // Verify admin user exists with password 'admin123'
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = SHA2(CONCAT(:password, salt), 256) AND is_admin = 1");
    $stmt->execute([':username' => 'admin', ':password' => 'admin123']);
    $admin = $stmt->fetch();
    
    // Debug output
    echo "<pre>";
    echo "Database Connection: " . ($db ? "Successful" : "Failed") . "\n";
    if ($admin) {
        echo "Admin Verification: Success\n";
        echo "Admin User Details:\n";
        print_r($admin);
    } else {
        echo "Admin Verification: Failed - User not found or invalid credentials\n";
    }
    echo "</pre>";

} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    exit;
}
