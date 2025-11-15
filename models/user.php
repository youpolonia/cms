<?php

require_once __DIR__ . '/../config.php';

class User {
    private static $db;

    public static function init() {
        self::$db = \core\Database::connection();
    }

    // Create a new user
    public static function create(array $data): ?int {
        try {
            $sql = "INSERT INTO users (username, email, password_hash, created_at) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = self::$db->prepare($sql);
            $success = $stmt->execute([
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            return $success ? self::$db->lastInsertId() : null;
        } catch (\Exception $e) {
            error_log("User::create() error: " . $e->getMessage());
            return null;
        }
    }

    // Get user by ID
    public static function getById(int $id): ?array {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log("User::getById() error: " . $e->getMessage());
            return null;
        }
    }

    // Update user
    public static function update(int $id, array $data): bool {
        try {
            $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$data['username'], $data['email'], $id]);
        } catch (\Exception $e) {
            error_log("User::update() error: " . $e->getMessage());
            return false;
        }
    }

    // Delete user
    public static function delete(int $id): bool {
        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("User::delete() error: " . $e->getMessage());
            return false;
        }
    }

    // Assign role to user
    public static function assignRole(int $userId, int $roleId): bool {
        try {
            $sql = "INSERT INTO user_role_assignments (user_id, role_id) VALUES (?, ?)";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$userId, $roleId]);
        } catch (\Exception $e) {
            error_log("User::assignRole() error: " . $e->getMessage());
            return false;
        }
    }

    // Reset password
    public static function resetPassword(int $userId, string $newPassword): bool {
        try {
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                $userId
            ]);
        } catch (\Exception $e) {
            error_log("User::resetPassword() error: " . $e->getMessage());
            return false;
        }
    }

    // Get user roles
    public static function getRoles(int $userId): array {
        try {
            $sql = "SELECT r.* FROM user_roles r
                    JOIN user_role_assignments ura ON r.id = ura.role_id
                    WHERE ura.user_id = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("User::getRoles() error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize the User class with database connection
User::init();
