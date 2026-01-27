<?php
/**
 * Migration: Create security tables
 * Creates security_logs, login_attempts, blocked_ips tables
 */
require_once __DIR__ . '/abstractmigration.php';

class Migration_0001_create_security_tables extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // Create security_logs table for audit logging
        $db->exec("
            CREATE TABLE IF NOT EXISTS security_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
                user_id INT UNSIGNED NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent VARCHAR(500) NULL,
                details TEXT NULL,
                metadata JSON NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_severity (severity),
                INDEX idx_user_id (user_id),
                INDEX idx_ip_address (ip_address),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create login_attempts table for brute-force protection
        $db->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(190) NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent VARCHAR(500) NULL,
                success TINYINT(1) DEFAULT 0,
                failure_reason VARCHAR(100) NULL,
                attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_address (ip_address),
                INDEX idx_username (username),
                INDEX idx_attempted_at (attempted_at),
                INDEX idx_success (success)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create blocked_ips table for IP blocking
        $db->exec("
            CREATE TABLE IF NOT EXISTS blocked_ips (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                reason VARCHAR(255) NULL,
                blocked_by INT UNSIGNED NULL,
                blocked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NULL,
                is_permanent TINYINT(1) DEFAULT 0,
                UNIQUE KEY uk_ip_address (ip_address),
                INDEX idx_expires_at (expires_at),
                INDEX idx_is_permanent (is_permanent)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create security_policies table for policy management
        $db->exec("
            CREATE TABLE IF NOT EXISTS security_policies (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT NULL,
                settings JSON NOT NULL,
                parent_id INT UNSIGNED NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_name (name),
                INDEX idx_parent_id (parent_id),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create security_settings table for global security settings
        $db->exec("
            CREATE TABLE IF NOT EXISTS security_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT NOT NULL,
                description VARCHAR(255) NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default security settings
        $defaults = [
            ['max_login_attempts', '5', 'Maximum failed login attempts before lockout'],
            ['lockout_duration', '900', 'Lockout duration in seconds (default 15 min)'],
            ['session_timeout', '1800', 'Session timeout in seconds (default 30 min)'],
            ['password_min_length', '8', 'Minimum password length'],
            ['require_special_char', '1', 'Require special character in password'],
            ['require_uppercase', '1', 'Require uppercase letter in password'],
            ['require_number', '1', 'Require number in password'],
            ['two_factor_enabled', '0', 'Enable two-factor authentication'],
            ['ip_whitelist_enabled', '0', 'Enable IP whitelist for admin'],
            ['csrf_token_lifetime', '3600', 'CSRF token lifetime in seconds']
        ];

        $stmt = $db->prepare("
            INSERT IGNORE INTO security_settings (setting_key, setting_value, description)
            VALUES (?, ?, ?)
        ");

        foreach ($defaults as $setting) {
            $stmt->execute($setting);
        }

        return true;
    }
}
