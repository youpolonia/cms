<?php
function social_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS social_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            platform ENUM('twitter','linkedin','facebook','instagram','tiktok','pinterest') NOT NULL,
            account_name VARCHAR(255) DEFAULT '',
            access_token TEXT DEFAULT NULL,
            refresh_token TEXT DEFAULT NULL,
            token_expires_at DATETIME DEFAULT NULL,
            profile_url VARCHAR(500) DEFAULT '',
            avatar_url VARCHAR(500) DEFAULT '',
            status ENUM('active','expired','disconnected') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_platform (platform),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS social_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            account_id INT DEFAULT NULL,
            platform VARCHAR(50) NOT NULL,
            content TEXT NOT NULL,
            media_urls TEXT DEFAULT NULL,
            hashtags TEXT DEFAULT NULL,
            scheduled_at DATETIME DEFAULT NULL,
            published_at DATETIME DEFAULT NULL,
            external_post_id VARCHAR(255) DEFAULT NULL,
            engagement_json TEXT DEFAULT NULL,
            status ENUM('draft','scheduled','publishing','published','failed') DEFAULT 'draft',
            error_message TEXT DEFAULT NULL,
            credits_used INT DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_account (account_id),
            INDEX idx_status (status),
            INDEX idx_scheduled (scheduled_at),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS social_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            platform VARCHAR(50) DEFAULT 'all',
            content TEXT NOT NULL,
            hashtags TEXT DEFAULT NULL,
            category VARCHAR(100) DEFAULT '',
            is_ai_generated TINYINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS social_analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            impressions INT DEFAULT 0,
            clicks INT DEFAULT 0,
            likes INT DEFAULT 0,
            shares INT DEFAULT 0,
            comments INT DEFAULT 0,
            reach INT DEFAULT 0,
            fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_post (post_id),
            FOREIGN KEY (post_id) REFERENCES social_posts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) { $pdo->exec($sql); }
}
