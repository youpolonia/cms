<?php
/**
 * Jessie Portfolio — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT '',
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category_id INT UNSIGNED DEFAULT NULL,
    client_name VARCHAR(150) DEFAULT '',
    description TEXT,
    short_description VARCHAR(500) DEFAULT '',
    cover_image VARCHAR(255) DEFAULT NULL,
    images JSON DEFAULT NULL,
    technologies JSON DEFAULT NULL,
    project_url VARCHAR(255) DEFAULT '',
    completion_date DATE DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    status ENUM('published','draft') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_category (category_id),
    KEY idx_status (status),
    KEY idx_featured (is_featured),
    FULLTEXT idx_search (title, description, short_description, client_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED DEFAULT NULL,
    client_name VARCHAR(150) NOT NULL,
    client_title VARCHAR(150) DEFAULT '',
    client_company VARCHAR(150) DEFAULT '',
    client_photo VARCHAR(255) DEFAULT NULL,
    content TEXT,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5 CHECK(rating BETWEEN 1 AND 5),
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('published','pending') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_project (project_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Portfolio tables created successfully.\n";
