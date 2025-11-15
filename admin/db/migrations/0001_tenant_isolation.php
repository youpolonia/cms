<?php
/**
 * Pure PDO Migration for Tenant Isolation
 * Uses direct PDO connection with credentials from config.php
 * No classes, no framework helpers, no ORM patterns
 */

require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

// Database connection setup (centralized)
require_once __DIR__ . '/../../../core/database.php';
$pdo = \core\Database::connection();

// Create tenants table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tenants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        domain VARCHAR(255) NOT NULL UNIQUE,
        database_name VARCHAR(255) NOT NULL UNIQUE,
        status ENUM('active','suspended','pending') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Add default tenant if none exists
$check = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
if ($check == 0) {
    $pdo->exec("INSERT INTO tenants (name, domain, database_name, status)
               VALUES ('Primary Tenant', 'primary.example.com', 'primary_tenant', 'active')");
}

// Migration complete
return true;
