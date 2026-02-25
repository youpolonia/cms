<?php
// install.php for jessie-saas-core
function saas_core_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();
    
    $tables = [
        "CREATE TABLE IF NOT EXISTS saas_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            name VARCHAR(255) DEFAULT '',
            company VARCHAR(255) DEFAULT '',
            avatar VARCHAR(500) DEFAULT '',
            plan VARCHAR(50) DEFAULT 'free',
            credits_remaining INT DEFAULT 0,
            credits_monthly INT DEFAULT 0,
            api_key VARCHAR(64) DEFAULT NULL UNIQUE,
            api_secret VARCHAR(128) DEFAULT NULL,
            stripe_customer_id VARCHAR(255) DEFAULT NULL,
            stripe_subscription_id VARCHAR(255) DEFAULT NULL,
            email_verified_at DATETIME DEFAULT NULL,
            verification_token VARCHAR(64) DEFAULT NULL,
            reset_token VARCHAR(64) DEFAULT NULL,
            reset_expires DATETIME DEFAULT NULL,
            timezone VARCHAR(50) DEFAULT 'UTC',
            language VARCHAR(10) DEFAULT 'en',
            settings_json TEXT DEFAULT NULL,
            status ENUM('active','suspended','deleted') DEFAULT 'active',
            last_login DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_api_key (api_key),
            INDEX idx_status (status),
            INDEX idx_plan (plan)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS saas_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            service VARCHAR(50) NOT NULL,
            price_monthly DECIMAL(10,2) DEFAULT 0,
            price_yearly DECIMAL(10,2) DEFAULT 0,
            credits_monthly INT DEFAULT 0,
            features_json TEXT DEFAULT NULL,
            limits_json TEXT DEFAULT NULL,
            stripe_price_monthly VARCHAR(255) DEFAULT NULL,
            stripe_price_yearly VARCHAR(255) DEFAULT NULL,
            is_popular TINYINT(1) DEFAULT 0,
            sort_order INT DEFAULT 0,
            status ENUM('active','hidden','deprecated') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_service (service),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS saas_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_id INT NOT NULL,
            service VARCHAR(50) NOT NULL,
            billing_cycle ENUM('monthly','yearly','lifetime','free') DEFAULT 'monthly',
            stripe_subscription_id VARCHAR(255) DEFAULT NULL,
            current_period_start DATETIME DEFAULT NULL,
            current_period_end DATETIME DEFAULT NULL,
            credits_used INT DEFAULT 0,
            credits_limit INT DEFAULT 0,
            status ENUM('active','past_due','cancelled','expired','trial') DEFAULT 'active',
            trial_ends_at DATETIME DEFAULT NULL,
            cancelled_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_service (service),
            INDEX idx_status (status),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE,
            FOREIGN KEY (plan_id) REFERENCES saas_plans(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS saas_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subscription_id INT DEFAULT NULL,
            type ENUM('charge','refund','credit_purchase','credit_usage') NOT NULL,
            amount DECIMAL(10,2) DEFAULT 0,
            currency VARCHAR(3) DEFAULT 'USD',
            credits INT DEFAULT 0,
            description VARCHAR(500) DEFAULT '',
            stripe_payment_id VARCHAR(255) DEFAULT NULL,
            stripe_invoice_id VARCHAR(255) DEFAULT NULL,
            metadata_json TEXT DEFAULT NULL,
            status ENUM('completed','pending','failed','refunded') DEFAULT 'completed',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_type (type),
            INDEX idx_status (status),
            INDEX idx_created (created_at),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS saas_api_usage (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            api_key VARCHAR(64) DEFAULT NULL,
            service VARCHAR(50) NOT NULL,
            endpoint VARCHAR(255) NOT NULL,
            method VARCHAR(10) DEFAULT 'POST',
            credits_used INT DEFAULT 1,
            tokens_in INT DEFAULT 0,
            tokens_out INT DEFAULT 0,
            latency_ms INT DEFAULT 0,
            status_code INT DEFAULT 200,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(500) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_service (service),
            INDEX idx_created (created_at),
            INDEX idx_api_key (api_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS saas_sessions (
            id VARCHAR(128) PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(500) DEFAULT NULL,
            payload TEXT DEFAULT NULL,
            last_activity INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
}
