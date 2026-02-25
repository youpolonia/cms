<?php
/**
 * Jessie Affiliate — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    commission_type ENUM('percentage','fixed') DEFAULT 'percentage',
    commission_value DECIMAL(10,2) DEFAULT 0,
    cookie_days INT UNSIGNED DEFAULT 30,
    min_payout DECIMAL(10,2) DEFAULT 50.00,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS affiliates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    referral_code VARCHAR(50) NOT NULL UNIQUE,
    website VARCHAR(255) DEFAULT '',
    payment_method VARCHAR(50) DEFAULT '',
    payment_details TEXT,
    total_clicks INT UNSIGNED DEFAULT 0,
    total_conversions INT UNSIGNED DEFAULT 0,
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    pending_payout DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('active','pending','suspended') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_program (program_id),
    KEY idx_status (status),
    KEY idx_email (email),
    KEY idx_referral (referral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_conversions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NOT NULL,
    order_id VARCHAR(100) DEFAULT '',
    order_total DECIMAL(12,2) DEFAULT 0.00,
    commission DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('pending','approved','rejected','paid') DEFAULT 'pending',
    ip_address VARCHAR(45) DEFAULT '',
    user_agent VARCHAR(500) DEFAULT '',
    referred_url VARCHAR(500) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_affiliate (affiliate_id),
    KEY idx_program (program_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_payouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT '',
    payment_reference VARCHAR(255) DEFAULT '',
    status ENUM('pending','completed','failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_affiliate (affiliate_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert default settings
$defaults = [
    ['default_cookie_days', '30'],
    ['default_min_payout', '50'],
    ['auto_approve_affiliates', '0'],
    ['terms_and_conditions', 'By joining our affiliate program, you agree to promote our products ethically and in compliance with all applicable laws.'],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO affiliate_settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($defaults as $d) {
    $stmt->execute($d);
}

echo "Affiliate tables created successfully.\n";
