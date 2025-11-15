<?php

declare(strict_types=1);

namespace Auth\Services;

use PDO;
use Exception;
use RuntimeException;
use Auth\Services\SessionService;

class AuthService
{
    private const CSRF_TOKEN_KEY = 'csrf_token';
    private const AUTH_USER_ID_KEY = 'auth_user_id';
    private const AUTH_TENANT_ID_KEY = 'auth_tenant_id';
    private PDO $db;
    private SessionService $session;

    public function __construct(PDO $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Authenticate user credentials
     */
    public function login(string $username, string $password, ?string $csrfToken = null): bool
    {
        if ($csrfToken && !$this->validateCsrfToken($csrfToken)) {
            error_log('CSRF token validation failed for login attempt.');
            return false; // Or throw an exception
        }

        $stmt = $this->db->prepare("SELECT u.id, u.username, u.password_hash, u.is_active, t.id as tenant_id
                                   FROM users u
                                   JOIN tenants t ON u.tenant_id = t.id
                                   WHERE u.username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['is_active'] && $this->verifyPassword($password, $user['password_hash'])) {
            $this->session->regenerate(); // Regenerate session ID to prevent fixation
            $this->session->set(self::AUTH_USER_ID_KEY, $user['id']);
            $this->session->set(self::AUTH_TENANT_ID_KEY, $user['tenant_id']);
            $this->generateCsrfToken(); // Generate a new CSRF token for subsequent requests
            return true;
        }
        
        error_log("Login failed for username: {$username}");
        return false;
    }

    /**
     * Process user logout
     */
    public function logout(): void
    {
        $this->session->destroy();
    }

    /**
     * Handle user registration
     */
    public function register(array $userData): bool
    {
        // Validate input
        if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
            throw new RuntimeException('Username, email, and password are required for registration.');
        }

        // Check if username or email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->bindParam(':username', $userData['username']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->execute();
        if ($stmt->fetch()) {
            throw new RuntimeException('Username or email already exists.');
        }

        // Hash password before storage
        $hashedPassword = $this->hashPassword($userData['password']);

        $firstName = $userData['first_name'] ?? null;
        $lastName = $userData['last_name'] ?? null;
        $isActive = $userData['is_active'] ?? 1; // Default to active

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, first_name, last_name, is_active, created_at, updated_at)
             VALUES (:username, :email, :password_hash, :first_name, :last_name, :is_active, NOW(), NOW())"
        );

        $stmt->bindParam(':username', $userData['username']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->bindParam(':password_hash', $hashedPassword);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log error, e.g., $e->getMessage()
            error_log("Error during user registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate secure password hash
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32)); // 32 bytes = 64 hex characters
        $this->session->set(self::CSRF_TOKEN_KEY, [
            'value' => $token,
            'expiry' => time() + 3600 // 1 hour
        ]);
        return $token;
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $token): bool
    {
        if (!$this->session->has(self::CSRF_TOKEN_KEY)) {
            return false;
        }

        $storedTokenData = $this->session->get(self::CSRF_TOKEN_KEY);
        
        if (!is_array($storedTokenData) || !isset($storedTokenData['value']) || !isset($storedTokenData['expiry'])) {
            $this->session->remove(self::CSRF_TOKEN_KEY); // Clear invalid token data
            return false;
        }
        
        // Check token value and expiry
        $isValid = hash_equals((string)$storedTokenData['value'], $token) && time() < (int)$storedTokenData['expiry'];
        
        // One-time use: remove token after validation attempt (success or fail) to prevent replay attacks
        // However, for forms that might fail validation and need resubmission, this might be too strict.
        // For now, let's keep it simple and not remove immediately. Consider this for higher security needs.
        // $this->session->remove(self::CSRF_TOKEN_KEY);

        return $isValid;
    }

    public function isLoggedIn(): bool
    {
        return $this->session->has(self::AUTH_USER_ID_KEY);
    }

    public function getCurrentUserId(): ?int
    {
        return $this->session->get(self::AUTH_USER_ID_KEY);
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        $userId = $this->getCurrentUserId();
        $stmt = $this->db->prepare("SELECT id, username, email, first_name, last_name, is_active FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}
