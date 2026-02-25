<?php
/**
 * Jessie Newsletter+ — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_lists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#6366f1',
    subscriber_count INT UNSIGNED DEFAULT 0,
    status ENUM('active','archived') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(100) DEFAULT '',
    status ENUM('active','unsubscribed','bounced','pending') DEFAULT 'pending',
    lists JSON DEFAULT NULL,
    custom_fields JSON DEFAULT NULL,
    source VARCHAR(50) DEFAULT 'manual',
    ip_address VARCHAR(45) DEFAULT NULL,
    confirmed_at DATETIME DEFAULT NULL,
    unsubscribed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_email (email),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    subject VARCHAR(255) NOT NULL DEFAULT '',
    preview_text VARCHAR(200) DEFAULT '',
    from_name VARCHAR(100) DEFAULT '',
    from_email VARCHAR(255) DEFAULT '',
    reply_to VARCHAR(255) DEFAULT '',
    content_html LONGTEXT,
    content_text TEXT,
    template_id INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','scheduled','sending','sent','paused') DEFAULT 'draft',
    list_id INT UNSIGNED DEFAULT NULL,
    segment_conditions JSON DEFAULT NULL,
    scheduled_at DATETIME DEFAULT NULL,
    started_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    stats_sent INT UNSIGNED DEFAULT 0,
    stats_opened INT UNSIGNED DEFAULT 0,
    stats_clicked INT UNSIGNED DEFAULT 0,
    stats_bounced INT UNSIGNED DEFAULT 0,
    stats_unsubscribed INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_list (list_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) DEFAULT 'custom',
    content_html LONGTEXT,
    thumbnail VARCHAR(255) DEFAULT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT UNSIGNED NOT NULL,
    subscriber_id INT UNSIGNED DEFAULT NULL,
    event_type ENUM('sent','opened','clicked','bounced','unsubscribed','complained') NOT NULL,
    metadata JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_campaign (campaign_id),
    KEY idx_subscriber (subscriber_id),
    KEY idx_type_date (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_automations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    trigger_type ENUM('subscribe','tag_added','date_field','manual') DEFAULT 'subscribe',
    trigger_config JSON DEFAULT NULL,
    steps JSON DEFAULT NULL,
    status ENUM('active','paused','draft') DEFAULT 'draft',
    list_id INT UNSIGNED DEFAULT NULL,
    stats_entered INT UNSIGNED DEFAULT 0,
    stats_completed INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Default templates
$check = $pdo->query("SELECT COUNT(*) FROM newsletter_templates WHERE is_default = 1")->fetchColumn();
if ((int)$check === 0) {
    $pdo->exec("INSERT INTO newsletter_templates (name, category, content_html, is_default) VALUES
    ('Clean Minimal', 'basic', '<div style=\"max-width:600px;margin:0 auto;font-family:-apple-system,BlinkMacSystemFont,sans-serif;color:#1e293b\"><div style=\"padding:40px 30px;background:#fff\"><h1 style=\"margin:0 0 20px;font-size:24px;color:#0f172a\">{{title}}</h1><div style=\"line-height:1.7;color:#334155\">{{content}}</div><div style=\"margin-top:30px\"><a href=\"{{cta_url}}\" style=\"display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600\">{{cta_text}}</a></div></div><div style=\"padding:20px 30px;text-align:center;font-size:12px;color:#94a3b8\"><p>{{company_name}} • <a href=\"{{unsubscribe_url}}\" style=\"color:#94a3b8\">Unsubscribe</a></p></div></div>', 1),
    ('Bold Header', 'promotional', '<div style=\"max-width:600px;margin:0 auto;font-family:-apple-system,BlinkMacSystemFont,sans-serif\"><div style=\"background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:50px 30px;text-align:center\"><h1 style=\"color:#fff;font-size:28px;margin:0 0 10px\">{{title}}</h1><p style=\"color:rgba(255,255,255,.8);margin:0;font-size:16px\">{{subtitle}}</p></div><div style=\"padding:40px 30px;background:#fff;color:#334155;line-height:1.7\">{{content}}<div style=\"margin-top:30px;text-align:center\"><a href=\"{{cta_url}}\" style=\"display:inline-block;padding:14px 32px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:16px\">{{cta_text}}</a></div></div><div style=\"padding:20px 30px;text-align:center;font-size:12px;color:#94a3b8;background:#f8fafc\"><p>{{company_name}} • <a href=\"{{unsubscribe_url}}\" style=\"color:#6366f1\">Unsubscribe</a></p></div></div>', 1),
    ('Plain Text', 'transactional', '<div style=\"max-width:600px;margin:0 auto;padding:30px;font-family:Georgia,serif;color:#1e293b;line-height:1.8;font-size:16px\"><p>{{content}}</p><p style=\"margin-top:20px\">— {{sender_name}}</p><hr style=\"border:none;border-top:1px solid #e2e8f0;margin:30px 0\"><p style=\"font-size:12px;color:#94a3b8\"><a href=\"{{unsubscribe_url}}\" style=\"color:#94a3b8\">Unsubscribe</a></p></div>', 1)
    ");
}

echo "Newsletter tables created successfully.\n";
