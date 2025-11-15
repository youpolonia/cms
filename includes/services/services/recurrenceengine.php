<?php

declare(strict_types=1);

namespace Includes\Services;

use Includes\Database\Connection;
use Includes\Services\ConditionEvaluator;

class RecurrenceEngine
{
    private Connection $db;
    private ConditionEvaluator $conditionEvaluator;

    public function __construct(ConditionEvaluator $conditionEvaluator)
    {
        $this->db = Connection::getInstance();
        $this->conditionEvaluator = $conditionEvaluator;
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
        $this->validateConditions($conditions);

        $scheduleId = $this->createRecurringSchedule(
            $contentId,
            $versionId,
            $startAt,
            $recurrencePattern,
            $recurrenceParams,
            $userId
        );

        return [
            'schedule_id' => $scheduleId,
            'next_occurrences' => $this->calculateNextOccurrences(
                $startAt,
                $recurrencePattern,
                $recurrenceParams,
                5 // Return next 5 occurrences
            )
        ];
    }

    private function validateConditions(array $conditions): void
    {
        if (!empty($conditions)) {
            $this->conditionEvaluator->validate($conditions);
        }
    }

    private function createRecurringSchedule(
        int $contentId,
        int $versionId,
        \DateTimeInterface $startAt,
        string $recurrencePattern,
        array $recurrenceParams,
        int $userId
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO recurring_schedules 
            (content_id, version_id, start_at, recurrence_pattern, recurrence_params, created_by) 
            VALUES (:content_id, :version_id, :start_at, :recurrence_pattern, :recurrence_params, :created_by)"
        );

        $stmt->execute([
            ':content_id' => $contentId,
            ':version_id' => $versionId,
            ':start_at' => $startAt->format('Y-m-d H:i:s'),
            ':recurrence_pattern' => $recurrencePattern,
            ':recurrence_params' => json_encode($recurrenceParams),
            ':created_by' => $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function calculateNextOccurrences(
        \DateTimeInterface $startAt,
        string $recurrencePattern,
        array $recurrenceParams,
        int $count
    ): array {
        $occurrences = [];
        $current = clone $startAt;

        for ($i = 0; $i < $count; $i++) {
            $current = $this->applyRecurrence($current, $recurrencePattern, $recurrenceParams);
            $occurrences[] = $current->format('Y-m-d H:i:s');
        }

        return $occurrences;
    }

    private function applyRecurrence(
        \DateTimeInterface $date,
        string $pattern,
        array $params
    ): \DateTimeInterface {
        $newDate = clone $date;

        switch ($pattern) {
            case 'daily':
                $newDate->modify('+1 day');
                break;
            case 'weekly':
                $newDate->modify('+1 week');
                break;
            case 'monthly':
                $newDate->modify('+1 month');
                break;
            case 'custom':
                if (isset($params['interval'])) {
                    $newDate->modify($params['interval']);
                }
                break;
        }

        return $newDate;
    }
}
