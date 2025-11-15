<?php

class ConditionEvaluator {
    private $db;
    private $versionedScheduleService;
    private $permissionService;

    public function __construct($db, $versionedScheduleService, $permissionService) {
        $this->db = $db;
        $this->versionedScheduleService = $versionedScheduleService;
        $this->permissionService = $permissionService;
    }

    public function evaluate(array $conditions, int $userId, int $contentId, ?int $versionId = null): array {
        // Validate permissions
        if (!$this->permissionService->hasPermission($userId, 'schedule_content')) {
            return $this->createResult(false, 'Missing schedule_content permission');
        }

        if (!$this->permissionService->hasPermission($userId, 'publish_scheduled_content')) {
            return $this->createResult(false, 'Missing publish_scheduled_content permission');
        }

        // Validate content (simplified - would check actual content model)
        $content = $this->getContent($contentId);
        if (!$content) {
            return $this->createResult(false, 'Content not found');
        }

        if ($content['status'] !== 'draft') {
            return $this->createResult(false, 'Only draft content can be scheduled');
        }

        // Evaluate conditions
        $evaluation = [
            'date_conditions' => $this->evaluateDateConditions($conditions['date'] ?? []),
            'content_conditions' => $this->evaluateContentConditions($conditions['content'] ?? []),
            'user_conditions' => $this->evaluateUserConditions($conditions['user'] ?? [], $userId)
        ];

        // Check for any failed conditions
        $failed = array_filter($evaluation, fn($result) => !$result['valid']);
        if (!empty($failed)) {
            return $this->createResult(false, 'Conditions not met', ['failed_conditions' => $failed]);
        }

        // Check for scheduling conflicts
        if (isset($conditions['date']['publish_at'])) {
            $publishAt = new DateTime($conditions['date']['publish_at']);
            $conflicts = $this->versionedScheduleService->checkForConflicts(
                $contentId,
                $versionId ?? $content['current_version'],
                $publishAt
            );

            if (!empty($conflicts)) {
                return $this->createResult(false, 'Scheduling conflict detected', ['conflicts' => $conflicts]);
            }
        }

        return $this->createResult(true, 'All conditions met', $evaluation);
    }

    private function evaluateDateConditions(array $conditions): array {
        // Validate required date fields
        if (!isset($conditions['publish_at'])) {
            return ['valid' => false, 'message' => 'Missing publish_at date'];
        }

        try {
            $publishAt = new DateTime($conditions['publish_at']);
            $now = new DateTime();

            if ($publishAt < $now) {
                return ['valid' => false, 'message' => 'Publish date must be in the future'];
            }

            return ['valid' => true, 'publish_at' => $publishAt];
        } catch (Exception $e) {
            return ['valid' => false, 'message' => 'Invalid date format'];
        }
    }

    private function evaluateContentConditions(array $conditions): array {
        // Placeholder for content-based conditions
        return ['valid' => true];
    }

    private function evaluateUserConditions(array $conditions, int $userId): array {
        // Placeholder for user-based conditions
        return ['valid' => true];
    }

    private function getContent(int $contentId): ?array {
        // Simplified - would query actual content model
        $stmt = $this->db->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$contentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function createResult(bool $success, string $message, array $details = []): array {
        return [
            'success' => $success,
            'message' => $message,
            'details' => $details
        ];
    }
}
