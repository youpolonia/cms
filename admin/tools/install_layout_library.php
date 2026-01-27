<?php
/**
 * Install Layout Library Table
 * DEV_MODE only - run once to create tb_layout_library table
 */

require_once dirname(__DIR__, 2) . '/config.php';

// DEV_MODE check
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('DEV_MODE required');
}

require_once CMS_ROOT . '/core/Database.php';

$db = \core\Database::connection();

$sql = "
CREATE TABLE IF NOT EXISTS tb_layout_library (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    category ENUM('home','about','services','contact','gallery','blog','landing','full-site','other') NOT NULL DEFAULT 'other',
    industry VARCHAR(100) DEFAULT NULL COMMENT 'corporate, restaurant, healthcare, construction, etc.',
    style VARCHAR(50) DEFAULT 'modern' COMMENT 'modern, minimal, bold, elegant, classic',
    page_count TINYINT DEFAULT 1 COMMENT 'Number of pages in layout pack',
    thumbnail VARCHAR(500) DEFAULT NULL COMMENT 'Screenshot/preview image path',
    content_json LONGTEXT NOT NULL COMMENT 'Layout structure JSON',
    is_premium TINYINT(1) DEFAULT 0,
    is_ai_generated TINYINT(1) DEFAULT 0,
    ai_prompt TEXT DEFAULT NULL COMMENT 'Original prompt used to generate',
    downloads INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_industry (industry),
    INDEX idx_style (style),
    INDEX idx_downloads (downloads DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $db->exec($sql);
    echo "<h2 style='color:green'>&#10004; Table tb_layout_library created successfully!</h2>";

    $stmt = $db->query("DESCRIBE tb_layout_library");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Table Structure:</h3><pre>";
    print_r($columns);
    echo "</pre>";

    $stmt = $db->query("SELECT COUNT(*) as cnt FROM tb_layout_library");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Current rows: " . $count['cnt'] . "</p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>&#10006; Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
