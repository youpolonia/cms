<?php
/**
 * Jessie Restaurant — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT '',
    icon VARCHAR(50) DEFAULT '',
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500) DEFAULT '',
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255) DEFAULT '',
    gallery JSON DEFAULT NULL,
    options JSON DEFAULT NULL,
    extras JSON DEFAULT NULL,
    allergens VARCHAR(500) DEFAULT '',
    calories INT DEFAULT NULL,
    prep_time_min INT DEFAULT NULL,
    is_vegetarian TINYINT(1) DEFAULT 0,
    is_vegan TINYINT(1) DEFAULT 0,
    is_gluten_free TINYINT(1) DEFAULT 0,
    is_spicy TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    status ENUM('active','hidden','soldout') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_category (category_id),
    KEY idx_status (status),
    FULLTEXT idx_search (name, description, allergens)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(255) DEFAULT '',
    customer_phone VARCHAR(30) NOT NULL,
    order_type ENUM('delivery','pickup','dine-in') DEFAULT 'delivery',
    delivery_address TEXT DEFAULT NULL,
    delivery_notes TEXT DEFAULT NULL,
    items_json JSON NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    tip DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash','card','online') DEFAULT 'cash',
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    status ENUM('new','confirmed','preparing','ready','delivering','completed','cancelled') DEFAULT 'new',
    estimated_time INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Default settings
$defaults = [
    ['restaurant_name', 'My Restaurant'],
    ['currency', 'GBP'],
    ['currency_symbol', '£'],
    ['min_order_amount', '10.00'],
    ['delivery_fee', '3.50'],
    ['tax_rate', '20'],
    ['order_types', 'delivery,pickup'],
    ['opening_hours', '{"monday":"11:00-22:00","tuesday":"11:00-22:00","wednesday":"11:00-22:00","thursday":"11:00-22:00","friday":"11:00-23:00","saturday":"11:00-23:00","sunday":"12:00-21:00"}'],
    ['estimated_delivery_time', '30-45'],
    ['estimated_pickup_time', '15-20'],
    ['accept_orders', '1'],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO restaurant_settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($defaults as $d) $stmt->execute($d);

echo "Restaurant tables created successfully.\n";
