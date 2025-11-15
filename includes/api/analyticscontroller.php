<?php
declare(strict_types=1);

class AnalyticsController {
    private $eventProcessor;
    private $collector;

    public function __construct() {
        require_once __DIR__ . '/../analytics/eventprocessor.php';
        require_once __DIR__ . '/../Analytics/Collector.php';
        $this->eventProcessor = new EventProcessor();
        $this->collector = new AnalyticsCollector();
    }

    public function handleEvent(array $data): array {
        $tenantId = $data['tenant_id'] ?? '';
        if (empty($tenantId)) {
            return ['success' => false, 'error' => 'Missing tenant ID'];
        }

        $success = $this->collector->track($data, $tenantId);
        return ['success' => $success];
    }

    public function getSummary(array $params): array {
        $date = $params['date'] ?? date('Y-m-d');
        return $this->eventProcessor->getDailySummary($date);
    }

    public function queryEvents(array $params): array {
        $tenantId = $params['tenant_id'] ?? '';
        $dateRange = [
            'start' => $params['start_date'] ?? date('Y-m-d 00:00:00'),
            'end' => $params['end_date'] ?? date('Y-m-d 23:59:59')
        ];

        if (empty($tenantId)) {
            return ['error' => 'Tenant ID required'];
        }

        return $this->eventProcessor->getTenantSummary($tenantId, $dateRange);
    }

    public function exportData(array $params): void {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="analytics_export.json"');
        echo json_encode($this->queryEvents($params));
        exit;
    }
}
