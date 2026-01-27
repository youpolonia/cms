<?php
/**
 * Security Auditor for CMS
 * Performs real-time security checks on the system configuration
 */
class SecurityAuditor {

    private static function getCmsRoot(): string {
        return defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__);
    }

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
        $root = self::getCmsRoot();
        $configFile = $root . '/config.php';

        // Default values
        $minLength = 8;
        $complexity = 2;
        $sessionTimeout = 7200;

        // Try to read from config
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);

            // Check for password length setting
            if (preg_match("/PASSWORD_MIN_LENGTH['\"]?\s*,\s*(\d+)/i", $content, $m)) {
                $minLength = (int)$m[1];
            }

            // Check for session timeout
            if (preg_match("/SESSION_LIFETIME['\"]?\s*,\s*(\d+)/i", $content, $m)) {
                $sessionTimeout = (int)$m[1];
            }
        }

        // Check PHP session settings
        $phpSessionTimeout = (int)ini_get('session.gc_maxlifetime');
        if ($phpSessionTimeout > 0 && $phpSessionTimeout < $sessionTimeout) {
            $sessionTimeout = $phpSessionTimeout;
        }

        return [
            'min_length' => $minLength >= 12,
            'complexity' => $complexity >= 3,
            'session_timeout' => $sessionTimeout <= 3600
        ];
    }

    /**
     * Check file upload security
     */
    private static function checkFileUploads(): array {
        // Check PHP upload settings
        $maxSize = self::parseSize(ini_get('upload_max_filesize'));
        $maxSizeMb = $maxSize / (1024 * 1024);

        // Check if ClamAV or similar is available
        $hasMalwareScan = false;
        $root = self::getCmsRoot();

        // Check for malware scanning configuration
        if (file_exists($root . '/config/uploads.php')) {
            $content = file_get_contents($root . '/config/uploads.php');
            $hasMalwareScan = strpos($content, 'malware_scan') !== false
                           && strpos($content, 'true') !== false;
        }

        return [
            'max_size' => $maxSizeMb <= 5,
            'malware_scan' => $hasMalwareScan
        ];
    }

    /**
     * Check security headers configuration
     */
    private static function checkSecurityHeaders(): array {
        $root = self::getCmsRoot();
        $headerFile = $root . '/core/security_headers.php';

        $hasXss = false;
        $hasCsp = false;
        $hasHsts = false;

        if (file_exists($headerFile)) {
            $content = file_get_contents($headerFile);
            $hasXss = stripos($content, 'X-XSS-Protection') !== false;
            $hasCsp = stripos($content, 'Content-Security-Policy') !== false;
            $hasHsts = stripos($content, 'Strict-Transport-Security') !== false;
        }

        // Also check .htaccess
        $htaccessFile = $root . '/.htaccess';
        if (file_exists($htaccessFile)) {
            $content = file_get_contents($htaccessFile);
            if (!$hasXss) $hasXss = stripos($content, 'X-XSS-Protection') !== false;
            if (!$hasCsp) $hasCsp = stripos($content, 'Content-Security-Policy') !== false;
            if (!$hasHsts) $hasHsts = stripos($content, 'Strict-Transport-Security') !== false;
        }

        return [
            'xss_protection' => $hasXss,
            'csp' => $hasCsp,
            'hsts' => $hasHsts
        ];
    }

    /**
     * Check database security settings
     */
    private static function checkDatabaseSecurity(): array {
        $root = self::getCmsRoot();

        // Check if using PDO with prepared statements (check Database class)
        $usesParamQueries = false;
        $dbFile = $root . '/core/database.php';
        if (file_exists($dbFile)) {
            $content = file_get_contents($dbFile);
            $usesParamQueries = stripos($content, 'prepare') !== false
                             && stripos($content, 'PDO') !== false;
        }

        // Check error reporting in production
        $errorReportingOff = true;
        if (defined('DEV_MODE') && DEV_MODE === true) {
            $errorReportingOff = false; // Expected in dev mode
        } else {
            // In production, errors should be hidden
            $displayErrors = ini_get('display_errors');
            $errorReportingOff = !$displayErrors || $displayErrors === '0' || $displayErrors === 'Off';
        }

        return [
            'parameterized_queries' => $usesParamQueries,
            'error_reporting' => $errorReportingOff
        ];
    }

    /**
     * Parse PHP size string (e.g., "2M" to bytes)
     */
    private static function parseSize(string $size): int {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1] ?? '');
        $value = (int)$size;

        switch ($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }

        return $value;
    }
}
