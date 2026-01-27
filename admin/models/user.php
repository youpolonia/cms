<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * User Model - Handles user CRUD operations with validation
 */
class User
{
    private static $db; // Database connection

    /**
     * Initialize database connection
     */
    public static function init($dbConnection)
    {
        self::$db = $dbConnection;
    }

    /**
     * Create new user with validation
     */
    public static function create(array $userData): array
    {
        // Validate input
        $validation = self::validateUserData($userData);
        if (!$validation['valid']) {
            return $validation;
        }

        // Check for duplicates
        if (self::usernameExists($userData['username'])) {
            return ['valid' => false, 'error' => 'Username already exists'];
        }
        if (self::emailExists($userData['email'])) {
            return ['valid' => false, 'error' => 'Email already exists'];
        }

        // Hash password
        $hashedPassword = self::hashPassword($userData['password']);

        // Insert user
        $stmt = self::$db->prepare("
            INSERT INTO users 
            (username, email, password, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userData['username'],
            $userData['email'],
            $hashedPassword
        ]);

        return ['valid' => true, 'userId' => self::$db->lastInsertId()];
    }

    /**
     * Update existing user
     */
    public static function update(int $userId, array $userData): array
    {
        // Validate input
        $validation = self::validateUserData($userData, false);
        if (!$validation['valid']) {
            return $validation;
        }

        // Get existing user
        $user = self::getById($userId);
        if (!$user) {
            return ['valid' => false, 'error' => 'User not found'];
        }

        // Check for duplicate username (if changed)
        if ($userData['username'] !== $user['username'] && self::usernameExists($userData['username'])) {
            return ['valid' => false, 'error' => 'Username already exists'];
        }

        // Check for duplicate email (if changed)
        if ($userData['email'] !== $user['email'] && self::emailExists($userData['email'])) {
            return ['valid' => false, 'error' => 'Email already exists'];
        }

        // Prepare update
        $updates = [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Only update password if provided
        if (!empty($userData['password'])) {
            $updates['password'] = self::hashPassword($userData['password']);
        }

        // Build query
        $setParts = [];
        $params = [];
        foreach ($updates as $field => $value) {
            $setParts[] = "$field = ?";
            $params[] = $value;
        }
        $params[] = $userId;

        $stmt = self::$db->prepare("
            UPDATE users 
            SET " . implode(', ', $setParts) . " 
            WHERE id = ?
        ");
        $stmt->execute($params);

        return ['valid' => true];
    }

    /**
     * Delete user
     */
    public static function delete(int $userId): bool
    {
        $stmt = self::$db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Hash password using PHP password_hash
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Validate user data
     */
    private static function validateUserData(array $data, bool $requirePassword = true): array
    {
        // Required fields
        $required = ['username', 'email'];
        if ($requirePassword) {
            $required[] = 'password';
        }

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'error' => "$field is required"];
            }
        }

        // Username validation (alphanumeric + underscore, 3-20 chars)
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
            return ['valid' => false, 'error' => 'Username must be 3-20 chars (letters, numbers, _)'];
        }

        // Email validation
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }

        // Password validation (if provided)
        if (isset($data['password']) && strlen($data['password']) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
        }

        return ['valid' => true];
    }

    /**
     * Check if username exists
     */
    private static function usernameExists(string $username): bool
    {
        $stmt = self::$db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email exists
     */
    private static function emailExists(string $email): bool
    {
        $stmt = self::$db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get user by ID
     */
    public static function getById(int $userId): ?array
    {
        $stmt = self::$db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
