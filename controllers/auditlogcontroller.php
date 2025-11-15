<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Audit Log Management API Controller
 */
class AuditLogController {
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService) {
        $this->auditLogService = $auditLogService;
    }

    public function getLogs(array $request): array {
        $filters = [
            'user_id' => $request['user_id'] ?? null,
            'action' => $request['action'] ?? null,
            'date_from' => $request['date_from'] ?? null,
            'date_to' => $request['date_to'] ?? null,
            'limit' => isset($request['limit']) ? (int)$request['limit'] : 50,
            'offset' => isset($request['offset']) ? (int)$request['offset'] : 0
        ];

        return [
            'status' => 'success',
            'data' => $this->auditLogService->getLogs($filters)
        ];
    }

    public function getLogDetails(int $logId): array {
        $log = $this->auditLogService->getLogDetails($logId);
        if (!$log) {
            throw new RuntimeException("Log entry not found");
        }

        return [
            'status' => 'success',
            'data' => $log
        ];
    }

    public function clearOldLogs(int $daysToKeep = 30): array {
        $count = $this->auditLogService->clearOldLogs($daysToKeep);
        return [
            'status' => 'success',
            'message' => "Cleared $count old log entries"
        ];
    }

    public function exportLogs(array $request): array {
        $filters = [
            'user_id' => $request['user_id'] ?? null,
            'action' => $request['action'] ?? null,
            'date_from' => $request['date_from'] ?? null,
            'date_to' => $request['date_to'] ?? null
        ];

        $format = $request['format'] ?? 'csv';
        $filePath = $this->auditLogService->exportLogs($filters, $format);

        return [
            'status' => 'success',
            'data' => [
                'download_url' => '/downloads/' . basename($filePath)
            ]
        ];
    }
}
