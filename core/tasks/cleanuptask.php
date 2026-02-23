<?php
namespace core\tasks;

/**
 * Database Cleanup Task
 * 
 * Removes old data that accumulates:
 * - login_attempts older than 30 days
 * - api_rate_limits older than 1 hour
 * - stale sessions
 * 
 * Safe to run via cron or scheduler.
 */
class CleanupTask
{
    public static function run(): bool
    {
        try {
            $pdo = \core\Database::connection();
            $cleaned = 0;

            // Clean old login attempts (keep 30 days)
            $stmt = $pdo->exec(
                "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $cleaned += (int)$stmt;

            // Clean old API rate limit records (keep 1 hour)
            try {
                $stmt = $pdo->exec(
                    "DELETE FROM api_rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
                );
                $cleaned += (int)$stmt;
            } catch (\Throwable $e) {
                // Table might not exist yet
            }

            // Clean old analytics events (keep 90 days)
            try {
                $stmt = $pdo->exec(
                    "DELETE FROM analytics_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
                );
                $cleaned += (int)$stmt;
            } catch (\Throwable $e) {}

            // Clean old SEO crawl logs (keep 60 days)
            try {
                $stmt = $pdo->exec(
                    "DELETE FROM seo_crawl_log WHERE crawled_at < DATE_SUB(NOW(), INTERVAL 60 DAY)"
                );
                $cleaned += (int)$stmt;
            } catch (\Throwable $e) {}

            error_log("[CleanupTask] Removed {$cleaned} old records");
            return true;
        } catch (\Throwable $e) {
            error_log("[CleanupTask] Error: " . $e->getMessage());
            return false;
        }
    }
}
