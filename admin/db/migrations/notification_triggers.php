<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class NotificationTriggersMigration {
    public static function execute() {
        $db = \core\Database::connection();
        
        $db->query('CREATE TABLE IF NOT EXISTS notification_triggers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            rule_id INT NOT NULL,
            event_type VARCHAR(100) NOT NULL,
            trigger_params JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_rule (rule_id),
            INDEX idx_event_type (event_type),
            CONSTRAINT fk_triggers_rule FOREIGN KEY (rule_id) 
                REFERENCES notification_rules(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
}
