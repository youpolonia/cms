<?php
/**
 * User Roles Schema
 * Creates the user_roles table structure
 */

require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

try {
    $pdo = \core\Database::connection();
    
    $sql = "CREATE TABLE IF NOT EXISTS user_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role ENUM('admin', 'editor', 'viewer') NOT NULL,
        assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX (user_id)
    )";
    
    $pdo->exec($sql);
} catch (PDOException $e) {
    error_log('Database error');
    http_response_code(500);
    exit;
}
