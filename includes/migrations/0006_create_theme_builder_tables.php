<?php
/**
 * Migration: Create Theme Builder 3.0 tables
 * Creates tb_pages, tb_templates, tb_presets, tb_revisions tables
 * Divi-style Section→Row→Column→Module hierarchy support
 */
require_once __DIR__ . '/abstractmigration.php';

class Migration_0006_create_theme_builder_tables extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // Create tb_pages table - stores page builder content
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb_pages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NOT NULL,
                content_json LONGTEXT NOT NULL,
                version VARCHAR(10) DEFAULT '3.0',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_page (page_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb_templates table - reusable templates and layouts
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb_templates (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type ENUM('page', 'header', 'footer', 'section', 'row', 'module') NOT NULL,
                category VARCHAR(100) NULL,
                content_json LONGTEXT NOT NULL,
                thumbnail VARCHAR(500) NULL,
                is_global TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_category (category),
                INDEX idx_is_global (is_global)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb_presets table - module-specific style presets
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb_presets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                module_type VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                settings_json TEXT NOT NULL,
                is_default TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_module_type (module_type),
                INDEX idx_is_default (is_default)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb_revisions table - page revision history
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb_revisions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NOT NULL,
                content_json LONGTEXT NOT NULL,
                user_id INT UNSIGNED NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_page_date (page_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        return true;
    }
}
