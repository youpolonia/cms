<?php
class AuditLogController {
    private AuditLogService $auditLogService;
    private AuthService $auth;

    public function __construct(AuditLogService $auditLogService, AuthService $auth) {
        $this->auditLogService = $auditLogService;
        $this->auth = $auth;
    }

    public function getFilteredLogs(): void {
        if (!$this->auth->currentUserHasPermission('view_audit_logs')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'limit' => min(100, $_GET['limit'] ?? 50),
            'offset' => $_GET['offset'] ?? 0
        ];

        $logs = $this->auditLogService->getLogs($filters);
        echo json_encode(['logs' => $logs]);
    }

    public function getAvailableFilters(): void {
        if (!$this->auth->currentUserHasPermission('view_audit_logs')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $filters = [
            'actions' => $this->auditLogService->getDistinctActions(),
            'users' => $this->auditLogService->getRecentUsers(),
            'date_range' => [
                'min' => $this->auditLogService->getOldestLogDate(),
                'max' => $this->auditLogService->getNewestLogDate()
            ]
        ];

        echo json_encode($filters);
    }
}
