<?php

class NotificationRules {
    /**
     * Create a new notification rule
     * @param array $data Rule data including tenant_id, name, conditions, actions
     * @return int|false Inserted ID or false on failure
     */
    public static function create(array $data): int|false {
        self::validateRuleData($data);
        
        try {
            $db = self::getDB();
            $stmt = $db->prepare("
                INSERT INTO notification_rules 
                (tenant_id, name, conditions, actions, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['tenant_id'],
                $data['name'],
                json_encode($data['conditions']),
                json_encode($data['actions']),
                $data['is_active'] ?? 1
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("NotificationRules create failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notification rule by ID with tenant isolation
     * @param int $id Rule ID
     * @param int $tenant_id Tenant ID for isolation
     * @return array|false Rule data or false if not found
     */
    public static function read(int $id, int $tenant_id): array|false {
        try {
            $stmt = self::getDB()->prepare("
                SELECT * FROM notification_rules 
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([$id, $tenant_id]);
            $rule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rule) {
                $rule['conditions'] = json_decode($rule['conditions'], true);
                $rule['actions'] = json_decode($rule['actions'], true);
            }
            return $rule;
        } catch (PDOException $e) {
            error_log("NotificationRules read failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update notification rule with tenant isolation
     * @param int $id Rule ID
     * @param int $tenant_id Tenant ID
     * @param array $data Update data
     * @return bool True on success, false on failure
     */
    public static function update(int $id, int $tenant_id, array $data): bool {
        self::validateRuleData($data);
        
        try {
            $stmt = self::getDB()->prepare("
                UPDATE notification_rules 
                SET name = ?, conditions = ?, actions = ?, is_active = ?, updated_at = NOW()
                WHERE id = ? AND tenant_id = ?
            ");
            return $stmt->execute([
                $data['name'],
                json_encode($data['conditions']),
                json_encode($data['actions']),
                $data['is_active'] ?? 1,
                $id,
                $tenant_id
            ]);
        } catch (PDOException $e) {
            error_log("NotificationRules update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete notification rule with tenant isolation
     * @param int $id Rule ID
     * @param int $tenant_id Tenant ID
     * @return bool True on success, false on failure
     */
    public static function delete(int $id, int $tenant_id): bool {
        try {
            $stmt = self::getDB()->prepare("
                DELETE FROM notification_rules 
                WHERE id = ? AND tenant_id = ?
            ");
            return $stmt->execute([$id, $tenant_id]);
        } catch (PDOException $e) {
            error_log("NotificationRules delete failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate rule data structure
     * @param array $data Rule data
     * @throws InvalidArgumentException If validation fails
     */
    private static function validateRuleData(array $data): void {
        $required = ['tenant_id', 'name', 'conditions', 'actions'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        if (!is_array($data['conditions'])) {
            throw new InvalidArgumentException("Conditions must be an array");
        }

        if (!is_array($data['actions'])) {
            throw new InvalidArgumentException("Actions must be an array");
        }
    }

    /**
     * Get database connection
     * @return PDO
     */
    private static function getDB(): PDO {
        // Assuming global $db exists - adjust if project uses different connection method
        global $db;
        if (!$db instanceof PDO) {
            throw new RuntimeException("Database connection not available");
        }
        return $db;
    }
}
