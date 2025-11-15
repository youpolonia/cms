<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class NotificationRulesMigration {
    public static function execute() {
        $db = \core\Database::connection();
        
        $db->query('CREATE TABLE IF NOT EXISTS notification_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            type VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "active",
            conditions JSON NOT NULL,
            actions JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_status (status),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
}
