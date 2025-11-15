<?php
namespace Includes\Auth;

use PDO;

class RateLimiter {
    private $db;
    private $maxAttempts = 5;
    private $decayMinutes = 15;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function tooManyAttempts($key): bool {
        $stmt = $this->db->prepare(
            "SELECT attempts, created_at 
             FROM rate_limits 
             WHERE `key` = ? 
             AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE"
        );
        $stmt->execute([$key, $this->decayMinutes]);
        $result = $stmt->fetch();

        return $result && $result['attempts'] >= $this->maxAttempts;
    }

    public function hit($key): void {
        $this->db->prepare(
            "INSERT INTO rate_limits (`key`, attempts) 
             VALUES (?, 1)
             ON DUPLICATE KEY UPDATE 
             attempts = attempts + 1, created_at = NOW()"
        )->execute([$key]);
    }

    public function clear($key): void {
        $this->db->prepare(
            "DELETE FROM rate_limits WHERE `key` = ?"
        )->execute([$key]);
    }

    public function availableIn($key): int {
        $stmt = $this->db->prepare(
            "SELECT TIMESTAMPDIFF(SECOND, NOW(), 
             DATE_ADD(created_at, INTERVAL ? MINUTE)) as remaining
             FROM rate_limits 
             WHERE `key` = ?"
        );
        $stmt->execute([$this->decayMinutes, $key]);
        $result = $stmt->fetch();

        return $result ? max(0, $result['remaining']) : 0;
    }
}
