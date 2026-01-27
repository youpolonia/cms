<?php
/**
 * Creates scheduled_tasks table for task scheduling system
 */

require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../../core/database.php';
try {
    $pdo = \core\Database::connection();

    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS scheduled_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NULL COMMENT 'Null for global tasks',
        task_class VARCHAR(255) NOT NULL COMMENT 'Fully qualified class name',
        interval VARCHAR(255) NOT NULL COMMENT 'Cron expression',
        is_active BOOLEAN DEFAULT TRUE,
        last_run TIMESTAMP NULL,
        next_run TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant_id (tenant_id),
        INDEX idx_next_run (next_run),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    error_log('Database error');
    http_response_code(500);
    exit;
}
