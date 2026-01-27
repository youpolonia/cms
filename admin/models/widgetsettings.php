<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Widget Settings Model - Handles CRUD operations for widget configurations
 */
class WidgetSettings {
    private static ?PDO $db = null;

    /**
     * Get database connection (singleton pattern)
     */
    private static function getDB(): PDO {
        if (self::$db === null) {
            self::$db = \core\Database::connection();
        }
        return self::$db;
    }

    /**
     * Create new widget setting
     */
    public static function create(array $data, int $tenantId): int {
        $db = self::getDB();
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("
                INSERT INTO widget_settings 
                (widget_type, config_json, tenant_id, created_by, created_at) 
                VALUES (:type, :config, :tenant, :user, NOW())
            ");
            
            $stmt->execute([
                ':type' => $data['type'],
                ':config' => json_encode($data['config']),
                ':tenant' => $tenantId,
                ':user' => $data['user_id']
            ]);
            
            $id = $db->lastInsertId();
            $db->commit();
            return $id;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("WidgetSettings::create failed: " . $e->getMessage());
            throw new RuntimeException("Database error, please try again later.");
        }
    }

    /**
     * Get widget setting by ID with tenant isolation
     */
    public static function getById(int $id, int $tenantId): ?array {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT * FROM widget_settings 
                WHERE id = :id AND tenant_id = :tenant
            ");
            $stmt->execute([':id' => $id, ':tenant' => $tenantId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("WidgetSettings::getById failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update widget setting
     */
    public static function update(int $id, array $data, int $tenantId): bool {
        $db = self::getDB();
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("
                UPDATE widget_settings 
                SET widget_type = :type, 
                    config_json = :config,
                    updated_at = NOW()
                WHERE id = :id AND tenant_id = :tenant
            ");
            
            $result = $stmt->execute([
                ':id' => $id,
                ':type' => $data['type'],
                ':config' => json_encode($data['config']),
                ':tenant' => $tenantId
            ]);
            
            $db->commit();
            return $result;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("WidgetSettings::update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete widget setting
     */
    public static function delete(int $id, int $tenantId): bool {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                DELETE FROM widget_settings 
                WHERE id = :id AND tenant_id = :tenant
            ");
            return $stmt->execute([':id' => $id, ':tenant' => $tenantId]);
        } catch (PDOException $e) {
            error_log("WidgetSettings::delete failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * List all widget settings for tenant
     */
    public static function list(int $tenantId): array {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT * FROM widget_settings 
                WHERE tenant_id = :tenant
                ORDER BY created_at DESC
            ");
            $stmt->execute([':tenant' => $tenantId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("WidgetSettings::list failed: " . $e->getMessage());
            return [];
        }
    }
}
