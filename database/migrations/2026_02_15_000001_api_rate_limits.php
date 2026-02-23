<?php
/**
 * Migration: Add API rate limiting support
 * 
 * Creates api_rate_limits table for tracking API request rates.
 * Also ensures login_attempts indexes are optimal.
 */

return new class {
    public string $name = '2026_02_15_000001_api_rate_limits';

    public function up(\PDO $pdo): void
    {
        // API rate limiting table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_rate_limits (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                endpoint VARCHAR(100) NOT NULL DEFAULT '*',
                request_count INT UNSIGNED NOT NULL DEFAULT 1,
                window_start DATETIME NOT NULL,
                INDEX idx_ip_window (ip_address, window_start),
                INDEX idx_cleanup (window_start)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Ensure login_attempts has good indexes for rate limiting queries
        try {
            $pdo->exec("ALTER TABLE login_attempts ADD INDEX idx_ip_time (ip_address, attempted_at)");
        } catch (\Throwable $e) {
            // Index might already exist
        }
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS api_rate_limits");
        try {
            $pdo->exec("ALTER TABLE login_attempts DROP INDEX idx_ip_time");
        } catch (\Throwable $e) {}
    }
};
