<?php
/**
 * UserManager - Handles user CRUD, authentication and password management
 * 
 * @package CMS
 * @subpackage Auth
 */

class UserManager {
    private $db;
    private $table = 'users';
    
    /**
     * Constructor - requires database connection
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Create new user
     * @param array $userData User data (must require_once email, password)
     * @return int|false New user ID or false on failure
     */
    public function createUser(array $userData): int|false {
        if (empty($userData['email']) || empty($userData['password'])) {
            return false;
        }
        
        // Check for existing user first
        if ($this->emailExists($userData['email'])) {
            return false;
        }
        
        $userData['password'] = $this->hashPassword($userData['password']);
        $userData['created_at'] = date('Y-m-d H:i:s');
        
        $columns = implode(', ', array_keys($userData));
        $placeholders = ':' . implode(', :', array_keys($userData));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        
        return $stmt->execute($userData) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Get user by ID
     * @param int $userId
     * @return array|false User data or false if not found
     */
    public function getUser(int $userId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update user
     * @param int $userId
     * @param array $userData
     * @return bool True on success
     */
    public function updateUser(int $userId, array $userData): bool {
        if (isset($userData['password'])) {
            $userData['password'] = $this->hashPassword($userData['password']);
        }
        
        $setParts = [];
        foreach ($userData as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        
        $setClause = implode(', ', $setParts);
        $userData['id'] = $userId;
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET $setClause WHERE id = :id");
        return $stmt->execute($userData);
    }
    
    /**
     * Delete user
     * @param int $userId
     * @return bool True on success
     */
    public function deleteUser(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }
    
    /**
     * Authenticate user
     * @param string $email
     * @param string $password
     * @return array|false User data on success, false on failure
     */
    public function authenticate(string $email, string $password): array|false {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($user && $this->verifyPassword($password, $user['password'])) ? $user : false;
    }
    
    /**
     * Hash password
     * @param string $password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     * @param string $password
     * @param string $hash
     * @return bool True if password matches hash
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if email exists
     * @param string $email
     * @return bool True if email exists
     */
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetchColumn();
    }
}
