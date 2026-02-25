<?php
/**
 * Jessie Directory — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS directory_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT UNSIGNED DEFAULT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT '',
    color VARCHAR(7) DEFAULT '#6366f1',
    sort_order INT DEFAULT 0,
    listing_count INT UNSIGNED DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS directory_listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category_id INT UNSIGNED DEFAULT NULL,
    description TEXT,
    short_description VARCHAR(500) DEFAULT '',
    owner_email VARCHAR(255) DEFAULT '',
    owner_name VARCHAR(100) DEFAULT '',
    phone VARCHAR(30) DEFAULT '',
    website VARCHAR(255) DEFAULT '',
    address VARCHAR(255) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    state VARCHAR(100) DEFAULT '',
    zip VARCHAR(20) DEFAULT '',
    country VARCHAR(50) DEFAULT '',
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    images JSON DEFAULT NULL,
    hours JSON DEFAULT NULL,
    social_links JSON DEFAULT NULL,
    tags VARCHAR(500) DEFAULT '',
    price_range ENUM('$','$$','$$$','$$$$','') DEFAULT '',
    is_featured TINYINT(1) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    is_claimed TINYINT(1) DEFAULT 0,
    claimed_by INT UNSIGNED DEFAULT NULL,
    avg_rating DECIMAL(3,2) DEFAULT 0,
    review_count INT UNSIGNED DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    status ENUM('active','pending','rejected','expired') DEFAULT 'pending',
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_category (category_id),
    KEY idx_status (status),
    KEY idx_city (city),
    KEY idx_featured (is_featured),
    FULLTEXT idx_search (title, description, tags, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS directory_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    reviewer_email VARCHAR(255) DEFAULT '',
    rating TINYINT UNSIGNED NOT NULL CHECK(rating BETWEEN 1 AND 5),
    title VARCHAR(200) DEFAULT '',
    content TEXT,
    is_verified TINYINT(1) DEFAULT 0,
    status ENUM('approved','pending','rejected') DEFAULT 'pending',
    helpful_count INT UNSIGNED DEFAULT 0,
    owner_reply TEXT DEFAULT NULL,
    owner_reply_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_listing (listing_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS directory_claims (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    proof TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_listing (listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Directory tables created successfully.\n";
