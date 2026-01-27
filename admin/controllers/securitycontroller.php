<?php
/**
 * Security Controller
 * Handles security dashboard, logs, settings, and IP management
 *
 * MVC Pattern: Pure PHP, no framework dependencies
 */

require_once __DIR__ . '/../models/securitymodel.php';

class SecurityController
{
    private SecurityModel $model;
    private ?int $userId;
    private string $clientIp;

    public function __construct()
    {
        $this->model = new SecurityModel();
        $this->userId = $_SESSION['user_id'] ?? null;
        $this->clientIp = $this->getClientIp();
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Dashboard action - main security overview
     */
    public function dashboard(): array
    {
        $stats = $this->model->getDashboardStats();
        $logStats = $this->model->getLogStats(7);

        return [
            'stats' => $stats,
            'log_stats' => $logStats,
            'settings' => $this->model->getAllSettings(),
            'blocked_ips_count' => count($this->model->getBlockedIps())
        ];
    }

    /**
     * Logs action - view security logs
     */
    public function logs(): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($_GET['per_page'] ?? 50)));

        $filters = [];
        if (!empty($_GET['event_type'])) {
            $filters['event_type'] = $_GET['event_type'];
        }
        if (!empty($_GET['severity'])) {
            $filters['severity'] = $_GET['severity'];
        }
        if (!empty($_GET['ip_address'])) {
            $filters['ip_address'] = $_GET['ip_address'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $result = $this->model->getLogs($filters, $page, $perPage);
        $eventTypes = $this->model->getEventTypes();

        return [
            'logs' => $result['logs'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total_pages' => $result['total_pages']
            ],
            'filters' => $filters,
            'event_types' => $eventTypes,
            'severities' => ['low', 'medium', 'high', 'critical']
        ];
    }

    /**
     * Login attempts action
     */
    public function loginAttempts(): array
    {
        $filters = [];
        if (isset($_GET['success'])) {
            $filters['success'] = $_GET['success'] === '1';
        }
        if (!empty($_GET['ip_address'])) {
            $filters['ip_address'] = $_GET['ip_address'];
        }
        if (!empty($_GET['username'])) {
            $filters['username'] = $_GET['username'];
        }

        $limit = min(500, max(50, (int) ($_GET['limit'] ?? 100)));

        return [
            'attempts' => $this->model->getLoginAttempts($filters, $limit),
            'filters' => $filters
        ];
    }

    /**
     * Blocked IPs action
     */
    public function blockedIps(): array
    {
        $includeExpired = isset($_GET['include_expired']);
        return [
            'blocked_ips' => $this->model->getBlockedIps($includeExpired),
            'include_expired' => $includeExpired
        ];
    }

    /**
     * Block IP action (POST)
     */
    public function blockIp(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }

        $ipAddress = trim($_POST['ip_address'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $permanent = isset($_POST['permanent']);
        $duration = (int) ($_POST['duration'] ?? 86400); // Default 24 hours

        if (empty($ipAddress)) {
            return ['success' => false, 'error' => 'IP address is required'];
        }

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return ['success' => false, 'error' => 'Invalid IP address format'];
        }

        // Prevent blocking own IP
        if ($ipAddress === $this->clientIp) {
            return ['success' => false, 'error' => 'Cannot block your own IP address'];
        }

        $success = $this->model->blockIp(
            $ipAddress,
            $reason ?: null,
            $this->userId,
            $permanent ? null : $duration,
            $permanent
        );

        if ($success) {
            $this->model->logEvent(
                'ip_blocked',
                'medium',
                $this->clientIp,
                $this->userId,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                "Blocked IP: {$ipAddress}. Reason: {$reason}"
            );
        }

        return [
            'success' => $success,
            'message' => $success ? 'IP address blocked successfully' : 'Failed to block IP address'
        ];
    }

    /**
     * Unblock IP action (POST)
     */
    public function unblockIp(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }

        $ipAddress = trim($_POST['ip_address'] ?? '');

        if (empty($ipAddress)) {
            return ['success' => false, 'error' => 'IP address is required'];
        }

        $success = $this->model->unblockIp($ipAddress);

        if ($success) {
            $this->model->logEvent(
                'ip_unblocked',
                'low',
                $this->clientIp,
                $this->userId,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                "Unblocked IP: {$ipAddress}"
            );
        }

        return [
            'success' => $success,
            'message' => $success ? 'IP address unblocked successfully' : 'Failed to unblock IP address'
        ];
    }

    /**
     * Settings action
     */
    public function settings(): array
    {
        return [
            'settings' => $this->model->getAllSettings(),
            'policies' => $this->model->getAllPolicies()
        ];
    }

    /**
     * Save settings action (POST)
     */
    public function saveSettings(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }

        $settings = $_POST['settings'] ?? [];
        $errors = [];

        foreach ($settings as $key => $value) {
            // Validate specific settings
            if (in_array($key, ['max_login_attempts', 'lockout_duration', 'session_timeout', 'password_min_length', 'csrf_token_lifetime'])) {
                if (!is_numeric($value) || (int) $value < 0) {
                    $errors[] = "{$key} must be a positive number";
                    continue;
                }
            }

            if (!$this->model->setSetting($key, (string) $value)) {
                $errors[] = "Failed to save {$key}";
            }
        }

        if (empty($errors)) {
            $this->model->logEvent(
                'settings_changed',
                'medium',
                $this->clientIp,
                $this->userId,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                'Security settings updated',
                ['changed_keys' => array_keys($settings)]
            );
        }

        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Settings saved successfully' : 'Some settings failed to save'
        ];
    }

    /**
     * Log a security event (helper for other modules)
     */
    public function logSecurityEvent(
        string $eventType,
        string $severity = 'low',
        ?string $details = null,
        ?array $metadata = null
    ): bool {
        return $this->model->logEvent(
            $eventType,
            $severity,
            $this->clientIp,
            $this->userId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $details,
            $metadata
        );
    }

    /**
     * Check if current IP is blocked
     */
    public function isCurrentIpBlocked(): bool
    {
        return $this->model->isIpBlocked($this->clientIp);
    }

    /**
     * Check if current IP is locked out (too many failed logins)
     */
    public function isLockedOut(): bool
    {
        return $this->model->isLockedOut($this->clientIp);
    }

    /**
     * Record login attempt (for auth module integration)
     */
    public function recordLoginAttempt(
        bool $success,
        ?string $username = null,
        ?string $failureReason = null
    ): bool {
        return $this->model->recordLoginAttempt(
            $this->clientIp,
            $username,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $success,
            $failureReason
        );
    }

    /**
     * Run security audit
     */
    public function runAudit(): array
    {
        $cmsRoot = dirname(dirname(__DIR__));
        $results = [];

        // Check DEV_MODE
        $devModePass = defined('DEV_MODE') && DEV_MODE === true;
        $results[] = [
            'check' => 'DEV_MODE Status',
            'status' => $devModePass ? 'pass' : 'fail',
            'severity' => $devModePass ? 'low' : 'info',
            'message' => $devModePass ? 'DEV_MODE is enabled' : 'DEV_MODE is disabled (production mode)'
        ];

        // Check writable directories
        $writableDirs = [
            '/logs/' => $cmsRoot . '/logs',
            '/uploads/tmp/' => $cmsRoot . '/uploads/tmp',
            '/extensions/' => $cmsRoot . '/extensions'
        ];

        foreach ($writableDirs as $name => $path) {
            $writable = is_writable($path);
            $results[] = [
                'check' => "Writable Directory: {$name}",
                'status' => $writable ? 'pass' : 'warn',
                'severity' => 'low',
                'message' => $writable ? 'Directory is writable' : 'Directory is not writable'
            ];
        }

        // Check admin .htaccess
        $htaccessPath = $cmsRoot . '/admin/.htaccess';
        $htaccessExists = file_exists($htaccessPath);
        $htaccessHasDeny = false;

        if ($htaccessExists) {
            $content = @file_get_contents($htaccessPath);
            $htaccessHasDeny = $content && stripos($content, 'deny from all') !== false;
        }

        $results[] = [
            'check' => 'Admin .htaccess Protection',
            'status' => ($htaccessExists && $htaccessHasDeny) ? 'pass' : 'warn',
            'severity' => 'medium',
            'message' => $htaccessExists ?
                ($htaccessHasDeny ? '.htaccess with deny block exists' : '.htaccess exists but missing deny block')
                : '.htaccess file missing'
        ];

        // Check blocked IPs
        $blockedCount = count($this->model->getBlockedIps());
        $results[] = [
            'check' => 'Blocked IPs',
            'status' => 'info',
            'severity' => 'low',
            'message' => "{$blockedCount} IP(s) currently blocked"
        ];

        // Check recent failed logins
        $failedLogins = $this->model->getLoginAttempts(['success' => false], 100);
        $recentFailed = count(array_filter($failedLogins, function ($a) {
            return strtotime($a['attempted_at']) > time() - 3600;
        }));

        $results[] = [
            'check' => 'Failed Logins (Last Hour)',
            'status' => $recentFailed > 10 ? 'warn' : 'pass',
            'severity' => $recentFailed > 10 ? 'medium' : 'low',
            'message' => "{$recentFailed} failed login attempt(s) in the last hour"
        ];

        // Log audit run
        $this->model->logEvent(
            'security_audit',
            'low',
            $this->clientIp,
            $this->userId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            'Security audit performed'
        );

        return $results;
    }

    /**
     * Cleanup old data
     */
    public function cleanup(): array
    {
        $loginAttemptsDeleted = $this->model->cleanupLoginAttempts(30);
        $blocksDeleted = $this->model->cleanupExpiredBlocks();

        $this->model->logEvent(
            'security_cleanup',
            'low',
            $this->clientIp,
            $this->userId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            "Cleanup: {$loginAttemptsDeleted} old login attempts, {$blocksDeleted} expired blocks removed"
        );

        return [
            'success' => true,
            'login_attempts_deleted' => $loginAttemptsDeleted,
            'blocks_deleted' => $blocksDeleted
        ];
    }

    /**
     * Export security logs
     */
    public function exportLogs(): array
    {
        $filters = [];
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        // Get all logs matching filters (up to 10000)
        $result = $this->model->getLogs($filters, 1, 10000);

        $this->model->logEvent(
            'logs_exported',
            'low',
            $this->clientIp,
            $this->userId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            "Exported {$result['total']} security logs"
        );

        return $result['logs'];
    }
}
