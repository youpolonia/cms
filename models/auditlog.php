<?php

class AuditLog {
    private static $db;
    
    public static function init() {
        self::$db = \core\Database::connection();
    }

    // Create a new audit log entry
    public static function log(int $userId, string $action, ?string $details = null): bool {
        try {
            $sql = "INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$userId, $action, $details]);
        } catch (\Exception $e) {
            error_log("AuditLog::log() error: " . $e->getMessage());
            return false;
        }
    }

    // Get log by ID
    public static function getById(int $id): ?array {
        try {
            $sql = "SELECT * FROM audit_logs WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log("AuditLog::getById() error: " . $e->getMessage());
            return null;
        }
    }

    // Get logs by user ID
    public static function getByUser(int $userId, int $limit = 100): array {
        try {
            $sql = "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("AuditLog::getByUser() error: " . $e->getMessage());
            return [];
        }
    }

    // Get logs by action
    public static function getByAction(string $action, int $limit = 100): array {
        try {
            $sql = "SELECT * FROM audit_logs WHERE action = ? ORDER BY created_at DESC LIMIT ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$action, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("AuditLog::getByAction() error: " . $e->getMessage());
            return [];
        }
    }

    // Get recent logs
    public static function getRecent(int $limit = 100): array {
        try {
            $sql = "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("AuditLog::getRecent() error: " . $e->getMessage());
            return [];
        }
    }

    // Search logs with filters
    public static function search(?int $userId = null, ?string $action = null, ?string $dateFrom = null, ?string $dateTo = null, int $limit = 100): array {
        try {
            $conditions = [];
            $params = [];
            
            if ($userId !== null) {
                $conditions[] = "user_id = ?";
                $params[] = $userId;
            }
            
            if ($action !== null) {
                $conditions[] = "action = ?";
                $params[] = $action;
            }
            
            if ($dateFrom !== null) {
                $conditions[] = "created_at >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo !== null) {
                $conditions[] = "created_at <= ?";
                $params[] = $dateTo;
            }
            
            $sql = "SELECT * FROM audit_logs";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = self::$db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("AuditLog::search() error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize the AuditLog class with database connection
AuditLog::init();
