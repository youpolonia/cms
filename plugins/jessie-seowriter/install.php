<?php
/**
 * SEO Writer — Database installer
 */
function seowriter_install(): void {
    if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
    require_once CMS_ROOT . '/db.php';
    $pdo = \core\Database::connection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS seowriter_projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            target_keyword VARCHAR(255) DEFAULT '',
            language VARCHAR(10) DEFAULT 'en',
            status ENUM('active','archived') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS seowriter_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            project_id INT DEFAULT NULL,
            title VARCHAR(500) DEFAULT '',
            meta_description VARCHAR(500) DEFAULT '',
            target_keyword VARCHAR(255) DEFAULT '',
            body LONGTEXT DEFAULT NULL,
            outline_json TEXT DEFAULT NULL,
            seo_score INT DEFAULT 0,
            word_count INT DEFAULT 0,
            status ENUM('draft','generating','complete','published') DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_project (project_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS seowriter_audits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            project_id INT DEFAULT NULL,
            url VARCHAR(2000) NOT NULL,
            score INT DEFAULT 0,
            issues_json LONGTEXT DEFAULT NULL,
            meta_json TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_project (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }

    // Seed free plan if not exists
    $stmt = $pdo->prepare("SELECT id FROM saas_plans WHERE service = 'seowriter' AND slug = 'seowriter-free' LIMIT 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO saas_plans (slug, name, service, price_monthly, price_yearly, credits_monthly, features_json, limits_json, sort_order, status)
            VALUES
            ('seowriter-free',   'Free',         'seowriter', 0,     0,     50,   '{\"keywords\":true,\"generate\":true,\"audit\":true}',  '{\"articles_per_month\":5,\"audits_per_month\":10}', 1, 'active'),
            ('seowriter-pro',    'Pro',           'seowriter', 29,    290,   500,  '{\"keywords\":true,\"generate\":true,\"audit\":true,\"bulk\":true}', '{\"articles_per_month\":50,\"audits_per_month\":100}', 2, 'active'),
            ('seowriter-agency', 'Agency',        'seowriter', 99,    990,   2000, '{\"keywords\":true,\"generate\":true,\"audit\":true,\"bulk\":true,\"api\":true}', '{\"articles_per_month\":200,\"audits_per_month\":500}', 3, 'active')
        ");
    }
}
