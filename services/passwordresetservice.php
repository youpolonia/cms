<?php
class PasswordResetService {
    private Database $db;
    private EmailService $email;
    private int $tokenExpiryHours = 24;

    public function __construct(Database $db, EmailService $email) {
        $this->db = $db;
        $this->email = $email;
    }

    public function requestReset(string $email): bool {
        $user = $this->db->query(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        )->fetch();

        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->tokenExpiryHours} hours"));

        $this->db->query(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)",
            [$user['id'], $token, $expiresAt]
        );

        $resetLink = "https://example.com/reset-password?token=$token";
        return $this->email->send(
            $email,
            'Password Reset Request',
            "Click this link to reset your password: $resetLink"
        );
    }

    public function validateToken(string $token): ?int {
        $result = $this->db->query(
            "SELECT user_id FROM password_resets 
             WHERE token = ? AND expires_at > NOW()",
            [$token]
        )->fetch();

        return $result ? $result['user_id'] : null;
    }

    public function resetPassword(string $token, string $newPassword): bool {
        $userId = $this->validateToken($token);
        if (!$userId) {
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $userId]
        );

        $this->db->query(
            "DELETE FROM password_resets WHERE token = ?",
            [$token]
        );

        return true;
    }
}
