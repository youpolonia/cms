<?php

namespace Services;

use Core\Logger;
use Exception;

class ScalingService
{
    private Logger $logger;
    private array $tenantPolicies = [];
    private string $loadBalancerUrl;

    public function __construct(string $loadBalancerUrl)
    {
        $this->logger = new Logger();
        $this->loadBalancerUrl = $loadBalancerUrl;
    }

    /**
     * Set scaling policies for a tenant
     */
    public function setTenantPolicy(string $tenantId, array $policy): void
    {
        $this->tenantPolicies[$tenantId] = $policy;
        $this->logger->info("Set scaling policy for tenant $tenantId", $policy);
    }

    /**
     * Check system resources and trigger scaling if needed
     */
    public function checkAndScale(): void
    {
        $metrics = $this->getSystemMetrics();
        
        foreach ($this->tenantPolicies as $tenantId => $policy) {
            $this->evaluateScaling($tenantId, $metrics, $policy);
        }
    }

    /**
     * Get current system metrics (CPU, RAM, Disk)
     */
    private function getSystemMetrics(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage()
        ];
    }

    private function getCpuUsage(): float
    {
        try {
            $load = sys_getloadavg();
            return $load[0] ?? 0.0; // 1-minute load average
        } catch (Exception $e) {
            $this->logger->error("Failed to get CPU load", ['error' => $e->getMessage()]);
            return 0.0;
        }
    }

    private function getMemoryUsage(): float
    {
        $used = memory_get_usage(true);
        $total = memory_get_usage(true) + memory_get_usage(false);
        return $total > 0 ? ($used / $total) : 0;
    }

    private function getDiskUsage(): float
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        return $total > 0 ? (1 - ($free / $total)) : 0;
    }

    /**
     * Evaluate scaling needs for a tenant
     */
    private function evaluateScaling(string $tenantId, array $metrics, array $policy): void
    {
        // Check CPU scaling
        if ($metrics['cpu'] > $policy['cpu_threshold']) {
            $this->triggerScaling($tenantId, 'scale_up', $policy['scale_up_count']);
            $this->logger->info("Triggered scale up for tenant $tenantId due to high CPU", [
                'cpu' => $metrics['cpu'],
                'threshold' => $policy['cpu_threshold']
            ]);
        }
        
        // Check memory scaling
        if ($metrics['memory'] > $policy['memory_threshold']) {
            $this->triggerScaling($tenantId, 'scale_up', $policy['scale_up_count']);
            $this->logger->info("Triggered scale up for tenant $tenantId due to high memory", [
                'memory' => $metrics['memory'],
                'threshold' => $policy['memory_threshold']
            ]);
        }
        
        // Check scale down conditions
        if ($metrics['cpu'] < $policy['scale_down_threshold'] &&
            $metrics['memory'] < $policy['scale_down_threshold']) {
            $this->triggerScaling($tenantId, 'scale_down', $policy['scale_down_count']);
            $this->logger->info("Triggered scale down for tenant $tenantId", $metrics);
        }
    }

    /**
     * Trigger scaling action via load balancer API
     */
    private function triggerScaling(string $tenantId, string $action, int $count): bool
    {
        $data = [
            'tenant_id' => $tenantId,
            'action' => $action,
            'count' => $count,
            'timestamp' => time()
        ];
        
        $ch = curl_init($this->loadBalancerUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $this->logger->error("Failed to trigger scaling action", [
                'tenant' => $tenantId,
                'action' => $action,
                'response' => $response,
                'code' => $httpCode
            ]);
            return false;
        }
        
        return true;
    }
}
