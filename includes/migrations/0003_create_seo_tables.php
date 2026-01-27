<?php
/**
 * Migration: Create SEO tables
 * Creates seo_metadata table for page-level SEO settings
 * Creates seo_redirects table for URL redirect management
 */
require_once __DIR__ . '/abstractmigration.php';

class Migration_0003_create_seo_tables extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // Create seo_metadata table for per-page SEO settings
        $db->exec("
            CREATE TABLE IF NOT EXISTS seo_metadata (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                entity_type ENUM('page', 'article', 'category', 'custom') NOT NULL DEFAULT 'page',
                entity_id INT UNSIGNED NOT NULL,
                meta_title VARCHAR(255) NULL,
                meta_description TEXT NULL,
                meta_keywords VARCHAR(500) NULL,
                canonical_url VARCHAR(2048) NULL,
                robots_index ENUM('index', 'noindex') DEFAULT 'index',
                robots_follow ENUM('follow', 'nofollow') DEFAULT 'follow',
                og_title VARCHAR(255) NULL,
                og_description TEXT NULL,
                og_image VARCHAR(2048) NULL,
                og_type VARCHAR(50) DEFAULT 'website',
                twitter_card ENUM('summary', 'summary_large_image', 'app', 'player') DEFAULT 'summary_large_image',
                twitter_title VARCHAR(255) NULL,
                twitter_description TEXT NULL,
                twitter_image VARCHAR(2048) NULL,
                schema_type VARCHAR(100) NULL,
                schema_data JSON NULL,
                focus_keyword VARCHAR(100) NULL,
                seo_score TINYINT UNSIGNED NULL,
                readability_score TINYINT UNSIGNED NULL,
                last_analyzed_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_entity (entity_type, entity_id),
                INDEX idx_entity_type (entity_type),
                INDEX idx_robots_index (robots_index),
                INDEX idx_seo_score (seo_score),
                INDEX idx_focus_keyword (focus_keyword)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create seo_redirects table for 301/302 redirect management
        $db->exec("
            CREATE TABLE IF NOT EXISTS seo_redirects (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                source_url VARCHAR(2048) NOT NULL,
                target_url VARCHAR(2048) NOT NULL,
                redirect_type SMALLINT UNSIGNED DEFAULT 301,
                hit_count INT UNSIGNED DEFAULT 0,
                last_hit_at DATETIME NULL,
                is_active TINYINT(1) DEFAULT 1,
                notes TEXT NULL,
                created_by INT UNSIGNED NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_source_url (source_url(191)),
                INDEX idx_is_active (is_active),
                INDEX idx_redirect_type (redirect_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create seo_crawl_log table for sitemap crawl tracking
        $db->exec("
            CREATE TABLE IF NOT EXISTS seo_crawl_log (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                url VARCHAR(2048) NOT NULL,
                status_code SMALLINT UNSIGNED NULL,
                response_time_ms INT UNSIGNED NULL,
                crawler_type VARCHAR(50) NULL,
                crawled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_url (url(191)),
                INDEX idx_status_code (status_code),
                INDEX idx_crawled_at (crawled_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create seo_keywords table for keyword tracking and research
        $db->exec("
            CREATE TABLE IF NOT EXISTS seo_keywords (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                keyword VARCHAR(255) NOT NULL,
                search_volume INT UNSIGNED NULL,
                difficulty TINYINT UNSIGNED NULL,
                cpc DECIMAL(10,2) NULL,
                trend_data JSON NULL,
                last_updated_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_keyword (keyword),
                INDEX idx_search_volume (search_volume),
                INDEX idx_difficulty (difficulty)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default SEO settings into settings table if not exists
        // Note: settings table uses columns: `key`, `value`, `group_name`
        $stmt = $db->prepare("
            INSERT IGNORE INTO settings (`key`, `value`, `group_name`)
            VALUES (?, ?, 'seo')
        ");

        $defaults = [
            ['seo_sitemap_enabled', '1'],
            ['seo_sitemap_frequency', 'weekly'],
            ['seo_sitemap_priority', '0.5'],
            ['seo_robots_txt_custom', ''],
            ['seo_default_og_image', ''],
            ['seo_twitter_handle', ''],
            ['seo_google_verification', ''],
            ['seo_bing_verification', ''],
            ['seo_schema_org_enabled', '1'],
            ['seo_auto_canonical', '1'],
        ];

        foreach ($defaults as $setting) {
            $stmt->execute($setting);
        }

        return true;
    }
}
