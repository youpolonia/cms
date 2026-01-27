<?php
/**
 * Analytics Tables Migration
 * Creates page_views, analytics_events, and analytics_daily_stats tables
 * Framework-free: pure SQL with PDO
 */

require_once __DIR__ . '/abstractmigration.php';

class CreateAnalyticsTables extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // Create page_views table for tracking individual page visits
        $db->exec("
            CREATE TABLE IF NOT EXISTS page_views (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_url VARCHAR(500) NOT NULL,
                page_title VARCHAR(255) DEFAULT NULL,
                referrer VARCHAR(500) DEFAULT NULL,
                user_agent VARCHAR(500) DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                session_id VARCHAR(128) DEFAULT NULL,
                user_id INT UNSIGNED DEFAULT NULL,
                tenant_id INT UNSIGNED DEFAULT NULL,
                device_type ENUM('desktop', 'mobile', 'tablet', 'bot', 'unknown') DEFAULT 'unknown',
                browser VARCHAR(50) DEFAULT NULL,
                os VARCHAR(50) DEFAULT NULL,
                country_code CHAR(2) DEFAULT NULL,
                duration_seconds INT UNSIGNED DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_page_views_url (page_url(100)),
                INDEX idx_page_views_created (created_at),
                INDEX idx_page_views_tenant (tenant_id),
                INDEX idx_page_views_session (session_id),
                INDEX idx_page_views_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create analytics_events table for custom event tracking
        $db->exec("
            CREATE TABLE IF NOT EXISTS analytics_events (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                event_name VARCHAR(100) NOT NULL,
                event_data JSON DEFAULT NULL,
                page_url VARCHAR(500) DEFAULT NULL,
                session_id VARCHAR(128) DEFAULT NULL,
                user_id INT UNSIGNED DEFAULT NULL,
                tenant_id INT UNSIGNED DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(500) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_events_type (event_type),
                INDEX idx_events_name (event_name),
                INDEX idx_events_created (created_at),
                INDEX idx_events_tenant (tenant_id),
                INDEX idx_events_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create analytics_daily_stats table for aggregated daily statistics
        $db->exec("
            CREATE TABLE IF NOT EXISTS analytics_daily_stats (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                stat_date DATE NOT NULL,
                tenant_id INT UNSIGNED DEFAULT NULL,
                total_views INT UNSIGNED DEFAULT 0,
                unique_visitors INT UNSIGNED DEFAULT 0,
                total_sessions INT UNSIGNED DEFAULT 0,
                avg_duration DECIMAL(10,2) DEFAULT 0,
                bounce_rate DECIMAL(5,2) DEFAULT 0,
                desktop_views INT UNSIGNED DEFAULT 0,
                mobile_views INT UNSIGNED DEFAULT 0,
                tablet_views INT UNSIGNED DEFAULT 0,
                top_pages JSON DEFAULT NULL,
                top_referrers JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_date_tenant (stat_date, tenant_id),
                INDEX idx_stats_date (stat_date),
                INDEX idx_stats_tenant (tenant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create analytics_content_stats for content-specific analytics
        $db->exec("
            CREATE TABLE IF NOT EXISTS analytics_content_stats (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                content_id INT UNSIGNED NOT NULL,
                content_type VARCHAR(50) DEFAULT 'page',
                tenant_id INT UNSIGNED DEFAULT NULL,
                total_views INT UNSIGNED DEFAULT 0,
                unique_views INT UNSIGNED DEFAULT 0,
                avg_duration DECIMAL(10,2) DEFAULT 0,
                last_viewed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_content_tenant (content_id, tenant_id),
                INDEX idx_content_stats_type (content_type),
                INDEX idx_content_stats_views (total_views DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        return true;
    }
}
