<?php
declare(strict_types=1);

/**
 * TenantMonitor Service - Security monitoring for multi-tenant system
 */
class TenantMonitor {
    private const SECURITY_EVENTS_FILE = __DIR__ . '/../../logs/tenant_security.log';
    private const ANOMALY_THRESHOLD = 3;
    
    /**
     * Track tenant activity and detect anomalies
     */
    public static function trackActivity(string $tenantId, string $action, array $context = []): void {
        $logEntry = [
            'timestamp' => time(),
            'tenant_id' => $tenantId,
            'action' => $action,
            'context' => $context
        ];
        
        file_put_contents(
            self::SECURITY_EVENTS_FILE,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );
        
        self::checkAnomalies($tenantId, $action);
    }
    
    /**
     * Analyze activity patterns for anomalies
     */
    private static function checkAnomalies(string $tenantId, string $action): void {
        $logLines = file(self::SECURITY_EVENTS_FILE, FILE_IGNORE_NEW_LINES);
        $recentActions = array_slice($logLines, -100); // Check last 100 actions
        
        $actionCount = 0;
        $timeWindow = 300; // 5 minutes
        
        foreach ($recentActions as $logLine) {
            $entry = json_decode($logLine, true);
            
            if ($entry['tenant_id'] === $tenantId &&
                $entry['action'] === $action &&
                time() - $entry['timestamp'] <= $timeWindow) {
                $actionCount++;
            }
        }
        
        if ($actionCount > self::ANOMALY_THRESHOLD) {
            self::raiseAlert(
                $tenantId,
                "Suspicious activity detected",
                [
                    'action' => $action,
                    'count' => $actionCount,
                    'threshold' => self::ANOMALY_THRESHOLD
                ]
            );
        }
    }
    
    /**
     * Generate security alert
     */
    public static function raiseAlert(string $tenantId, string $message, array $details = []): void {
        $alert = [
            'timestamp' => time(),
            'tenant_id' => $tenantId,
            'message' => $message,
            'details' => $details,
            'severity' => 'medium'
        ];
        
        // Log to security events
        file_put_contents(
            self::SECURITY_EVENTS_FILE,
            '[ALERT] ' . json_encode($alert) . PHP_EOL,
            FILE_APPEND
        );
        
        // Forward to MCP Alert system if available
        if (class_exists('MCPAlert')) {
            MCPAlert::sendSecurityAlert($tenantId, $message, $details);
        }
        
        // TODO: Implement additional alert integrations
    }
}
