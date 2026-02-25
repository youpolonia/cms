<?php
function emailmarketing_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS em_lists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            subscriber_count INT DEFAULT 0,
            status ENUM('active','archived') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS em_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            list_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) DEFAULT '',
            tags TEXT DEFAULT NULL,
            custom_fields_json TEXT DEFAULT NULL,
            status ENUM('active','unsubscribed','bounced') DEFAULT 'active',
            subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at DATETIME DEFAULT NULL,
            INDEX idx_user (user_id),
            INDEX idx_list (list_id),
            INDEX idx_email (email),
            INDEX idx_status (status),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE,
            FOREIGN KEY (list_id) REFERENCES em_lists(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS em_campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            list_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            subject VARCHAR(500) NOT NULL DEFAULT '',
            preview_text VARCHAR(255) DEFAULT '',
            from_name VARCHAR(255) DEFAULT '',
            from_email VARCHAR(255) DEFAULT '',
            html_body LONGTEXT DEFAULT NULL,
            text_body LONGTEXT DEFAULT NULL,
            template_id INT DEFAULT NULL,
            scheduled_at DATETIME DEFAULT NULL,
            sent_at DATETIME DEFAULT NULL,
            total_sent INT DEFAULT 0,
            total_opened INT DEFAULT 0,
            total_clicked INT DEFAULT 0,
            total_bounced INT DEFAULT 0,
            total_unsubscribed INT DEFAULT 0,
            status ENUM('draft','scheduled','sending','sent','failed') DEFAULT 'draft',
            credits_used INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS em_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) DEFAULT 'general',
            html_body LONGTEXT NOT NULL,
            thumbnail_url VARCHAR(500) DEFAULT '',
            is_global TINYINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS em_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_id INT NOT NULL,
            subscriber_id INT DEFAULT NULL,
            event_type ENUM('sent','opened','clicked','bounced','unsubscribed') NOT NULL,
            metadata VARCHAR(500) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_campaign (campaign_id),
            INDEX idx_type (event_type),
            FOREIGN KEY (campaign_id) REFERENCES em_campaigns(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS em_automations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            trigger_type ENUM('subscribe','tag_added','date','inactivity') NOT NULL,
            trigger_config TEXT DEFAULT NULL,
            action_type ENUM('send_email','add_tag','wait','condition') NOT NULL,
            action_config TEXT DEFAULT NULL,
            sequence_order INT DEFAULT 0,
            status ENUM('active','paused','draft') DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) { $pdo->exec($sql); }
}
