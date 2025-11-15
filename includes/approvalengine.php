<?php
declare(strict_types=1);

class ApprovalEngine {
    private static array $workflows = [];
    private static array $pendingApprovals = [];

    public static function init(): void {
        self::loadWorkflows();
    }

    private static function loadWorkflows(): void {
        // TODO: Load from database via DB Support
        self::$workflows = [
            'default' => [
                'stages' => ['editor', 'manager', 'publisher'],
                'notifications' => true
            ]
        ];
    }

    public static function createApprovalRequest(
        string $contentId,
        string $workflowType = 'default'
    ): string {
        $requestId = uniqid('appr_');
        self::$pendingApprovals[$requestId] = [
            'content_id' => $contentId,
            'workflow' => $workflowType,
            'current_stage' => 0,
            'status' => 'pending'
        ];
        return $requestId;
    }

    public static function approveStage(string $requestId, string $approverId): bool {
        if (!isset(self::$pendingApprovals[$requestId])) {
            return false;
        }

        $request = &self::$pendingApprovals[$requestId];
        $workflow = self::$workflows[$request['workflow']];
        
        if ($request['current_stage'] >= count($workflow['stages']) - 1) {
            $request['status'] = 'approved';
            return true;
        }

        $request['current_stage']++;
        return true;
    }

    public static function getPendingRequests(): array {
        return array_filter(self::$pendingApprovals, 
            fn($r) => $r['status'] === 'pending');
    }
}
