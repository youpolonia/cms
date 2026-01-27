<?php
/**
 * Migration: Create TB4 (Theme Builder 4.0) tables
 *
 * Creates tb4_pages, tb4_layouts, tb4_presets, tb4_templates, tb4_history tables
 * for the next-generation visual page builder with CSS caching and template conditions.
 *
 * @version 4.0
 */

require_once dirname(__DIR__, 2) . '/database.php';

class Migration_001_create_tb4_tables
{
    /**
     * Execute the migration - create all TB4 tables
     */
    public function execute(\PDO $db): bool
    {
        // Create tb4_pages - stores page builder content with CSS cache
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb4_pages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NOT NULL,
                content_json LONGTEXT NOT NULL,
                css_cache TEXT NULL COMMENT 'Pre-compiled CSS for this page',
                version INT UNSIGNED DEFAULT 1,
                updated_by INT UNSIGNED NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY idx_page_id (page_id),
                INDEX idx_updated_at (updated_at),
                INDEX idx_updated_by (updated_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb4_layouts - reusable layouts (headers, footers, sections)
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb4_layouts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type ENUM('header', 'footer', 'section', 'row', 'full_page') NOT NULL,
                category VARCHAR(100) NULL,
                content_json LONGTEXT NOT NULL,
                thumbnail VARCHAR(500) NULL,
                is_global TINYINT(1) DEFAULT 0,
                created_by INT UNSIGNED NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_category (category),
                INDEX idx_is_global (is_global),
                INDEX idx_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb4_presets - module-specific style presets
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb4_presets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                preset_type ENUM('module', 'section', 'row', 'global') NOT NULL DEFAULT 'module',
                name VARCHAR(255) NOT NULL,
                module_slug VARCHAR(50) NULL COMMENT 'Module this preset applies to (null for global)',
                settings_json TEXT NOT NULL,
                is_default TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_preset_type (preset_type),
                INDEX idx_module_slug (module_slug),
                INDEX idx_is_default (is_default)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb4_templates - conditional templates with priority
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb4_templates (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type ENUM('page', 'archive', 'single', 'category', 'search', '404', 'blank') NOT NULL,
                content_json LONGTEXT NOT NULL,
                conditions_json TEXT NULL COMMENT 'JSON conditions for when this template applies',
                priority INT DEFAULT 10 COMMENT 'Higher priority templates take precedence',
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_priority (priority),
                INDEX idx_is_active (is_active),
                INDEX idx_type_priority_active (type, priority DESC, is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create tb4_history - revision history for pages
        $db->exec("
            CREATE TABLE IF NOT EXISTS tb4_history (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NOT NULL,
                content_json LONGTEXT NOT NULL,
                action_type ENUM('create', 'update', 'restore', 'autosave') NOT NULL DEFAULT 'update',
                created_by INT UNSIGNED NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_page_id (page_id),
                INDEX idx_page_date (page_id, created_at DESC),
                INDEX idx_action_type (action_type),
                INDEX idx_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        return true;
    }

    /**
     * Rollback the migration - drop all TB4 tables
     */
    public function down(\PDO $db): bool
    {
        $db->exec("DROP TABLE IF EXISTS tb4_history");
        $db->exec("DROP TABLE IF EXISTS tb4_templates");
        $db->exec("DROP TABLE IF EXISTS tb4_presets");
        $db->exec("DROP TABLE IF EXISTS tb4_layouts");
        $db->exec("DROP TABLE IF EXISTS tb4_pages");

        return true;
    }

    /**
     * Run migration using centralized database connection
     */
    public static function run(): bool
    {
        $db = \core\Database::connection();
        $migration = new self();
        return $migration->execute($db);
    }

    /**
     * Rollback migration using centralized database connection
     */
    public static function rollback(): bool
    {
        $db = \core\Database::connection();
        $migration = new self();
        return $migration->down($db);
    }
}
