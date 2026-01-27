<?php
/**
 * Security Model
 * Handles all security-related database operations
 *
 * Tables: security_logs, login_attempts, blocked_ips, security_policies, security_settings
 */

require_once __DIR__ . '/../../core/database.php';

class SecurityModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \core\Database::connection();
    }

    // ========== SECURITY LOGS ==========

    /**
     * Log a security event
     */
    public function logEvent(
        string $eventType,
        string $severity,
        string $ipAddress,
        ?int $userId = null,
        ?string $userAgent = null,
        ?string $details = null,
        ?array $metadata = null
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO security_logs
            (event_type, severity, user_id, ip_address, user_agent, details, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $eventType,
            $severity,
            $userId,
            $ipAddress,
            $userAgent,
            $details,
            $metadata ? json_encode($metadata) : null
        ]);
    }

    /**
     * Get security logs with filtering and pagination
     */
    public function getLogs(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['event_type'])) {
            $where[] = 'event_type = ?';
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['severity'])) {
            $where[] = 'severity = ?';
            $params[] = $filters['severity'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['ip_address'])) {
            $where[] = 'ip_address LIKE ?';
            $params[] = '%' . $filters['ip_address'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM security_logs WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get logs
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare("
            SELECT * FROM security_logs
            WHERE {$whereClause}
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get log statistics for dashboard
     */
    public function getLogStats(int $days = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT
                severity,
                COUNT(*) as count
            FROM security_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY severity
        ");
        $stmt->execute([$days]);
        $bySeverity = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt = $this->db->prepare("
            SELECT
                event_type,
                COUNT(*) as count
            FROM security_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY event_type
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$days]);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM security_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
        $total = (int) $stmt->fetchColumn();

        return [
            'by_severity' => $bySeverity,
            'by_type' => $byType,
            'total' => $total
        ];
    }

    /**
     * Get distinct event types for filter dropdown
     */
    public function getEventTypes(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT event_type FROM security_logs ORDER BY event_type");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ========== LOGIN ATTEMPTS ==========

    /**
     * Record a login attempt
     */
    public function recordLoginAttempt(
        string $ipAddress,
        ?string $username = null,
        ?string $userAgent = null,
        bool $success = false,
        ?string $failureReason = null
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts
            (username, ip_address, user_agent, success, failure_reason, attempted_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $username,
            $ipAddress,
            $userAgent,
            $success ? 1 : 0,
            $failureReason
        ]);
    }

    /**
     * Get failed login count for an IP in the last X minutes
     */
    public function getFailedLoginCount(string $ipAddress, int $minutes = 15): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE ip_address = ?
            AND success = 0
            AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$ipAddress, $minutes]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if IP is locked out
     */
    public function isLockedOut(string $ipAddress): bool
    {
        $maxAttempts = (int) $this->getSetting('max_login_attempts', 5);
        $lockoutDuration = (int) $this->getSetting('lockout_duration', 900);
        $minutes = ceil($lockoutDuration / 60);

        return $this->getFailedLoginCount($ipAddress, $minutes) >= $maxAttempts;
    }

    /**
     * Get login attempts with filtering
     */
    public function getLoginAttempts(array $filters = [], int $limit = 100): array
    {
        $where = ['1=1'];
        $params = [];

        if (isset($filters['success'])) {
            $where[] = 'success = ?';
            $params[] = $filters['success'] ? 1 : 0;
        }

        if (!empty($filters['ip_address'])) {
            $where[] = 'ip_address LIKE ?';
            $params[] = '%' . $filters['ip_address'] . '%';
        }

        if (!empty($filters['username'])) {
            $where[] = 'username LIKE ?';
            $params[] = '%' . $filters['username'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $params[] = $limit;

        $stmt = $this->db->prepare("
            SELECT * FROM login_attempts
            WHERE {$whereClause}
            ORDER BY attempted_at DESC
            LIMIT ?
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clean up old login attempts
     */
    public function cleanupLoginAttempts(int $daysOld = 30): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts
            WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$daysOld]);
        return $stmt->rowCount();
    }

    // ========== BLOCKED IPs ==========

    /**
     * Block an IP address
     */
    public function blockIp(
        string $ipAddress,
        ?string $reason = null,
        ?int $blockedBy = null,
        ?int $expiresInSeconds = null,
        bool $permanent = false
    ): bool {
        $expiresAt = null;
        if (!$permanent && $expiresInSeconds) {
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);
        }

        $stmt = $this->db->prepare("
            INSERT INTO blocked_ips
            (ip_address, reason, blocked_by, blocked_at, expires_at, is_permanent)
            VALUES (?, ?, ?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE
                reason = VALUES(reason),
                blocked_by = VALUES(blocked_by),
                blocked_at = NOW(),
                expires_at = VALUES(expires_at),
                is_permanent = VALUES(is_permanent)
        ");

        return $stmt->execute([
            $ipAddress,
            $reason,
            $blockedBy,
            $expiresAt,
            $permanent ? 1 : 0
        ]);
    }

    /**
     * Unblock an IP address
     */
    public function unblockIp(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blocked_ips WHERE ip_address = ?");
        return $stmt->execute([$ipAddress]);
    }

    /**
     * Check if an IP is blocked
     */
    public function isIpBlocked(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM blocked_ips
            WHERE ip_address = ?
            AND (is_permanent = 1 OR expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$ipAddress]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Get all blocked IPs
     */
    public function getBlockedIps(bool $includeExpired = false): array
    {
        if ($includeExpired) {
            $stmt = $this->db->query("
                SELECT * FROM blocked_ips ORDER BY blocked_at DESC
            ");
        } else {
            $stmt = $this->db->query("
                SELECT * FROM blocked_ips
                WHERE is_permanent = 1 OR expires_at IS NULL OR expires_at > NOW()
                ORDER BY blocked_at DESC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clean up expired IP blocks
     */
    public function cleanupExpiredBlocks(): int
    {
        $stmt = $this->db->query("
            DELETE FROM blocked_ips
            WHERE is_permanent = 0 AND expires_at IS NOT NULL AND expires_at <= NOW()
        ");
        return $stmt->rowCount();
    }

    // ========== SECURITY SETTINGS ==========

    /**
     * Get a security setting
     */
    public function getSetting(string $key, $default = null)
    {
        $stmt = $this->db->prepare("SELECT setting_value FROM security_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    /**
     * Set a security setting
     */
    public function setSetting(string $key, string $value, ?string $description = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO security_settings (setting_key, setting_value, description)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                description = COALESCE(VALUES(description), description)
        ");
        return $stmt->execute([$key, $value, $description]);
    }

    /**
     * Get all security settings
     */
    public function getAllSettings(): array
    {
        $stmt = $this->db->query("SELECT * FROM security_settings ORDER BY setting_key");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== SECURITY POLICIES ==========

    /**
     * Save a security policy
     */
    public function savePolicy(string $name, array $settings, ?string $description = null, ?int $parentId = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO security_policies (name, description, settings, parent_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                description = VALUES(description),
                settings = VALUES(settings),
                parent_id = VALUES(parent_id),
                updated_at = NOW()
        ");
        return $stmt->execute([
            $name,
            $description,
            json_encode($settings),
            $parentId
        ]);
    }

    /**
     * Get a policy by name with inherited settings
     */
    public function getPolicy(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM security_policies WHERE name = ?");
        $stmt->execute([$name]);
        $policy = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$policy) {
            return null;
        }

        $settings = json_decode($policy['settings'], true) ?: [];

        // Apply inheritance
        if ($policy['parent_id']) {
            $parent = $this->getPolicyById($policy['parent_id']);
            if ($parent) {
                $parentSettings = json_decode($parent['settings'], true) ?: [];
                $settings = array_merge($parentSettings, $settings);
            }
        }

        $policy['settings'] = $settings;
        return $policy;
    }

    /**
     * Get policy by ID
     */
    private function getPolicyById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM security_policies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all policies
     */
    public function getAllPolicies(): array
    {
        $stmt = $this->db->query("SELECT * FROM security_policies ORDER BY name");
        $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($policies as &$policy) {
            $policy['settings'] = json_decode($policy['settings'], true) ?: [];
        }

        return $policies;
    }

    /**
     * Delete a policy
     */
    public function deletePolicy(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM security_policies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ========== DASHBOARD STATS ==========

    /**
     * Get comprehensive security dashboard stats
     */
    public function getDashboardStats(): array
    {
        // Recent login stats
        $stmt = $this->db->query("
            SELECT
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed
            FROM login_attempts
            WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $loginStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Blocked IPs count
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM blocked_ips
            WHERE is_permanent = 1 OR expires_at IS NULL OR expires_at > NOW()
        ");
        $blockedIps = (int) $stmt->fetchColumn();

        // Security events by severity (last 24h)
        $stmt = $this->db->query("
            SELECT severity, COUNT(*) as count
            FROM security_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY severity
        ");
        $eventsBySeverity = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Recent critical events
        $stmt = $this->db->query("
            SELECT * FROM security_logs
            WHERE severity IN ('critical', 'high')
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $criticalEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Active sessions count (from activity_logs if available)
        $activeSessions = 0;
        try {
            $stmt = $this->db->query("
                SELECT COUNT(DISTINCT user_id) FROM activity_logs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ");
            $activeSessions = (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            // activity_logs may not exist
        }

        return [
            'login_stats_24h' => [
                'successful' => (int) ($loginStats['successful'] ?? 0),
                'failed' => (int) ($loginStats['failed'] ?? 0)
            ],
            'blocked_ips' => $blockedIps,
            'events_by_severity' => [
                'critical' => (int) ($eventsBySeverity['critical'] ?? 0),
                'high' => (int) ($eventsBySeverity['high'] ?? 0),
                'medium' => (int) ($eventsBySeverity['medium'] ?? 0),
                'low' => (int) ($eventsBySeverity['low'] ?? 0)
            ],
            'critical_events' => $criticalEvents,
            'active_sessions' => $activeSessions
        ];
    }
}
