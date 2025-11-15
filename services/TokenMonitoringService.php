<?php
require_once __DIR__ . '/../config.php';

/**
 * Token Monitoring Service
 * Tracks and analyzes token usage across the system
 */
class TokenMonitoringService {
    private static $instance;
    private $db;

    private function __construct() {
        $this->db = \core\Database::connection();
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Records token usage for an operation
     */
    public static function recordUsage(
        int $tokens, 
        string $operationType,
        string $endpoint,
        ?string $tenantId = null
    ): bool {
        $db = \core\Database::connection();
        
        $query = "INSERT INTO token_usage 
                 (tokens_consumed, operation_type, endpoint, tenant_id, created_at)
                 VALUES (?, ?, ?, ?, NOW())";
        
        return $db->execute($query, [
            $tokens,
            $operationType,
            $endpoint,
            $tenantId
        ]);
    }

    /**
     * Gets token usage trends for a period
     */
    public static function getUsageTrends(
        DateTime $start, 
        DateTime $end,
        ?string $tenantId = null
    ): array {
        $db = \core\Database::connection();
        $params = [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
        
        $tenantFilter = '';
        if ($tenantId) {
            $tenantFilter = " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        $query = "SELECT 
                    DATE(created_at) as day,
                    SUM(tokens_consumed) as total_tokens,
                    operation_type
                  FROM token_usage
                  WHERE created_at BETWEEN ? AND ?
                  $tenantFilter
                  GROUP BY day, operation_type
                  ORDER BY day";

        return $db->fetchAll($query, $params);
    }

    /**
     * Checks if usage exceeds thresholds
     */
    public static function checkThresholds(
        string $operationType,
        int $currentUsage
    ): bool {
        $thresholds = Config::get('token_thresholds');
        return $currentUsage >= ($thresholds[$operationType] ?? 0);
    }
}
