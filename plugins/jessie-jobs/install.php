<?php
/**
 * Jessie Jobs — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS job_companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    logo VARCHAR(255) DEFAULT NULL,
    description TEXT,
    website VARCHAR(255) DEFAULT '',
    industry VARCHAR(100) DEFAULT '',
    size VARCHAR(50) DEFAULT '',
    location VARCHAR(255) DEFAULT '',
    status ENUM('active','hidden') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS job_listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    company_name VARCHAR(200) DEFAULT '',
    company_logo VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT '',
    remote_type ENUM('onsite','remote','hybrid') DEFAULT 'onsite',
    job_type ENUM('full-time','part-time','contract','freelance') DEFAULT 'full-time',
    salary_min DECIMAL(12,2) DEFAULT NULL,
    salary_max DECIMAL(12,2) DEFAULT NULL,
    salary_currency VARCHAR(3) DEFAULT 'USD',
    description TEXT,
    requirements TEXT,
    benefits TEXT,
    category VARCHAR(100) DEFAULT '',
    experience_level ENUM('entry','mid','senior','lead') DEFAULT 'mid',
    skills JSON DEFAULT NULL,
    application_url VARCHAR(500) DEFAULT '',
    application_email VARCHAR(255) DEFAULT '',
    is_featured TINYINT(1) DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    status ENUM('active','expired','draft') DEFAULT 'draft',
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_category (category),
    KEY idx_job_type (job_type),
    KEY idx_remote_type (remote_type),
    KEY idx_featured (is_featured),
    KEY idx_expires (expires_at),
    FULLTEXT idx_search (title, description, requirements, company_name, location, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS job_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    applicant_name VARCHAR(150) NOT NULL,
    applicant_email VARCHAR(255) NOT NULL,
    applicant_phone VARCHAR(30) DEFAULT '',
    cover_letter TEXT,
    resume_path VARCHAR(500) DEFAULT '',
    status ENUM('new','reviewed','shortlisted','rejected') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_job (job_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS job_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Job board tables created successfully.\n";
