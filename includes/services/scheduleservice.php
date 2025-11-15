<?php

declare(strict_types=1);

namespace Includes\Services;

use Exception;

class ScheduleService {
    protected $db;
    protected $permissionService;
    protected $conditionEvaluator;

    public function __construct($db, $permissionService, ConditionEvaluator $conditionEvaluator) {
        $this->db = $db;
        $this->permissionService = $permissionService;
        $this->conditionEvaluator = $conditionEvaluator;
    }

    protected function beginTransaction(): void {
        $this->db->beginTransaction();
    }

    protected function commit(): void {
        $this->db->commit();
    }

    protected function rollBack(): void {
        $this->db->rollBack();
    }

    protected function prepare(string $sql) {
        return $this->db->prepare($sql);
    }

    protected function getDbError(): ?string {
        return $this->db->errorInfo()[2] ?? null;
    }

    public function scheduleContent($contentId, $scheduledAt, $userId, array $conditions = []) {
        if (!$this->permissionService->canScheduleContent($userId)) {
            throw new Exception('Permission denied');
        }

        $context = $this->buildEvaluationContext($contentId, $userId);
        
        if (!empty($conditions)) {
            if (!$this->conditionEvaluator->evaluate($conditions, $context)) {
                throw new Exception('Content scheduling conditions not met');
            }
        }

        $stmt = $this->prepare(
            "INSERT INTO scheduled_events
            (content_id, version_id, scheduled_at, status, conditions)
            VALUES (?, (SELECT id FROM content_versions WHERE content_id = ? ORDER BY created_at DESC LIMIT 1), ?, 'pending', ?)"
        );
        
        return $stmt->execute([
            $contentId,
            $contentId,
            $scheduledAt,
            json_encode($conditions)
        ]);
    }

    public function getScheduledEvents($userId, $limit = 100) {
        if (!$this->permissionService->canViewScheduledContent($userId)) {
            throw new Exception('Permission denied');
        }

        $stmt = $this->prepare(
            "SELECT * FROM scheduled_events
             WHERE status = 'pending'
             ORDER BY scheduled_at ASC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        
        $events = $stmt->fetchAll();
        foreach ($events as &$event) {
            $event['conditions'] = json_decode($event['conditions'] ?? '[]', true);
        }
        
        return $events;
    }

    private function buildEvaluationContext(int $contentId, int $userId): array
    {
        return [
            'content_id' => $contentId,
            'user_id' => $userId,
            'timestamp' => time()
        ];
    }
}
