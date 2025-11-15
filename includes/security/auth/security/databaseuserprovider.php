<?php
require_once __DIR__ . '/../../../core/database.php';

namespace Includes\Auth;

use Includes\Database;

class DatabaseUserProvider implements UserProvider {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function retrieveById($id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function retrieveByCredentials(array $credentials): ?array {
        if (empty($credentials['email'])) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$credentials['email']]);
        return $stmt->fetch() ?: null;
    }

    public function validateCredentials(array $user, array $credentials): bool {
        if (empty($credentials['password'])) {
            return false;
        }

        $authService = new AuthService($this, new SessionManager());
        return $authService->verifyPassword($credentials['password'], $user['password']);
    }

    public function updateRememberToken($userId, string $token, int $expires): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?"
        );
        return $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $userId]);
    }

    public function retrieveByToken(string $token): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE remember_token = ? AND remember_token_expires > NOW() AND deleted_at IS NULL"
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function sendVerificationEmail(array $user): bool {
        // Implementation would depend on the mail system
        // Placeholder for actual email sending logic
        return true;
    }

    public function verifyEmail(array $user, string $token): bool {
        if ($user['email_verification_token'] !== $token) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE users SET email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?"
        );
        return $stmt->execute([$user['id']]);
    }

    public function hasVerifiedEmail(array $user): bool {
        return !empty($user['email_verified_at']);
    }
}
