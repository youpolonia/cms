# Phase10 Database Implementation Plan

## 1. Schema Changes
```sql
-- Performance metrics table
CREATE TABLE IF NOT EXISTS tenant_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    metric_name VARCHAR(255) NOT NULL,
    metric_value DECIMAL(12,4) NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_metric (metric_name)
);

-- Alert thresholds table
CREATE TABLE IF NOT EXISTS alert_thresholds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    metric_name VARCHAR(255) NOT NULL,
    warning_value DECIMAL(12,4) NOT NULL,
    critical_value DECIMAL(12,4) NOT NULL,
    INDEX idx_tenant_metric (tenant_id, metric_name)
);
```

## 2. Migration Implementation
```php
<?php
class Phase10MetricsMigration {
    public static function migrate(\PDO $pdo): bool {
        try {
            $pdo->beginTransaction();
            
            // Create metrics table
            $pdo->exec("CREATE TABLE IF NOT EXISTS tenant_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT NOT NULL,
                metric_name VARCHAR(255) NOT NULL,
                metric_value DECIMAL(12,4) NOT NULL,
                recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_metric (metric_name)
            )");

            // Create thresholds table
            $pdo->exec("CREATE TABLE IF NOT EXISTS alert_thresholds (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT NOT NULL,
                metric_name VARCHAR(255) NOT NULL,
                warning_value DECIMAL(12,4) NOT NULL,
                critical_value DECIMAL(12,4) NOT NULL,
                INDEX idx_tenant_metric (tenant_id, metric_name)
            )");

            $pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $pdo->rollBack();
            error_log("Migration failed: " . $e->getMessage());
            return false;
        }
    }

    public static function rollback(\PDO $pdo): bool {
        try {
            $pdo->beginTransaction();
            $pdo->exec("DROP TABLE IF EXISTS tenant_metrics");
            $pdo->exec("DROP TABLE IF EXISTS alert_thresholds");
            $pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $pdo->rollBack();
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
}
?>
```

## 3. Testing Strategy
1. **Unit Tests**:
   - Verify table creation
   - Test metric insertion
   - Validate threshold configurations

2. **Integration Tests**:
   - Test with existing tenant isolation
   - Verify dashboard data aggregation
   - Test alert triggering logic

3. **Performance Tests**:
   - Benchmark with high metric volumes
   - Test aggregation queries

## 4. Web Testing Endpoints
Create `/public/api/test/metrics_test.php` with:
- `/migrate` - Run migration
- `/rollback` - Rollback changes
- `/test` - Full test sequence
- `/cleanup` - Remove test data

## 5. Documentation Requirements
1. Update `memory-bank/decisionLog.md` with:
   - Schema decisions
   - Index strategy
   - Testing approach

2. Create API documentation for:
   - Metrics collection endpoints
   - Threshold configuration
   - Dashboard data retrieval

## 6. Compliance Verification
- [x] Framework-free PHP 8.1+
- [x] Transaction support
- [x] Proper error handling
- [x] Web-accessible testing
- [x] Tenant isolation maintained