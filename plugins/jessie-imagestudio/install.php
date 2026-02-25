<?php
/**
 * Image Studio — database installer
 * Tables: imagestudio_images, imagestudio_jobs
 */
function imagestudio_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS imagestudio_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_filename VARCHAR(255) DEFAULT '',
            file_path VARCHAR(500) NOT NULL,
            file_url VARCHAR(500) NOT NULL,
            file_size INT DEFAULT 0,
            mime_type VARCHAR(50) DEFAULT '',
            width INT DEFAULT 0,
            height INT DEFAULT 0,
            type ENUM('upload','remove_bg','enhanced','generated','resized','alt_text') DEFAULT 'upload',
            source_image_id INT DEFAULT NULL,
            prompt TEXT DEFAULT NULL,
            alt_text TEXT DEFAULT NULL,
            metadata_json TEXT DEFAULT NULL,
            status ENUM('pending','processing','completed','failed') DEFAULT 'completed',
            credits_used INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_type (type),
            INDEX idx_status (status),
            INDEX idx_created (created_at),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS imagestudio_jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            batch_id VARCHAR(64) DEFAULT NULL,
            job_type ENUM('remove_bg','alt_text','enhance','generate','resize') NOT NULL,
            input_image_id INT DEFAULT NULL,
            input_data TEXT DEFAULT NULL,
            output_image_id INT DEFAULT NULL,
            output_data TEXT DEFAULT NULL,
            progress TINYINT DEFAULT 0,
            status ENUM('queued','processing','completed','failed','cancelled') DEFAULT 'queued',
            error_message TEXT DEFAULT NULL,
            started_at DATETIME DEFAULT NULL,
            completed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_batch (batch_id),
            INDEX idx_status (status),
            INDEX idx_type (job_type),
            FOREIGN KEY (user_id) REFERENCES saas_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }

    // Insert plans if not present
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saas_plans WHERE service = 'imagestudio'");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec("INSERT INTO saas_plans (slug, name, service, price_monthly, price_yearly, credits_monthly, features_json, limits_json, sort_order, status) VALUES
            ('imagestudio-free', 'Free', 'imagestudio', 0, 0, 10, '{\"remove_bg\":true,\"alt_text\":true,\"enhance\":true,\"generate\":true,\"resize\":true,\"batch\":false}', '{\"credits_monthly\":10,\"max_file_mb\":5}', 1, 'active'),
            ('imagestudio-pro', 'Pro', 'imagestudio', 19.99, 199.99, 500, '{\"remove_bg\":true,\"alt_text\":true,\"enhance\":true,\"generate\":true,\"resize\":true,\"batch\":true}', '{\"credits_monthly\":500,\"max_file_mb\":25}', 2, 'active'),
            ('imagestudio-business', 'Business', 'imagestudio', 49.99, 499.99, 2000, '{\"remove_bg\":true,\"alt_text\":true,\"enhance\":true,\"generate\":true,\"resize\":true,\"batch\":true,\"api_access\":true}', '{\"credits_monthly\":2000,\"max_file_mb\":50}', 3, 'active')
        ");
    }
}
