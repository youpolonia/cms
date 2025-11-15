<?php

declare(strict_types=1);

namespace Includes\Services;

use Exception;
use PDO;

class BatchScheduleService {
    protected $db;
    protected $scheduleService;
    protected $versionedScheduleService;
    protected $batchSize = 100;

    public function __construct(
        PDO $db,
        ScheduleService $scheduleService,
        VersionedScheduleService $versionedScheduleService,
        int $batchSize = 100
    ) {
        $this->db = $db;
        $this->scheduleService = $scheduleService;
        $this->versionedScheduleService = $versionedScheduleService;
        $this->batchSize = $batchSize;
    }

    public function processBatch(array $batchData): array {
        $results = [];
        $this->db->beginTransaction();

        try {
            foreach ($batchData as $item) {
                try {
                    if (isset($item['version_id'])) {
                        $result = $this->versionedScheduleService->scheduleWithVersion(
                            $item['content_id'],
                            $item['version_id'],
                            $item['scheduled_at']
                        );
                    } else {
                        $result = $this->scheduleService->scheduleContent(
                            $item['content_id'],
                            $item['scheduled_at'],
                            $item['user_id'],
                            $item['conditions'] ?? []
                        );
                    }

                    $results[] = [
                        'content_id' => $item['content_id'],
                        'success' => true,
                        'message' => 'Scheduled successfully'
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'content_id' => $item['content_id'],
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $results;
    }

    public function processLargeBatch(array $batchData): array {
        $allResults = [];
        $batches = array_chunk($batchData, $this->batchSize);

        foreach ($batches as $batch) {
            $allResults = array_merge(
                $allResults,
                $this->processBatch($batch)
            );
        }

        return $allResults;
    }

    public function checkBatchConflicts(array $batchData): array {
        $conflicts = [];

        foreach ($batchData as $item) {
            if (isset($item['version_id'])) {
                $conflicts[$item['content_id']] = 
                    $this->versionedScheduleService->checkForConflicts(
                        $item['content_id'],
                        $item['version_id'],
                        $item['scheduled_at']
                    );
            }
        }

        return array_filter($conflicts);
    }

    public function getBatchStatus(array $contentIds): array {
        if (empty($contentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($contentIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT
                content_id,
                status,
                scheduled_at,
                processed_at,
                error_message
             FROM scheduled_events
             WHERE content_id IN ($placeholders)
             ORDER BY scheduled_at DESC"
        );
        $stmt->execute($contentIds);
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['content_id']] = [
                'status' => $row['status'],
                'scheduled_at' => $row['scheduled_at'],
                'processed_at' => $row['processed_at'],
                'error_message' => $row['error_message']
            ];
        }

        // Fill in missing content IDs with 'not_scheduled' status
        foreach ($contentIds as $id) {
            if (!isset($results[$id])) {
                $results[$id] = [
                    'status' => 'not_scheduled',
                    'scheduled_at' => null,
                    'processed_at' => null,
                    'error_message' => null
                ];
            }
        }

        return $results;
    }

    public function getBatchProgress(string $batchId): array {
        // Get total items in batch
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total
             FROM batch_items
             WHERE batch_id = ?"
        );
        $stmt->execute([$batchId]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Get completed items count
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as completed
             FROM batch_items bi
             JOIN scheduled_events se ON bi.content_id = se.content_id
             WHERE bi.batch_id = ?
             AND se.status IN ('published', 'failed')"
        );
        $stmt->execute([$batchId]);
        $completed = $stmt->fetch(PDO::FETCH_ASSOC)['completed'] ?? 0;

        // Get failed items count
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as failed
             FROM batch_items bi
             JOIN scheduled_events se ON bi.content_id = se.content_id
             WHERE bi.batch_id = ?
             AND se.status = 'failed'"
        );
        $stmt->execute([$batchId]);
        $failed = $stmt->fetch(PDO::FETCH_ASSOC)['failed'] ?? 0;

        return [
            'batch_id' => $batchId,
            'progress' => $completed,
            'total' => $total,
            'failed' => $failed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
        ];
    }
}
