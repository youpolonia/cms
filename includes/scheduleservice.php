<?php

declare(strict_types=1);

namespace Includes;

require_once CMS_ROOT . '/includes/database/connection.php';
require_once CMS_ROOT . '/includes/versioning/versionmetadata.php';
require_once CMS_ROOT . '/includes/audit/restorationlogger.php';
require_once CMS_ROOT . '/includes/core/MCPMonitor.php';
require_once CMS_ROOT . '/includes/services/recurrenceengine.php';
require_once CMS_ROOT . '/includes/services/conditionevaluator.php';

use Includes\Database\Connection;
use Includes\Versioning\VersionMetadata;
use Includes\audit\RestorationLogger;
use Includes\Core\MCPMonitor;
use Includes\Services\RecurrenceEngine;
use Includes\Services\ConditionEvaluator;

class ScheduleService
{
    private Connection $db;
    private VersionMetadata $versionMetadata;
    private RestorationLogger $logger;
    private MCPMonitor $monitor;
    private RecurrenceEngine $recurrenceEngine;

    public function __construct(RecurrenceEngine $recurrenceEngine = null)
    {
        $this->db = Connection::getInstance();
        $this->versionMetadata = new VersionMetadata();
        $this->logger = new RestorationLogger();
        $this->monitor = new MCPMonitor();
        $this->recurrenceEngine = $recurrenceEngine ?? new RecurrenceEngine(new ConditionEvaluator());
    }

    private function checkPermission(string $permission, int $userId): void
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as has_permission
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE u.id = ? AND p.name = ?
        ");
        $stmt->execute([$userId, $permission]);
        $result = $stmt->fetch();

        if (empty($result['has_permission'])) {
            throw new \RuntimeException('Permission denied');
        }
    }

    public function scheduleContent(
        int $contentId,
        int $versionId,
        \DateTimeInterface $publishAt,
        int $userId
    ): int {
        $this->checkPermission('schedule.create', $userId);
        $this->validateVersion($contentId, $versionId);

        $stmt = $this->db->prepare(
            "INSERT INTO scheduled_events 
            (content_id, version_id, scheduled_at, status) 
            VALUES (:content_id, :version_id, :scheduled_at, 'pending')"
        );

        $stmt->execute([
            ':content_id' => $contentId,
            ':version_id' => $versionId,
            ':scheduled_at' => $publishAt->format('Y-m-d H:i:s')
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function publishScheduledContent(int $scheduleId, int $userId): bool
    {
        $this->checkPermission('schedule.publish', $userId);
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM scheduled_events 
                WHERE id = :id AND status = 'pending' 
                FOR UPDATE"
            );
            $stmt->execute([':id' => $scheduleId]);
            $event = $stmt->fetch();

            if (!$event) {
                throw new \RuntimeException('Scheduled event not found or already processed');
            }

            $this->versionMetadata->updateCurrentVersion(
                $event['content_id'],
                $event['version_id']
            );

            $stmt = $this->db->prepare(
                "UPDATE scheduled_events 
                SET status = 'published' 
                WHERE id = :id"
            );
            $stmt->execute([':id' => $scheduleId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->markAsFailed($scheduleId, $e->getMessage());
            throw $e;
        }
    }

    public function checkConflicts(int $contentId, \DateTimeInterface $publishAt): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, scheduled_at 
            FROM scheduled_events 
            WHERE content_id = :content_id 
            AND status = 'pending'
            AND scheduled_at BETWEEN 
                :start_range AND :end_range"
        );

        $startRange = (clone $publishAt)->modify('-1 hour');
        $endRange = (clone $publishAt)->modify('+1 hour');

        $stmt->execute([
            ':content_id' => $contentId,
            ':start_range' => $startRange->format('Y-m-d H:i:s'),
            ':end_range' => $endRange->format('Y-m-d H:i:s')
        ]);

        return $stmt->fetchAll();
    }

    public function batchSchedule(array $items, int $userId): array
    {
        $this->checkPermission('schedule.create', $userId);
        $this->db->beginTransaction();
        $results = [];

        try {
            foreach ($items as $item) {
                $this->validateVersion($item['content_id'], $item['version_id']);

                $stmt = $this->db->prepare(
                    "INSERT INTO scheduled_events 
                    (content_id, version_id, scheduled_at, status) 
                    VALUES (:content_id, :version_id, :scheduled_at, 'pending')"
                );

                $stmt->execute([
                    ':content_id' => $item['content_id'],
                    ':version_id' => $item['version_id'],
                    ':scheduled_at' => $item['publish_at']->format('Y-m-d H:i:s')
                ]);

                $id = (int)$this->db->lastInsertId();
                $results[] = ['id' => $id, 'status' => 'success'];
            }

            $this->db->commit();
            $this->monitor->logMetric('scheduled_batch', count($items));
            return $results;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->logError('batch_schedule_failed', [
                'error' => $e->getMessage(),
                'items' => $items
            ]);
            throw $e;
        }
    }

    public function processPendingBatch(int $batchSize = 100): array
    {
        $this->monitor->startOperation('process_scheduled_batch');
        $results = ['processed' => 0, 'success' => 0, 'failed' => 0];

        $stmt = $this->db->prepare(
            "SELECT id, content_id, version_id 
            FROM scheduled_events 
            WHERE status = 'pending' 
            AND scheduled_at <= NOW() 
            ORDER BY scheduled_at ASC 
            LIMIT ?"
        );
        $stmt->execute([$batchSize]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            try {
                $this->publishScheduledContent($item['id'], 0); // Assuming system user or no specific user for batch
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $this->logger->logError('batch_publish_failed', [
                    'schedule_id' => $item['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $results['processed'] = count($items);
        $this->monitor->endOperation('process_scheduled_batch', $results);
        return $results;
    }

    public function getBatchStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                status, 
                COUNT(*) as count,
                MIN(scheduled_at) as oldest,
                MAX(scheduled_at) as newest
            FROM scheduled_events
            GROUP BY status"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function scheduleRecurringContent(
        int $contentId,
        int $versionId,
        \DateTimeInterface $startAt,
        string $recurrencePattern,
        array $recurrenceParams,
        int $userId,
        array $conditions = []
    ): array {
        $this->checkPermission('schedule.create', $userId);
        $this->validateVersion($contentId, $versionId);

        return $this->recurrenceEngine->scheduleRecurringContent(
            $contentId,
            $versionId,
            $startAt,
            $recurrencePattern,
            $recurrenceParams,
            $userId,
            $conditions
        );
    }

    private function validateVersion(int $contentId, int $versionId): void
    {
        $version = $this->versionMetadata->getMetadata($versionId);
        if (!$version || $version['content_id'] !== $contentId) {
            throw new \InvalidArgumentException('Version does not belong to content');
        }
    }

    private function markAsFailed(int $scheduleId, string $error): void
    {
        $stmt = $this->db->prepare(
            "UPDATE scheduled_events
            SET status = 'failed', error_message = :error
            WHERE id = :id"
        );
        $stmt->execute([
            ':id' => $scheduleId,
            ':error' => $error
        ]);
    }

    public function scheduleNotification(
        string $title,
        string $message,
        int $userId,
        \DateTimeInterface $publishAt,
        int $scheduledBy
    ): int {
        $this->checkPermission('schedule.create', $userId);

        $stmt = $this->db->prepare(
            "INSERT INTO scheduled_events
            (notification_title, notification_message, user_id, scheduled_at, status, created_by)
            VALUES (:title, :message, :user_id, :scheduled_at, 'pending', :created_by)"
        );

        $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':user_id' => $userId,
            ':scheduled_at' => $publishAt->format('Y-m-d H:i:s'),
            ':created_by' => $scheduledBy
        ]);

        return (int)$this->db->lastInsertId();
    }
}
