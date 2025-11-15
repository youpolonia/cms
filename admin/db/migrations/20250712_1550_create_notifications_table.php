<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Migration to create notifications table
 */
class NotificationsMigration {
    public static function run(PDO $db): void {
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    message TEXT NOT NULL,
                    is_read TINYINT(1) DEFAULT 0,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_notifications_user (user_id),
                    INDEX idx_notifications_read (is_read)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            file_put_contents(
                __DIR__ . '/../../memory-bank/progress.md',
                "## [2025-07-12] Migration: Created notifications table\n",
                FILE_APPEND
            );
        } catch (PDOException $e) {
            error_log('Database error');
            http_response_code(500);
            exit;
        }
    }

    public static function revert(PDO $db): void {
        $db->exec("DROP TABLE IF EXISTS notifications");
    }
}
