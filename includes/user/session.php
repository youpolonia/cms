<?php

namespace CMS\User;

class Session {
    private $db;
    private $sessionLifetime = 86400; // 24 hours

    public function __construct($db) {
        $this->db = $db;
        $this->startSession();
    }

    private function startSession(): void {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../core/session_boot.php';
        cms_session_start('public');
    }

    public function create(int $userId, string $ipAddress, string $userAgent): string {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);

        $this->db->execute(
            "INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)",
            [$sessionId, $userId, $ipAddress, $userAgent, $expiresAt]
        );

        $_SESSION['session_id'] = $sessionId;
        $_SESSION['user_id'] = $userId;
        return $sessionId;
    }

    public function validate(string $sessionId, string $ipAddress, string $userAgent): bool {
        $session = $this->db->query(
            "SELECT * FROM user_sessions 
            WHERE session_id = ? AND expires_at > NOW()",
            [$sessionId]
        );

        if (!$session) {
            return false;
        }

        // Basic security checks
        if ($session['ip_address'] !== $ipAddress || $session['user_agent'] !== $userAgent) {
            $this->destroy($sessionId);
            return false;
        }

        // Refresh session expiration
        $this->refresh($sessionId);
        return true;
    }

    public function refresh(string $sessionId): bool {
        $newExpiry = date('Y-m-d H:i:s', time() + $this->sessionLifetime);
        return $this->db->execute(
            "UPDATE user_sessions SET expires_at = ? WHERE session_id = ?",
            [$newExpiry, $sessionId]
        );
    }

    public function destroy(string $sessionId): bool {
        unset($_SESSION['session_id'], $_SESSION['user_id']);
        return $this->db->execute(
            "DELETE FROM user_sessions WHERE session_id = ?",
            [$sessionId]
        );
    }

    public function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public function getSessionId(): ?string {
        return $_SESSION['session_id'] ?? null;
    }

    public function cleanupExpiredSessions(): int {
        $result = $this->db->execute("DELETE FROM user_sessions WHERE expires_at <= NOW()");
        return $result ? $this->db->affectedRows() : 0;
    }
}
