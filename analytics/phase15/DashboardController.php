<?php
/**
 * Dashboard Controller for Phase 15 Analytics
 */
class DashboardController {
    private $analyticsService;
    private $accessControlService;
    private $reportService;

    public function __construct() {
        require_once __DIR__ . '/../../services/analyticsservice.php';
        require_once __DIR__ . '/../../services/AccessControlService.php';
        require_once __DIR__ . '/../../services/ReportService.php';
        
        $this->analyticsService = new AnalyticsService();
        $this->accessControlService = new AccessControlService();
        $this->reportService = new ReportService();
    }

    /**
     * Get aggregated analytics data
     */
    public function getAggregatedData(array $params): array {
        $tenantId = $this->accessControlService->validateTenant($params['tenant_id'] ?? '');
        return [
            'status' => 'success',
            'data' => $this->analyticsService->getAggregatedData(['tenant_id' => $tenantId])
        ];
    }

    /**
     * Stream real-time events
     */
    public function getRealtimeEvents(array $params): array {
        $tenantId = $this->accessControlService->validateTenant($params['tenant_id'] ?? '');
        $lastId = $params['last_id'] ?? 0;
        return [
            'events' => $this->analyticsService->getRealtimeEvents($lastId, $tenantId)
        ];
    }

    /**
     * Generate downloadable report
     */
    public function generateReport(array $params): array {
        $reportType = $params['type'] ?? 'standard';
        return [
            'url' => $this->reportService->generate($reportType)
        ];
    }

    /**
     * Verify user access permissions
     */
    public function verifyAccess(array $params): array {
        $userId = $params['user_id'] ?? 0;
        return [
            'has_access' => $this->accessControlService->checkDashboardAccess($userId)
        ];
    }
}
