<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Content Resolution Approval Controller
 */
class ResolutionApprovalController {
    private ResolutionService $resolutionService;
    private ApprovalService $approvalService;

    public function __construct(
        ResolutionService $resolutionService,
        ApprovalService $approvalService
    ) {
        $this->resolutionService = $resolutionService;
        $this->approvalService = $approvalService;
    }

    public function resolveConflict(array $request): array {
        if (empty($request['conflict_id']) || empty($request['strategy'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        return [
            'status' => 'success',
            'data' => $this->resolutionService->resolveConflict(
                $request['conflict_id'],
                $request['strategy'],
                $request['resolution_data'] ?? []
            )
        ];
    }

    public function approveResolution(array $request): array {
        if (empty($request['resolution_id']) || empty($request['approver_id'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        return [
            'status' => 'success',
            'data' => $this->approvalService->approveResolution(
                $request['resolution_id'],
                $request['approver_id'],
                $request['notes'] ?? ''
            )
        ];
    }

    public function getResolutionHistory(array $request): array {
        if (empty($request['content_id'])) {
            throw new InvalidArgumentException("Missing content_id");
        }

        return [
            'status' => 'success',
            'data' => $this->resolutionService->getResolutionHistory(
                $request['content_id']
            )
        ];
    }
}
