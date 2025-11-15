<?php

declare(strict_types=1);

namespace Api\v1\Controllers;

use Includes\Services\BatchScheduleService;
use Includes\Routing\Response;
use Includes\Routing\Request;
use Includes\Core\Auth;

class BatchScheduleController {
    private $batchScheduleService;
    private $auth;

    public function __construct(BatchScheduleService $batchScheduleService, Auth $auth) {
        $this->batchScheduleService = $batchScheduleService;
        $this->auth = $auth;
    }

    public function scheduleBatch(Request $request): Response {
        $data = $request->getJsonBody();
        
        if (empty($data['items'])) {
            return new Response(['error' => 'No items provided'], 400);
        }

        if (!$this->auth->hasPermission('batch_schedule')) {
            return new Response(['error' => 'Insufficient permissions'], 403);
        }

        if (count($data['items']) > 50) {
            return new Response(['error' => 'Maximum 50 items per batch'], 400);
        }

        try {
            $results = $this->batchScheduleService->processLargeBatch($data['items']);
            return new Response([
                'results' => $results,
                'batch_id' => uniqid('batch_')
            ]);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function checkConflicts(Request $request): Response {
        $data = $request->getJsonBody();
        
        if (empty($data['items'])) {
            return new Response(['error' => 'No items provided'], 400);
        }

        try {
            $conflicts = $this->batchScheduleService->checkBatchConflicts($data['items']);
            return new Response(['conflicts' => $conflicts]);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function getStatus(Request $request): Response {
        $contentIds = $request->getQueryParams()['content_ids'] ?? [];
        
        if (empty($contentIds)) {
            return new Response(['error' => 'No content IDs provided'], 400);
        }

        try {
            $statuses = $this->batchScheduleService->getBatchStatus($contentIds);
            return new Response(['statuses' => $statuses]);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }

    public function getBatchProgress(Request $request): Response {
        $batchId = $request->getQueryParams()['batch_id'] ?? '';
        
        if (empty($batchId)) {
            return new Response(['error' => 'No batch ID provided'], 400);
        }

        try {
            $progress = $this->batchScheduleService->getBatchProgress($batchId);
            return new Response(['progress' => $progress]);
        } catch (\Exception $e) {
            return new Response(['error' => $e->getMessage()], 500);
        }
    }
}
