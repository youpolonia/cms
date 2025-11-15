<?php

class RateLimiter {
    private Database $db;
    private EmergencyLogger $logger;
    private int $maxAttempts;
    private int $timeoutSeconds;

    public function __construct(Database $db, EmergencyLogger $logger, int $maxAttempts = 5, int $timeoutSeconds = 300) {
        $this->db = $db;
        $this->logger = $logger;
        $this->maxAttempts = $maxAttempts;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function check(string $ip, string $identifier): bool {
        // Clean up old attempts
        $this->cleanupOldAttempts($ip, $identifier);

        // Get current attempts
        $attempts = $this->getAttempts($ip, $identifier);

        if ($attempts >= $this->maxAttempts) {
            $this->logger->log("Rate limit exceeded for $identifier from $ip", $ip);
            return false;
        }

        return true;
    }

    public function recordAttempt(string $ip, string $identifier): void {
        $stmt = $this->db->prepare(
            "INSERT INTO rate_limits (ip, identifier, created_at) VALUES (?, ?, NOW())"
        );
        $stmt->execute([$ip, $identifier]);
    }

    private function getAttempts(string $ip, string $identifier): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM rate_limits 
             WHERE ip = ? AND identifier = ? 
             AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$ip, $identifier, $this->timeoutSeconds]);
        return (int)$stmt->fetchColumn();
    }

    private function cleanupOldAttempts(string $ip, string $identifier): void {
        $stmt = $this->db->prepare(
            "DELETE FROM rate_limits 
             WHERE ip = ? AND identifier = ? 
             AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$ip, $identifier, $this->timeoutSeconds]);
    }
}
