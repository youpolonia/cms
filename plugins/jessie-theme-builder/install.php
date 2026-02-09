<?php
/**
 * Jessie Theme Builder - Installation Script
 * Creates required database tables
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/../..'));
}

require_once CMS_ROOT . '/core/database.php';

try {
    $db = \core\Database::connection();

    // jtb_pages table
    $db->exec("
        CREATE TABLE IF NOT EXISTS jtb_pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL UNIQUE,
            content JSON NOT NULL,
            css_cache TEXT,
            version VARCHAR(10) DEFAULT '1.0',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_post_id (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // jtb_templates table
    $db->exec("
        CREATE TABLE IF NOT EXISTS jtb_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            content JSON NOT NULL,
            conditions JSON,
            is_active TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (type),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // jtb_global_modules table
    $db->exec("
        CREATE TABLE IF NOT EXISTS jtb_global_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            content JSON NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    error_log("Jessie Theme Builder: Tables created successfully");

} catch (Exception $e) {
    error_log("Jessie Theme Builder installation error: " . $e->getMessage());
    throw $e;
}
