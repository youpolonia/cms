<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Approval Workflow Controller
 * Handles state transitions for content approval workflows
 */
class ApprovalWorkflowController {
    // Workflow states
    const STATE_DRAFT = 'draft';
    const STATE_PENDING = 'pending_review';
    const STATE_APPROVED = 'approved';
    const STATE_REJECTED = 'rejected';
    const STATE_PUBLISHED = 'published';

    /**
     * Transition workflow state
     * @param string $currentState Current workflow state
     * @param string $action Requested action
     * @return string New state
     * @throws Exception On invalid transition
     */
    public static function transitionState(string $currentState, string $action): string {
        $transitions = [
            self::STATE_DRAFT => [
                'submit' => self::STATE_PENDING
            ],
            self::STATE_PENDING => [
                'approve' => self::STATE_APPROVED,
                'reject' => self::STATE_REJECTED
            ],
            self::STATE_APPROVED => [
                'publish' => self::STATE_PUBLISHED,
                'reject' => self::STATE_REJECTED
            ],
            self::STATE_REJECTED => [
                'resubmit' => self::STATE_PENDING,
                'withdraw' => self::STATE_DRAFT
            ]
        ];

        if (!isset($transitions[$currentState][$action])) {
            throw new Exception("Invalid transition: $currentState -> $action");
        }

        return $transitions[$currentState][$action];
    }

    /**
     * Create new approval request
     * @param int $contentId Content ID to approve
     * @param int $userId User ID making request
     * @return array Created request data
     */
    public static function createRequest(int $contentId, int $userId): array {
        // In real implementation would save to database
        return [
            'request_id' => uniqid(),
            'content_id' => $contentId,
            'user_id' => $userId,
            'state' => self::STATE_PENDING,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Process approval action
     * @param string $requestId Approval request ID
     * @param int $approverId User ID approving
     * @param string $action Action to take (approve/reject)
     * @return array Updated request data
     */
    public static function processApproval(string $requestId, int $approverId, string $action): array {
        // In real implementation would load from database
        $request = [
            'request_id' => $requestId,
            'state' => self::STATE_PENDING
        ];

        $request['state'] = self::transitionState($request['state'], $action);
        $request['processed_at'] = date('Y-m-d H:i:s');
        $request['approver_id'] = $approverId;

        return $request;
    }

    /**
     * Get pending requests
     * @param int|null $userId Optional user ID filter
     * @return array List of pending requests
     */
    public static function getPendingRequests(?int $userId = null): array {
        // Stub implementation - would query database
        return [];
    }
}
