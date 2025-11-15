<?php

namespace Includes\Auth;

use Includes\Database\Database;

class RateLimiter
{
    protected $db;
    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function tooManyAttempts(string $key): bool
    {
        $attempts = $this->db->query(
            "SELECT attempts FROM rate_limits WHERE `key` = ? AND expires_at > NOW()",
            [$key]
        )->fetchColumn();

        return $attempts >= $this->maxAttempts;
    }

    public function hit(string $key): int
    {
        $this->db->query(
            "INSERT INTO rate_limits (`key`, attempts, expires_at) 
            VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? MINUTE))
            ON DUPLICATE KEY UPDATE 
            attempts = attempts + 1,
            expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE)",
            [$key, $this->decayMinutes, $this->decayMinutes]
        );

        return $this->availableIn($key);
    }

    public function availableIn(string $key): int
    {
        $expires = $this->db->query(
            "SELECT TIMESTAMPDIFF(SECOND, NOW(), expires_at) 
            FROM rate_limits WHERE `key` = ?",
            [$key]
        )->fetchColumn();

        return max(0, (int)$expires);
    }

    public function clear(string $key): void
    {
        $this->db->query(
            "DELETE FROM rate_limits WHERE `key` = ?",
            [$key]
        );
    }

    public function remaining(string $key): int
    {
        $attempts = $this->db->query(
            "SELECT attempts FROM rate_limits WHERE `key` = ?",
            [$key]
        )->fetchColumn();

        return max(0, $this->maxAttempts - (int)$attempts);
    }
}
