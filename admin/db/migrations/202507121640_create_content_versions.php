<?php
/**
 * Content Versioning Migration
 * Creates the content_versions table for tracking content revisions
 */

require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

// Database connection setup (centralized)
require_once __DIR__ . '/../../../core/database.php';
$db = \core\Database::connection();

try {
    $sql = "CREATE TABLE IF NOT EXISTS content_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_id INT NOT NULL,
        version_number INT NOT NULL,
        author_id INT NOT NULL,
        data_json LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
        INDEX (content_id),
        INDEX (version_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $db->exec($sql);
    
    // Log successful migration
    error_log("Content versions table created successfully");
    
} catch (PDOException $e) {
    error_log('Database error');
    http_response_code(500);
    exit;
}
