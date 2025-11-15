<?php

class VersionedScheduleService extends ScheduleService {
    protected $versionControlService;

    public function __construct($db, $permissionService, ConditionEvaluator $conditionEvaluator, $versionControlService) {
        parent::__construct($db, $permissionService, $conditionEvaluator);
        $this->versionControlService = $versionControlService;
    }

    public function scheduleWithVersion(int $contentId, int $versionId, DateTime $scheduledAt): bool {
        // Verify version exists and belongs to content
        if (!$this->versionControlService->versionExists($contentId, $versionId)) {
            throw new InvalidArgumentException("Version does not exist for this content");
        }

        return $this->createSchedule($contentId, $versionId, $scheduledAt);
    }

    public function batchScheduleWithVersions(array $contentIds, array $versionIds, array $scheduledAts): array {
        $results = [];
        $this->beginTransaction();
        
        try {
            foreach ($contentIds as $index => $contentId) {
                $versionId = $versionIds[$index];
                $scheduledAt = $scheduledAts[$index];
                
                try {
                    $results[] = [
                        'content_id' => $contentId,
                        'success' => $this->scheduleWithVersion($contentId, $versionId, $scheduledAt),
                        'error' => null
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'content_id' => $contentId,
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        
        return $results;
    }

    public function checkForConflicts(int $contentId, int $versionId, DateTime $scheduledAt): array {
        $existing = $this->getScheduledEvents($contentId);
        $conflicts = [];

        foreach ($existing as $event) {
            if ($event['version_id'] !== $versionId &&
                abs($event['scheduled_at'] - $scheduledAt->getTimestamp()) < 3600) {
                $conflicts[] = [
                    'existing_version' => $event['version_id'],
                    'scheduled_time' => $event['scheduled_at'],
                    'new_version' => $versionId,
                    'requested_time' => $scheduledAt->getTimestamp()
                ];
            }
        }

        return $conflicts;
    }

    public function batchCheckForConflicts(array $contentIds, array $versionIds, array $scheduledAts): array {
        $allConflicts = [];
        
        foreach ($contentIds as $index => $contentId) {
            $conflicts = $this->checkForConflicts(
                $contentId,
                $versionIds[$index],
                $scheduledAts[$index]
            );
            
            if (!empty($conflicts)) {
                $allConflicts[$contentId] = $conflicts;
            }
        }
        
        return $allConflicts;
    }

    protected function createSchedule(int $contentId, int $versionId, DateTime $scheduledAt): bool {
        $stmt = $this->prepare(
            "INSERT INTO scheduled_events
            (content_id, version_id, scheduled_at)
            VALUES (?, ?, ?)"
        );
        
        return $stmt->execute([
            $contentId,
            $versionId,
            $scheduledAt->format('Y-m-d H:i:s')
        ]);
    }

    protected function updateScheduledEvents(array $eventIds, array $updates): array {
        $results = [];
        $this->beginTransaction();
        
        try {
            foreach ($eventIds as $index => $eventId) {
                $stmt = $this->prepare(
                    "UPDATE scheduled_events
                    SET version_id = ?, scheduled_at = ?
                    WHERE id = ?"
                );
                
                $success = $stmt->execute([
                    $updates[$index]['version_id'],
                    $updates[$index]['scheduled_at']->format('Y-m-d H:i:s'),
                    $eventId
                ]);
                
                $results[] = [
                    'event_id' => $eventId,
                    'success' => $success,
                    'error' => $success ? null : 'Database operation failed'
                ];
            }
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        
        return $results;
    }
}
