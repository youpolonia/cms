<?php
/**
 * Jessie Booking — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS booking_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    duration_minutes INT DEFAULT 60,
    buffer_minutes INT DEFAULT 15,
    price DECIMAL(10,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'USD',
    max_bookings_per_slot INT DEFAULT 1,
    category VARCHAR(100),
    image VARCHAR(500),
    color VARCHAR(20) DEFAULT '#6366f1',
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS booking_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    avatar VARCHAR(500),
    bio TEXT,
    services JSON,
    schedule JSON,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS booking_appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    staff_id INT,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    customer_phone VARCHAR(50),
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed','no_show') DEFAULT 'pending',
    notes TEXT,
    price_paid DECIMAL(10,2) DEFAULT 0,
    payment_status ENUM('none','pending','paid','refunded') DEFAULT 'none',
    reminder_sent TINYINT(1) DEFAULT 0,
    source ENUM('admin','widget','api') DEFAULT 'widget',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_service (service_id),
    INDEX idx_staff (staff_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS booking_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Default settings
$defaults = [
    'business_name' => 'My Business',
    'timezone' => 'UTC',
    'business_hours' => json_encode([
        'mon' => ['09:00','17:00'], 'tue' => ['09:00','17:00'], 'wed' => ['09:00','17:00'],
        'thu' => ['09:00','17:00'], 'fri' => ['09:00','17:00'], 'sat' => [], 'sun' => [],
    ]),
    'slot_interval' => '30',
    'min_advance_hours' => '2',
    'max_advance_days' => '60',
    'auto_confirm' => '0',
    'notification_email' => '',
    'reminder_hours' => '24',
    'booking_enabled' => '1',
];

foreach ($defaults as $k => $v) {
    $pdo->prepare("INSERT IGNORE INTO booking_settings (`key`, `value`) VALUES (?, ?)")->execute([$k, $v]);
}

echo "Booking tables created successfully.\n";
