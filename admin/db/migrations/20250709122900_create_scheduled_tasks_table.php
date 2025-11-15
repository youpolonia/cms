<?php
/**
 * Migration for scheduled tasks table
 * Created: 2025-07-09 12:29:00
 */

require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

// Ensure this is only executed via require_once, not directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not allowed');
}
require_once __DIR__ . '/../../../../core/database.php';
$pdo = \core\Database::connection();

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS scheduled_tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_name VARCHAR(255) NOT NULL,
            interval_minutes INT NOT NULL,
            last_run DATETIME NULL,
            tenant_id INT NULL,
            status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant_id (tenant_id),
            INDEX idx_status (status),
            INDEX idx_last_run (last_run)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_COLLATION . ";
    ");
    
    file_put_contents(__DIR__ . '/../../../../memory-bank/progress.md', 
        "[" . date('Y-m-d H:i:s') . "] Created scheduled_tasks table migration\n", 
        FILE_APPEND);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/../../../../memory-bank/progress.md', 
        "[" . date('Y-m-d H:i:s') . "] Database error\n", 
        FILE_APPEND);
    error_log('Database error');
    http_response_code(500);
    exit;
}
