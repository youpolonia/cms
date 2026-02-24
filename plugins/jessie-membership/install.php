<?php
/**
 * Jessie Membership — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS membership_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0,
    billing_period ENUM('monthly','quarterly','yearly','lifetime','free') DEFAULT 'monthly',
    trial_days INT UNSIGNED DEFAULT 0,
    features JSON DEFAULT NULL,
    content_access JSON DEFAULT NULL,
    max_members INT UNSIGNED DEFAULT 0,
    color VARCHAR(7) DEFAULT '#6366f1',
    sort_order INT DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS membership_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(100) DEFAULT '',
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('active','trial','expired','cancelled','paused') DEFAULT 'trial',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    trial_ends_at DATETIME DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_ref VARCHAR(255) DEFAULT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_plan (plan_id),
    KEY idx_user (user_id),
    KEY idx_status (status),
    KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS membership_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    type ENUM('payment','refund','trial','upgrade','downgrade') DEFAULT 'payment',
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_ref VARCHAR(255) DEFAULT NULL,
    status ENUM('completed','pending','failed','refunded') DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_member (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS membership_content_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('page','article','category','custom') DEFAULT 'page',
    content_id INT UNSIGNED DEFAULT NULL,
    content_pattern VARCHAR(255) DEFAULT NULL,
    plan_ids JSON DEFAULT NULL,
    rule_type ENUM('require_any','require_all','exclude') DEFAULT 'require_any',
    message TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Membership tables created successfully.\n";
