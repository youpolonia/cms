<?php
/**
 * Restoration Audit Logger
 * 
 * Tracks restoration attempts, successes, and failures with detailed metadata
 * 
 * Usage:
 * $logger = new RestorationLogger();
 * $logId = $logger->logAttempt($versionId, $userId, $ip, $stats, $options);
 * $logger->logSuccess($logId, $message = null);
 * $logger->logFailure($logId, $errorMessage);
 * 
 * @package CMS
 * @subpackage Audit
 */

class RestorationLogger {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../core/database.php';
        
        $this->db = \core\Database::connection();
    }
    
    /**
     * Log a restoration attempt
     * 
     * @param int $versionId Version being restored
     * @param int|null $userId User initiating the restoration
     * @param string $ipAddress User's IP address
     * @param array $stats Change statistics
     * @param array $options Restoration options
     * @return int Log entry ID
     */
    public function logAttempt($versionId, $userId, $ipAddress, $stats, $options) {
        $stmt = $this->db->prepare("
            INSERT INTO restoration_audit_log (
                event_type, version_id, user_id, ip_address,
                lines_added, lines_removed, lines_changed,
                notify_authors, clear_cache, create_backup
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'attempt',
            $versionId,
            $userId,
            $ipAddress,
            $stats['added'] ?? 0,
            $stats['removed'] ?? 0,
            $stats['changed'] ?? 0,
            $options['notifyAuthors'] ?? false,
            $options['clearCache'] ?? false,
            $options['createBackup'] ?? false
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Log a successful restoration
     * 
     * @param int $logId Original attempt log ID
     * @param string|null $message Optional success message
     */
    public function logSuccess($logId, $message = null) {
        $stmt = $this->db->prepare("
            UPDATE restoration_audit_log 
            SET event_type = 'success', status_message = ?
            WHERE id = ?
        ");
        $stmt->execute([$message, $logId]);
    }
    
    /**
     * Log a failed restoration
     * 
     * @param int $logId Original attempt log ID
     * @param string $errorMessage Error description
     */
    public function logFailure($logId, $errorMessage) {
        $stmt = $this->db->prepare("
            UPDATE restoration_audit_log 
            SET event_type = 'failure', status_message = ?
            WHERE id = ?
        ");
        $stmt->execute([$errorMessage, $logId]);
    }
    
}
