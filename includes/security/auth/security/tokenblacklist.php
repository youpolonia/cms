<?php

namespace Includes\Auth;

class TokenBlacklist {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function add(string $token, int $userId, \DateTime $expiresAt, ?string $ip = null): bool {
        $hashedToken = hash('sha256', $token);
        $stmt = $this->db->prepare(
            "INSERT INTO token_blacklist (token_hash, user_id, expires_at, ip_address)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$hashedToken, $userId, $expiresAt->format('Y-m-d H:i:s'), $ip]);
    }

    public function isBlacklisted(string $token, ?string $ip = null): bool {
        $hashedToken = hash('sha256', $token);
        $sql = "SELECT 1 FROM token_blacklist
                WHERE token_hash = ? AND expires_at > NOW()";
        $params = [$hashedToken];
        
        if ($ip) {
            $sql .= " AND (ip_address IS NULL OR ip_address = ?)";
            $params[] = $ip;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    public function revokeAllForUser(int $userId): int {
        $stmt = $this->db->prepare(
            "DELETE FROM token_blacklist WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    public function cleanupExpired(): int {
        $stmt = $this->db->prepare(
            "DELETE FROM token_blacklist WHERE expires_at <= NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function scheduleCleanup(): void {
        // Run cleanup if more than 100 expired tokens exist
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM token_blacklist WHERE expires_at <= NOW()"
        );
        if ($stmt->fetchColumn() > 100) {
            $this->cleanupExpired();
        }
    }
}
