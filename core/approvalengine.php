<?php
declare(strict_types=1);

/**
 * ApprovalEngine - Handles workflow approval processes
 */
class ApprovalEngine
{
    private array $workflows = [];
    private array $pendingApprovals = [];

    /**
     * Create new approval request
     */
    public function createRequest(
        string $contentId,
        string $requesterId,
        array $stages
    ): string {
        $requestId = uniqid('req_');
        $this->pendingApprovals[$requestId] = [
            'content_id' => $contentId,
            'requester_id' => $requesterId,
            'current_stage' => 0,
            'stages' => $stages,
            'status' => 'pending'
        ];
        
        return $requestId;
    }

    /**
     * Approve current stage and progress workflow
     */
    public function approveStage(string $requestId, string $approverId): bool
    {
        if (!isset($this->pendingApprovals[$requestId])) {
            return false;
        }

        $request = &$this->pendingApprovals[$requestId];
        $request['stages'][$request['current_stage']]['approved_by'] = $approverId;
        $request['current_stage']++;

        if ($request['current_stage'] >= count($request['stages'])) {
            $request['status'] = 'approved';
        }

        return true;
    }

    /**
     * Get current status of approval request
     */
    public function getStatus(string $requestId): ?array
    {
        return $this->pendingApprovals[$requestId] ?? null;
    }

    /**
     * Get all pending approvals for a user
     */
    public function getPendingForUser(string $userId): array
    {
        return array_filter($this->pendingApprovals, function($req) use ($userId) {
            return $req['status'] === 'pending' 
                && $req['stages'][$req['current_stage']]['approver_id'] === $userId;
        });
    }
}
