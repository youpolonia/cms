<?php
function analytics_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS analytics_events (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            event_source VARCHAR(50) NOT NULL DEFAULT 'web',
            page_url VARCHAR(2000) DEFAULT '',
            referrer VARCHAR(2000) DEFAULT '',
            session_id VARCHAR(64) DEFAULT '',
            ip_hash VARCHAR(64) DEFAULT '',
            user_agent VARCHAR(500) DEFAULT '',
            country VARCHAR(5) DEFAULT '',
            device VARCHAR(20) DEFAULT '',
            metadata_json TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_type (event_type),
            INDEX idx_source (event_source),
            INDEX idx_created (created_at),
            INDEX idx_session (session_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS analytics_goals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            target_value INT DEFAULT 0,
            current_value INT DEFAULT 0,
            period ENUM('daily','weekly','monthly','all_time') DEFAULT 'monthly',
            status ENUM('active','achieved','expired') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS analytics_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            report_type VARCHAR(50) NOT NULL DEFAULT 'custom',
            config_json TEXT DEFAULT NULL,
            data_json LONGTEXT DEFAULT NULL,
            generated_at DATETIME DEFAULT NULL,
            status ENUM('pending','generating','ready','failed') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) { $pdo->exec($sql); }

    // Add analytics plans if missing
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saas_plans WHERE service = 'analytics'");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec("INSERT INTO saas_plans (slug, name, service, price_monthly, price_yearly, credits_monthly, features_json, limits_json, sort_order, status) VALUES
            ('analytics-free', 'Free', 'analytics', 0, 0, 100, '{\"events\":true,\"goals\":true,\"reports\":false,\"ai_insights\":false}', '{\"events_per_month\":10000,\"reports\":0}', 1, 'active'),
            ('analytics-pro', 'Pro', 'analytics', 29, 290, 1000, '{\"events\":true,\"goals\":true,\"reports\":true,\"ai_insights\":true}', '{\"events_per_month\":100000,\"reports\":50}', 2, 'active'),
            ('analytics-business', 'Business', 'analytics', 79, 790, 5000, '{\"events\":true,\"goals\":true,\"reports\":true,\"ai_insights\":true,\"api\":true,\"export\":true}', '{\"events_per_month\":1000000,\"reports\":999}', 3, 'active')
        ");
    }
}
