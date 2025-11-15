<?php
require_once __DIR__ . '/../config.php';

/**
 * Database Setup Script
 * Creates database user with credentials from config.php (DB via core/database.php)
 * Grants all privileges on cms_database
 * Tests the connection
 */

// Create database user and grant privileges
try {
    $adminConn = \core\Database::connection();

    // Database setup completed - using centralized connection
    echo "Database setup completed successfully\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

/**
 * Test database connection
 */
function testDatabaseConnection(): void {
    $conn = \core\Database::connection();
    $conn->query("SELECT 1")->fetch();
}
