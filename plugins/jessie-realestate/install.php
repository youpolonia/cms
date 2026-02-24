<?php
/**
 * Jessie Real Estate — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS re_agents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    email VARCHAR(255) DEFAULT '',
    phone VARCHAR(30) DEFAULT '',
    photo VARCHAR(255) DEFAULT '',
    bio TEXT,
    license_number VARCHAR(100) DEFAULT '',
    specialties VARCHAR(500) DEFAULT '',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS re_properties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(250) NOT NULL,
    slug VARCHAR(250) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500) DEFAULT '',
    property_type ENUM('house','apartment','condo','townhouse','land','commercial','other') DEFAULT 'house',
    listing_type ENUM('sale','rent','lease') DEFAULT 'sale',
    price DECIMAL(14,2) NOT NULL DEFAULT 0,
    price_period ENUM('total','monthly','weekly','yearly') DEFAULT 'total',
    currency VARCHAR(5) DEFAULT 'GBP',
    bedrooms TINYINT UNSIGNED DEFAULT NULL,
    bathrooms TINYINT UNSIGNED DEFAULT NULL,
    area_sqft INT UNSIGNED DEFAULT NULL,
    lot_size INT UNSIGNED DEFAULT NULL,
    year_built SMALLINT UNSIGNED DEFAULT NULL,
    address VARCHAR(255) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    state VARCHAR(100) DEFAULT '',
    zip VARCHAR(20) DEFAULT '',
    country VARCHAR(50) DEFAULT '',
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    images JSON DEFAULT NULL,
    floor_plan VARCHAR(255) DEFAULT '',
    virtual_tour VARCHAR(255) DEFAULT '',
    features JSON DEFAULT NULL,
    agent_id INT UNSIGNED DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    status ENUM('active','pending','sold','rented','draft') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_agent (agent_id),
    KEY idx_status (status),
    KEY idx_city (city),
    KEY idx_type (property_type, listing_type),
    KEY idx_featured (is_featured),
    KEY idx_price (price),
    FULLTEXT idx_search (title, description, address, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS re_inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT '',
    message TEXT,
    status ENUM('new','read','replied','archived') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_property (property_id),
    KEY idx_status (status),
    KEY idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS re_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Default settings
$defaults = [
    ['company_name', 'Jessie Real Estate'],
    ['currency', 'GBP'],
    ['currency_symbol', '£'],
    ['default_listing_type', 'sale'],
    ['properties_per_page', '12'],
    ['enable_inquiries', '1'],
    ['contact_email', ''],
    ['contact_phone', ''],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO re_settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($defaults as $d) $stmt->execute($d);

echo "Real Estate tables created successfully.\n";
