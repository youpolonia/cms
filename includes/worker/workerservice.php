<?php
require_once __DIR__ . '/../service/ServiceIntegrationHandler.php';
require_once __DIR__ . '/../logging/logger.php';

class WorkerService {
    private $logger;
    private $serviceHandler;
    private $healthCheckInterval = 300; // 5 minutes in seconds

    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->serviceHandler = new ServiceIntegrationHandler($logger);
    }

    public function scheduleHealthChecks() {
        $this->logger->info("Scheduling service health checks");
        while (true) {
            $this->performHealthChecks();
            sleep($this->healthCheckInterval);
        }
    }

    private function performHealthChecks() {
        try {
            $services = $this->serviceHandler->discoverServices();
            foreach ($services as $service) {
                $health = $this->serviceHandler->verifyServiceHealth($service['name']);
                $this->logger->info("Health check for {$service['name']}: " . json_encode($health));
            }
        } catch (Exception $e) {
            $this->logger->error("Health check failed: " . $e->getMessage());
        }
    }

    // Existing WorkerService methods remain unchanged
    // ...
}
