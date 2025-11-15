<?php

class SchedulingPermissionService {
    protected $db;
    protected $cache = [];
    protected $cacheTtl = 300; // 5 minutes

    public function __construct($db) {
        $this->db = $db;
    }

    public function canScheduleContent(int $userId, string $scheduleType): bool {
        $cacheKey = "schedule_{$userId}_{$scheduleType}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $stmt = $this->db->prepare("
            SELECT EXISTS(
                SELECT 1 FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN scheduling_permissions sp ON rp.permission_id = sp.permission_id
                WHERE u.id = ? AND sp.schedule_type = ?
            ) as has_permission
        ");
        $stmt->execute([$userId, $scheduleType]);
        $result = (bool)$stmt->fetchColumn();

        $this->cache[$cacheKey] = $result;
        return $result;
    }

    public function canManageVersionControl(int $userId): bool {
        $cacheKey = "version_{$userId}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $stmt = $this->db->prepare("
            SELECT EXISTS(
                SELECT 1 FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN scheduling_permissions sp ON rp.permission_id = sp.permission_id
                WHERE u.id = ? AND sp.version_control = TRUE
            ) as has_permission
        ");
        $stmt->execute([$userId]);
        $result = (bool)$stmt->fetchColumn();

        $this->cache[$cacheKey] = $result;
        return $result;
    }

    public function canResolveConflicts(int $userId): bool {
        $cacheKey = "conflict_{$userId}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $stmt = $this->db->prepare("
            SELECT EXISTS(
                SELECT 1 FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN scheduling_permissions sp ON rp.permission_id = sp.permission_id
                WHERE u.id = ? AND sp.conflict_resolution = TRUE
            ) as has_permission
        ");
        $stmt->execute([$userId]);
        $result = (bool)$stmt->fetchColumn();

        $this->cache[$cacheKey] = $result;
        return $result;
    }

    public function clearCache(): void {
        $this->cache = [];
    }
}
