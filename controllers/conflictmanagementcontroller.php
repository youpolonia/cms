<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Content Conflict Management Controller
 */
class ConflictManagementController {
    private ConflictService $conflictService;
    private ContentVersionService $versionService;

    public function __construct(
        ConflictService $conflictService,
        ContentVersionService $versionService
    ) {
        $this->conflictService = $conflictService;
        $this->versionService = $versionService;
    }

    public function detectConflicts(array $request): array {
        if (empty($request['content_id']) || empty($request['tenant_ids'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        return [
            'status' => 'success',
            'data' => $this->conflictService->detectConflicts(
                $request['content_id'],
                $request['tenant_ids']
            )
        ];
    }

    public function getConflictDetails(array $request): array {
        if (empty($request['conflict_id'])) {
            throw new InvalidArgumentException("Missing conflict_id");
        }

        return [
            'status' => 'success',
            'data' => $this->conflictService->getConflictDetails(
                $request['conflict_id']
            )
        ];
    }

    public function trackConflict(array $request): array {
        if (empty($request['content_id']) || empty($request['conflict_data'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        return [
            'status' => 'success',
            'data' => $this->conflictService->trackConflict(
                $request['content_id'],
                $request['conflict_data']
            )
        ];
    }
}
