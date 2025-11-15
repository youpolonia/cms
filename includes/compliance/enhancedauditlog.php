<?php
declare(strict_types=1);

/**
 * Compliance - Enhanced Audit Log
 * Provides comprehensive activity tracking with tamper-evident features
 */
class EnhancedAuditLog {
    private static string $logDir = __DIR__ . '/../../logs/audit/';
    private static string $currentLogFile;
    private static string $hashAlgo = 'sha256';
    private static string $previousHash = '';

    /**
     * Initialize audit log system
     */
    public static function init(): void {
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        self::$currentLogFile = self::$logDir . 'audit_' . date('Y-m-d') . '.log';
        self::loadPreviousHash();
    }

    /**
     * Log an auditable event
     */
    public static function logEvent(
        string $action,
        string $userId,
        array $details = [],
        ?string $entityType = null,
        ?string $entityId = null
    ): void {
        $entry = [
            'timestamp' => microtime(true),
            'action' => $action,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'previous_hash' => self::$previousHash
        ];

        $jsonEntry = json_encode($entry);
        $currentHash = hash(self::$hashAlgo, $jsonEntry . self::$previousHash);
        
        $logEntry = $currentHash . '|' . $jsonEntry . "\n";
        file_put_contents(self::$currentLogFile, $logEntry, FILE_APPEND);
        
        self::$previousHash = $currentHash;
    }

    /**
     * Verify log integrity
     */
    public static function verifyLog(string $logFile): bool {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $previousHash = '';

        foreach ($lines as $line) {
            list($hash, $json) = explode('|', $line, 2);
            $expectedHash = hash(self::$hashAlgo, $json . $previousHash);
            
            if ($hash !== $expectedHash) {
                return false;
            }
            $previousHash = $hash;
        }
        return true;
    }

    private static function loadPreviousHash(): void {
        $files = glob(self::$logDir . 'audit_*.log');
        if (empty($files)) {
            self::$previousHash = hash(self::$hashAlgo, 'initial_hash');
            return;
        }

        $lastFile = end($files);
        $lines = file($lastFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!empty($lines)) {
            $lastLine = end($lines);
            list(self::$previousHash, ) = explode('|', $lastLine, 2);
        }
    }

    // BREAKPOINT: Continue with retention policy implementation
}
