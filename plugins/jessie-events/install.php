<?php
/**
 * Jessie Events — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500) DEFAULT '',
    venue_name VARCHAR(200) DEFAULT '',
    venue_address VARCHAR(500) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    country VARCHAR(100) DEFAULT '',
    start_date DATETIME NOT NULL,
    end_date DATETIME DEFAULT NULL,
    image VARCHAR(255) DEFAULT '',
    category VARCHAR(100) DEFAULT '',
    organizer_name VARCHAR(200) DEFAULT '',
    organizer_email VARCHAR(255) DEFAULT '',
    max_capacity INT UNSIGNED DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    status ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_start_date (start_date),
    KEY idx_city (city),
    KEY idx_category (category),
    FULLTEXT idx_search (title, description, venue_name, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS event_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    description VARCHAR(500) DEFAULT '',
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'GBP',
    quantity_total INT UNSIGNED NOT NULL DEFAULT 100,
    quantity_sold INT UNSIGNED DEFAULT 0,
    max_per_order INT UNSIGNED DEFAULT 10,
    sale_start DATETIME DEFAULT NULL,
    sale_end DATETIME DEFAULT NULL,
    status ENUM('active','soldout','hidden') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_event (event_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS event_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    ticket_id INT UNSIGNED NOT NULL,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    buyer_name VARCHAR(100) NOT NULL,
    buyer_email VARCHAR(255) DEFAULT '',
    buyer_phone VARCHAR(30) DEFAULT '',
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    qr_code VARCHAR(100) DEFAULT '',
    checked_in TINYINT(1) DEFAULT 0,
    checked_in_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_event (event_id),
    KEY idx_ticket (ticket_id),
    KEY idx_order_number (order_number),
    KEY idx_payment (payment_status),
    KEY idx_qr (qr_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS event_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Default settings
$defaults = [
    ['currency', 'GBP'],
    ['currency_symbol', '£'],
    ['organizer_name', ''],
    ['organizer_email', ''],
    ['default_max_capacity', '500'],
    ['require_phone', '0'],
    ['checkin_salt', bin2hex(random_bytes(8))],
    ['events_per_page', '12'],
    ['allow_free_events', '1'],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO event_settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($defaults as $d) $stmt->execute($d);

echo "Events tables created successfully.\n";
