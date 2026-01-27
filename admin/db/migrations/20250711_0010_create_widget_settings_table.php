<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Creates widget_settings table - pure SQL version
 */

try {
    $pdo = \core\Database::connection();

    // Create table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS widget_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            widget_type VARCHAR(50) NOT NULL,
            config_json JSON NOT NULL,
            tenant_id INT NOT NULL,
            created_by INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            INDEX idx_widget_settings_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Log success
    file_put_contents(
        __DIR__ . '/../../memory-bank/progress.md',
        "## [2025-07-12] Migration: Rewrote widget_settings table creation with pure SQL\n",
        FILE_APPEND
    );

} catch (PDOException $e) {
    error_log('Database error');
    http_response_code(500);
    exit;
}

// Drop script (separate file would be better but keeping here for now)
function drop_widget_settings_table() {
    try {
        $pdo = \core\Database::connection();
        $pdo->exec("DROP TABLE IF EXISTS widget_settings");
    } catch (PDOException $e) {
        error_log('Database error');
        http_response_code(500);
        exit;
    }
}
