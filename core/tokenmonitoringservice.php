<?php
require_once __DIR__ . '/database.php';

class TokenMonitoringService {
    private static $instance;
    private $db;
    private $alertThresholds = [
        'critical' => 10000,
        'warning' => 5000,
        'tenant_critical' => 50000,
        'tenant_warning' => 25000
    ];

    private function __construct() {
        $this->db = \core\Database::connection();
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function trackTokenUsage(int $tokens, string $operation, string $endpoint, string $tenantId = null, string $mode = null, string $modelType = null): void {
        $query = "INSERT INTO token_usage
                 (timestamp, tokens_consumed, operation_type, endpoint, tenant_id, mode, model_type)
                 VALUES (NOW(), ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($query, [$tokens, $operation, $endpoint, $tenantId, $mode, $modelType]);

        $this->checkThresholds($tokens, $operation, $tenantId);
    }

    private function checkThresholds(int $tokens, string $operation, ?string $tenantId): void {
        if ($tokens > $this->alertThresholds['critical']) {
            $this->triggerAlert('critical', $operation, $tokens, $tenantId);
        } elseif ($tokens > $this->alertThresholds['warning']) {
            $this->triggerAlert('warning', $operation, $tokens, $tenantId);
        }
        
        if ($tenantId && $tokens > $this->alertThresholds['tenant_critical']) {
            $this->triggerAlert('tenant_critical', $operation, $tokens, $tenantId);
        } elseif ($tenantId && $tokens > $this->alertThresholds['tenant_warning']) {
            $this->triggerAlert('tenant_warning', $operation, $tokens, $tenantId);
        }
    }

    private function triggerAlert(string $level, string $operation, int $tokens, ?string $tenantId): void {
        $message = "Token $level alert: $operation used $tokens tokens";
        if ($tenantId) {
            $message .= " (Tenant: $tenantId)";
        }
        AlertManager::log($level, 'TOKEN_USAGE', $message);
    }

    public function getUsagePatterns(string $timeRange = '7d', ?string $tenantId = null): array {
        $where = $tenantId ? "WHERE tenant_id = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ?"
                          : "WHERE timestamp > DATE_SUB(NOW(), INTERVAL ?";
        $params = $tenantId ? [$tenantId, $timeRange] : [$timeRange];
        
        $query = "SELECT operation_type, endpoint, mode, model_type,
                 SUM(tokens_consumed) as total_tokens,
                 COUNT(*) as operation_count
                 FROM token_usage
                 $where
                 GROUP BY operation_type, endpoint, mode, model_type
                 ORDER BY total_tokens DESC";

        return $this->db->query($query, $params)->fetchAll();
    }

    public function getTenantUsageReport(string $tenantId, string $timeRange = '7d'): array {
        $query = "SELECT
                    DATE(timestamp) as day,
                    SUM(tokens_consumed) as daily_tokens,
                    operation_type,
                    model_type
                 FROM token_usage
                 WHERE tenant_id = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ?)
                 GROUP BY day, operation_type, model_type
                 ORDER BY day DESC";
        
        return $this->db->query($query, [$tenantId, $timeRange])->fetchAll();
    }

    public function setThreshold(string $level, int $value): void {
        if (in_array($level, ['critical', 'warning', 'tenant_critical', 'tenant_warning'])) {
            $this->alertThresholds[$level] = $value;
        }
    }

    public function getVisualizationData(string $timeRange = '7d'): array {
        $data = $this->getUsagePatterns($timeRange);
        return AnalyticsVisualizationService::prepareTokenData($data);
    }
}
