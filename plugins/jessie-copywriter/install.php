<?php
/**
 * Jessie AI Copywriter — database installer
 */
function copywriter_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS copywriter_brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            tone VARCHAR(100) DEFAULT 'professional',
            vocabulary_json TEXT DEFAULT NULL,
            guidelines_json TEXT DEFAULT NULL,
            examples TEXT DEFAULT NULL,
            status ENUM('active','archived') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS copywriter_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            batch_id INT DEFAULT NULL,
            brand_id INT DEFAULT NULL,
            product_name VARCHAR(500) NOT NULL,
            product_features TEXT DEFAULT NULL,
            product_category VARCHAR(255) DEFAULT '',
            platform VARCHAR(50) NOT NULL DEFAULT 'general',
            tone VARCHAR(50) DEFAULT 'professional',
            title TEXT DEFAULT NULL,
            description TEXT DEFAULT NULL,
            bullet_points TEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description VARCHAR(500) DEFAULT NULL,
            tags TEXT DEFAULT NULL,
            raw_ai_response TEXT DEFAULT NULL,
            credits_used INT DEFAULT 1,
            status ENUM('pending','completed','failed') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_batch (batch_id),
            INDEX idx_platform (platform),
            INDEX idx_status (status),
            INDEX idx_created (created_at),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS copywriter_batches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            brand_id INT DEFAULT NULL,
            platform VARCHAR(50) NOT NULL DEFAULT 'general',
            tone VARCHAR(50) DEFAULT 'professional',
            total_items INT DEFAULT 0,
            completed_items INT DEFAULT 0,
            failed_items INT DEFAULT 0,
            status ENUM('pending','processing','completed','failed') DEFAULT 'pending',
            csv_filename VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
}
