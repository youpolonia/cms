<?php
namespace Includes\Auth\Services;

use PDO;

class EmergencyLogger {
    private $db;
    private $logTable = 'emergency_logs';
    private $allowedIPs = ['127.0.0.1', '::1']; // Default to localhost

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void {
        /* exec disabled: was $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->logTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context JSON DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (level),
                INDEX (created_at),
                INDEX (ip_address)
            )
        ") */
    }

    public function log(string $level, string $message, array $context = []): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Always allow emergency logs from whitelisted IPs
        if (in_array($ip, $this->allowedIPs, true)) {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->logTable} (level, message, context, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $level,
                $message,
                json_encode($context),
                $ip
            ]);
        }
    }

    public function getRecentLogs(int $limit = 100): array {
        // Require authentication for log retrieval
        if (!$this->isAuthenticated()) {
            $this->log('SECURITY', 'Unauthorized access attempt to getRecentLogs', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->logTable}
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function isAuthenticated(): bool {
        return isset($_SESSION['user_id']) && $_SESSION['user_level'] >= 2;
    }

    public function setAllowedIPs(array $ips): void {
        $this->allowedIPs = $ips;
    }
}
