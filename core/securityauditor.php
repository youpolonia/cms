<?php
/**
 * Security Auditor for CMS
 * Implements automated security checks based on security-standards.md
 */
class SecurityAuditor {
    /**
     * Run all security checks
     * @return array Audit results
     */
    public static function runFullAudit(): array {
        return [
            'password_policy' => self::checkPasswordPolicy(),
            'file_uploads' => self::checkFileUploads(),
            'security_headers' => self::checkSecurityHeaders(),
            'database_security' => self::checkDatabaseSecurity()
        ];
    }

    /**
     * Check password policy compliance
     */
    private static function checkPasswordPolicy(): array {
        $config = parse_ini_file('../config/security-standards.md', true);
        return [
            'min_length' => $config['general']['min_password_length'] >= 12,
            'complexity' => $config['general']['password_complexity'] >= 3,
            'session_timeout' => $config['general']['session_timeout'] <= 3600
        ];
    }

    /**
     * Check file upload security
     */
    private static function checkFileUploads(): array {
        $config = parse_ini_file('../config/security-standards.md', true);
        return [
            'max_size' => $config['file_uploads']['max_size'] <= 5,
            'malware_scan' => $config['file_uploads']['scan_for_malware'] === true
        ];
    }

    /**
     * Check security headers
     */
    private static function checkSecurityHeaders(): array {
        $config = parse_ini_file('../config/security-standards.md', true);
        return [
            'xss_protection' => !empty($config['headers']['xss_protection']),
            'csp' => str_contains($config['headers']['content_security_policy'], "'self'"),
            'hsts' => str_contains($config['headers']['strict_transport_security'], 'max-age=')
        ];
    }

    /**
     * Check database security
     */
    private static function checkDatabaseSecurity(): array {
        $config = parse_ini_file('../config/security-standards.md', true);
        return [
            'parameterized_queries' => $config['database']['parameterized_queries'] === true,
            'error_reporting' => $config['database']['error_reporting'] === false
        ];
    }
}
