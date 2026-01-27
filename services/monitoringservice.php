<?php
namespace services;

use core\Database;
use core\Tenant;
use Exception;

class MonitoringService {
    private static $inMemoryMetrics = [];
    private static $usingDatabase = true;

    public static function track(string $metric, float $value): void {
        try {
            if (self::$usingDatabase) {
                $tenantId = Tenant::currentId();
                $sql = "INSERT INTO metrics (tenant_id, metric_name, metric_value) VALUES (?, ?, ?)";
                Database::execute($sql, [$tenantId, $metric, $value]);
            } else {
                self::$inMemoryMetrics[$metric] = $value;
            }
        } catch (Exception $e) {
            // Fallback to in-memory storage if database fails
            self::$usingDatabase = false;
            self::$inMemoryMetrics[$metric] = $value;
            error_log("MonitoringService: Database write failed, using in-memory storage. Error: " . $e->getMessage());
        }
    }

    public static function getMetrics(): array {
        try {
            if (self::$usingDatabase) {
                $tenantId = Tenant::currentId();
                $sql = "SELECT metric_name, metric_value FROM metrics WHERE tenant_id = ?";
                $results = Database::query($sql, [$tenantId]);
                
                $metrics = [];
                foreach ($results as $row) {
                    $metrics[$row['metric_name']] = (float)$row['metric_value'];
                }
                return $metrics;
            }
            return self::$inMemoryMetrics;
        } catch (Exception $e) {
            // Fallback to in-memory storage if database fails
            self::$usingDatabase = false;
            error_log("MonitoringService: Database read failed, using in-memory storage. Error: " . $e->getMessage());
            return self::$inMemoryMetrics;
        }
    }

    public static function getMetric(string $metric): ?float {
        try {
            if (self::$usingDatabase) {
                $tenantId = Tenant::currentId();
                $sql = "SELECT metric_value FROM metrics WHERE tenant_id = ? AND metric_name = ? LIMIT 1";
                $result = Database::query($sql, [$tenantId, $metric]);
                return $result[0]['metric_value'] ?? null;
            }
            return self::$inMemoryMetrics[$metric] ?? null;
        } catch (Exception $e) {
            self::$usingDatabase = false;
            error_log("MonitoringService: Database read failed, using in-memory storage. Error: " . $e->getMessage());
            return self::$inMemoryMetrics[$metric] ?? null;
        }
    }

    public static function reset(): void {
        try {
            if (self::$usingDatabase) {
                $tenantId = Tenant::currentId();
                Database::execute("DELETE FROM metrics WHERE tenant_id = ?", [$tenantId]);
            }
            self::$inMemoryMetrics = [];
        } catch (Exception $e) {
            self::$usingDatabase = false;
            self::$inMemoryMetrics = [];
            error_log("MonitoringService: Database reset failed, cleared in-memory storage. Error: " . $e->getMessage());
        }
    }

    public static function healthCheck(): array {
        $status = self::$usingDatabase ? 'OK' : 'WARNING (using fallback storage)';
        
        return [
            'status' => $status,
            'timestamp' => time(),
            'metrics' => self::getMetrics(),
            'storage_type' => self::$usingDatabase ? 'database' : 'memory'
        ];
    }
}
