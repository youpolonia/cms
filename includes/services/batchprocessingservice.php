<?php

namespace Includes\Services;

use Includes\Database\DatabaseConnection;
use Includes\Database\QueryBuilder;
use Includes\ErrorHandler;

class BatchProcessingService
{
    private $db;
    private $queryBuilder;

    public function __construct()
    {
        $this->db = \core\Database::connection();
        $this->queryBuilder = new QueryBuilder();
    }

    /**
     * Get all batches with their status
     */
    public function getAllBatches()
    {
        try {
            return $this->queryBuilder
                ->table('batch_processing')
                ->select(['id', 'status', 'progress', 'created_at'])
                ->orderBy('created_at', 'DESC')
                ->get();
        } catch (\Exception $e) {
            ErrorHandler::logError('BatchProcessingService', $e->getMessage());
            throw new \Exception('Failed to retrieve batch status');
        }
    }

    /**
     * Get progress details for a specific batch
     */
    public function getBatchProgress($batchId)
    {
        try {
            $batch = $this->queryBuilder
                ->table('batch_processing')
                ->where('id', '=', $batchId)
                ->first();

            if (!$batch) {
                throw new \Exception('Batch not found');
            }

            return [
                'id' => $batch['id'],
                'status' => $batch['status'],
                'progress' => $batch['progress'],
                'total_items' => $batch['total_items'],
                'processed_items' => $batch['processed_items'],
                'error_count' => $batch['error_count'],
                'last_error' => $batch['last_error'],
                'created_at' => $batch['created_at']
            ];
        } catch (\Exception $e) {
            ErrorHandler::logError('BatchProcessingService', $e->getMessage());
            throw new \Exception('Failed to retrieve batch progress');
        }
    }

    /**
     * Request cancellation of a batch
     */
    public function cancelBatch($batchId)
    {
        try {
            $result = $this->queryBuilder
                ->table('batch_processing')
                ->where('id', '=', $batchId)
                ->whereIn('status', ['pending', 'processing'])
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            return $result > 0;
        } catch (\Exception $e) {
            ErrorHandler::logError('BatchProcessingService', $e->getMessage());
            throw new \Exception('Failed to cancel batch');
        }
    }

    /**
     * Process batch items (to be called from scheduled task)
     */
    public function processBatchItems($batchId, $items)
    {
        // Implementation would go here
    }
}
