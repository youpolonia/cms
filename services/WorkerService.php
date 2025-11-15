<?php
/**
 * WorkerService - Consolidated worker management service
 * Implements singleton pattern for framework-free access
 */
class WorkerService {
    private static $instance;
    private $configService;
    private $memoryManager;
    private $authService;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->configService = ConfigurationService::getInstance();
        $this->memoryManager = MemoryManager::getInstance();
        $this->authService = AuthService::getInstance();
    }

    public static function getInstance(): WorkerService {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function monitorHealth(): array {
        try {
            $threshold = $this->configService->get('health_threshold', 90);
            $status = $this->checkSystemStatus();
            
            if ($status['load'] > $threshold) {
                $this->manageRecovery();
            }
            
            return $status;
        } catch (Exception $e) {
            error_log("Health monitoring failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function checkHeartbeats(): bool {
        $lastHeartbeat = $this->configService->get('last_heartbeat', null);
        $timeout = $this->configService->get('heartbeat_timeout', 300);
        
        return (time() - $lastHeartbeat) < $timeout;
    }

    public function manageRecovery(): void {
        $this->memoryManager->cleanup();
        $this->authService->refreshToken();
    }

    public function trackMetrics(): array {
        return [
            'memory' => $this->memoryManager->getUsage(),
            'auth' => $this->authService->getStatus(),
            'config' => $this->configService->getMetrics()
        ];
    }

    private function checkSystemStatus(): array {
        return [
            'load' => sys_getloadavg()[0],
            'memory' => memory_get_usage(true),
            'processes' => count(glob('/proc/[0-9]*'))
        ];
    }
}
