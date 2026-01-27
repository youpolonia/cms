<?php
/**
 * ConfigurationService - PDO-based config loader with RBAC
 */
class ConfigurationService {
    private static $pdo;
    
    public static function init(PDO $pdo) {
        self::$pdo = $pdo;
    }
    
    public static function getConfig(string $key): mixed {
        $stmt = self::$pdo->prepare("SELECT value FROM config WHERE `key` = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    }
    
    public static function checkConfigPermission(string $key, int $userId): bool {
        $stmt = self::$pdo->prepare(
            "SELECT 1 FROM config_permissions 
             WHERE config_key = ? AND user_id = ?"
        );
        $stmt->execute([$key, $userId]);
        return (bool)$stmt->fetchColumn();
    }
    
    public static function validateConfigScope(string $key, string $scope): bool {
        $stmt = self::$pdo->prepare(
            "SELECT 1 FROM config_scopes 
             WHERE config_key = ? AND scope = ?"
        );
        $stmt->execute([$key, $scope]);
        return (bool)$stmt->fetchColumn();
    }
}
