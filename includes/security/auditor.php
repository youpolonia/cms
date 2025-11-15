<?php
/**
 * SecurityAuditor - Handles security event logging and auditing
 * 
 * @package CMS
 * @subpackage Security
 */

namespace CMS\Security;

class SecurityAuditor {
    /**
     * Logs a security audit event
     * 
     * @param string $event Event type/name
     * @param array $details Additional event details 
     * @return void
     */
    public function logAuditEvent(string $event, array $details): void {
        $userId = $_SESSION['user_id'] ?? null;
        $context = [
            'event' => $event,
            'user_id' => $userId,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null
        ];
        
        \CMS\Logging\Logger::info(
            "Security audit event: {$event}",
            $context,
            'security_audit'
        );
    }
}
