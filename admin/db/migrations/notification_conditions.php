<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class NotificationConditionsMigration {
    public static function execute() {
        $db = \core\Database::connection();
        
        $db->query('CREATE TABLE IF NOT EXISTS notification_conditions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            rule_id INT NOT NULL,
            field VARCHAR(100) NOT NULL,
            operator VARCHAR(20) NOT NULL,
            value TEXT,
            logical_operator VARCHAR(3),
            condition_group INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_rule (rule_id),
            CONSTRAINT fk_conditions_rule FOREIGN KEY (rule_id) 
                REFERENCES notification_rules(id) ON DELETE CASCADE,
            INDEX idx_condition_group (condition_group)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
}
