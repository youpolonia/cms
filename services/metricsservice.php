<?php
/**
 * Phase10 Metrics Collection Service
 */
class MetricsService {
    private \PDO $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function recordMetric(int $tenantId, string $metricName, float $value): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO tenant_metrics 
                (tenant_id, metric_name, metric_value) 
                VALUES (:tenant_id, :metric_name, :metric_value)"
            );
            
            return $stmt->execute([
                ':tenant_id' => $tenantId,
                ':metric_name' => $metricName,
                ':metric_value' => $value
            ]);
        } catch (\PDOException $e) {
            error_log("MetricsService error: " . $e->getMessage());
            return false;
        }
    }
    
    public function setThreshold(
        int $tenantId,
        string $metricName,
        float $warningValue,
        float $criticalValue
    ): bool {
        try {
            $stmt = $this->pdo->prepare(
                "REPLACE INTO alert_thresholds
                (tenant_id, metric_name, warning_value, critical_value)
                VALUES (:tenant_id, :metric_name, :warning_value, :critical_value)"
            );
            
            return $stmt->execute([
                ':tenant_id' => $tenantId,
                ':metric_name' => $metricName,
                ':warning_value' => $warningValue,
                ':critical_value' => $criticalValue
            ]);
        } catch (\PDOException $e) {
            error_log("MetricsService threshold error: " . $e->getMessage());
            return false;
        }
    }

    public function cleanupOldMetrics(): bool {
        try {
            $stmt = $this->pdo->prepare("CALL cleanup_old_metrics()");
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Metrics cleanup error: " . $e->getMessage());
            return false;
        }
    }

    public function getMetricsHistory(
        int $tenantId,
        string $metricName,
        int $days = 30
    ): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT metric_value, created_at
                FROM tenant_metrics
                WHERE tenant_id = :tenant_id
                AND metric_name = :metric_name
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                ORDER BY created_at DESC"
            );
            
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':metric_name' => $metricName,
                ':days' => $days
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Metrics history error: " . $e->getMessage());
            return [];
        }
    }
}
