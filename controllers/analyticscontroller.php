<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__.'/../core/database.php';

class AnalyticsController {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function getTenantMetrics($tenantId, $timeRange = '7 DAY') {
        try {
            // Validate inputs
            if (empty($tenantId)) {
                throw new InvalidArgumentException('Tenant ID is required');
            }

            // Query database for metrics
            $query = "SELECT 
                        event_type,
                        DATE(timestamp) as date,
                        COUNT(*) as count
                      FROM tenant_metrics
                      WHERE tenant_id = :tenant_id
                      AND timestamp >= DATE_SUB(NOW(), INTERVAL $timeRange)
                      GROUP BY event_type, DATE(timestamp)
                      ORDER BY date";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':tenant_id', $tenantId);
            $stmt->execute();

            $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'data' => $metrics
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
