<?php
/**
 * Batch Processing Controller
 * Pure PHP implementation - no framework dependencies
 */

class BatchProcessingController {
    private $batchJob;
    private $batchJobItem;

    public function __construct() {
        $this->batchJob = new BatchJob();
        $this->batchJobItem = new BatchJobItem();
    }

    public function createJob($request) {
        try {
            $data = json_decode($request['body'], true);
            
            // Validate required fields
            if (empty($data['type'])) {
                throw new Exception("Type is required");
            }

            $jobId = $this->batchJob->create([
                'type' => $data['type'],
                'payload' => $data['payload'] ?? []
            ]);

            return [
                'status' => 201,
                'body' => json_encode([
                    'id' => $jobId,
                    'message' => 'Batch job created successfully'
                ])
            ];
        } catch (Exception $e) {
            return [
                'status' => 400,
                'body' => json_encode([
                    'error' => $e->getMessage()
                ])
            ];
        }
    }

    public function getJobStatus($request) {
        try {
            $jobId = $request['params']['id'] ?? null;
            if (!$jobId) {
                throw new Exception("Job ID is required");
            }

            $job = $this->batchJob->getById($jobId);
            if (!$job) {
                throw new Exception("Job not found");
            }

            return [
                'status' => 200,
                'body' => json_encode($job)
            ];
        } catch (Exception $e) {
            return [
                'status' => 404,
                'body' => json_encode([
                    'error' => $e->getMessage()
                ])
            ];
        }
    }

    public function addJobItem($request) {
        try {
            $jobId = $request['params']['id'] ?? null;
            $data = json_decode($request['body'], true);
            
            if (!$jobId || empty($data['item_id'])) {
                throw new Exception("Job ID and Item ID are required");
            }

            $itemId = $this->batchJobItem->create(
                $jobId,
                $data['item_id'],
                $data
            );

            return [
                'status' => 201,
                'body' => json_encode([
                    'id' => $itemId,
                    'message' => 'Job item added successfully'
                ])
            ];
        } catch (Exception $e) {
            return [
                'status' => 400,
                'body' => json_encode([
                    'error' => $e->getMessage()
                ])
            ];
        }
    }

    public function getJobItems($request) {
        try {
            $jobId = $request['params']['id'] ?? null;
            $status = $request['query']['status'] ?? null;
            
            if (!$jobId) {
                throw new Exception("Job ID is required");
            }

            $items = $this->batchJobItem->getItemsByJob($jobId, $status);

            return [
                'status' => 200,
                'body' => json_encode($items)
            ];
        } catch (Exception $e) {
            return [
                'status' => 400,
                'body' => json_encode([
                    'error' => $e->getMessage()
                ])
            ];
        }
    }
}
