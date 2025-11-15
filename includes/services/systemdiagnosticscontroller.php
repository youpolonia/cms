<?php
declare(strict_types=1);

class SystemDiagnosticsController {
    /**
     * Simple ping endpoint protected by auth
     * @return void
     */
    public function ping(): void {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    /**
     * Check database connection status
     * @return void
     */
    public function checkDatabase(): void {
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../../core/database.php';
            $db = \core\Database::connection();
            echo json_encode(['db' => 'connected']);
        } catch (Exception $e) {
            echo json_encode(['db' => 'error']);
        }
    }

    /**
     * Get system information
     * @return void
     */
    public function systemInfo(): void {
        header('Content-Type: application/json');
        echo json_encode([
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'memory_limit' => ini_get('memory_limit')
        ]);
    }

    /**
     * Check security status
     * @return void
     */
    public function securityStatus(): void {
        header('Content-Type: application/json');
        
        require_once __DIR__ . '/../security/emergency_mode.php';
        require_once __DIR__ . '/../security/securitylogger.php';
        
        $emergencyMode = EmergencyMode::isActive();
        $logWritable = is_writable(__DIR__ . '/../logs/security.log');
        
        echo json_encode([
            'emergency_mode' => $emergencyMode,
            'logs_writable' => $logWritable
        ]);
    }
}
