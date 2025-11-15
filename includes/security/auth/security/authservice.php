<?php
namespace Includes\Security;

use PDO;
use PDOException;

class AuthService {
    private static $pdo;

    public static function init(PDO $pdo): void {
        self::$pdo = $pdo;
    }

    public static function authenticate(string $username, string $password): ?array {
        try {
            // Check user credentials
            $stmt = self::$pdo->prepare("
                SELECT id, password_hash 
                FROM users 
                WHERE username = :username OR email = :username
            ");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            return null;
        }
    }

    public static function validateTenant(int $userId, int $tenantId): bool {
        try {
            $stmt = self::$pdo->prepare("
                SELECT 1 
                FROM user_tenants 
                WHERE user_id = :user_id AND tenant_id = :tenant_id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':tenant_id' => $tenantId
            ]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Tenant validation error: " . $e->getMessage());
            return false;
        }
    }
}
