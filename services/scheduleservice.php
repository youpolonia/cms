<?php

class ScheduleService {
    private $versionControlService;
    private $notificationService;
    private $workflowService;
    private $db;

    public function __construct($versionControlService, $notificationService, $workflowService) {
        $this->versionControlService = $versionControlService;
        $this->notificationService = $notificationService;
        $this->workflowService = $workflowService;
        $this->db = \core\Database::connection();
    }

    /**
     * Create a recurring schedule
     * @param array $eventData {
     *     @var string $content_id
     *     @var string $action
     *     @var string $start_date
     *     @var string $end_date
     *     @var string $recurrence_rule (RRULE format)
     *     @var string $timezone
     * }
     * @return array Created events with version info
     */
    public function createRecurringEvent(array $eventData): array {
        // Validate user context and permissions
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            throw new Exception('User not authenticated');
        }

        // Check schedule.create permission
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as has_permission
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE u.id = ? AND p.name = 'schedule.create'
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($result['has_permission'])) {
            throw new Exception('User lacks schedule.create permission');
        }

        // Check if content requires approval
        $workflowStatus = $this->workflowService->getWorkflowStatus(
            $eventData['content_id'],
            $eventData['workflow_id'] ?? 0
        );

        if ($workflowStatus['status'] !== 'approved') {
            // Start approval workflow if not already started
            if ($workflowStatus['status'] === 'pending') {
                $this->workflowService->startWorkflow(
                    $eventData['content_id'],
                    $eventData['workflow_id'] ?? 0,
                    $eventData['user_id'] ?? 0
                );
            }
            throw new Exception('Content requires approval before scheduling');
        }

        $events = $this->generateRecurringInstances($eventData);
        $createdEvents = [];
        
        // Add user context to each event
        foreach ($events as &$event) {
            $event['user_id'] = $userId;
            $version = $this->versionControlService->createVersion(
                $event['content_id'],
                'scheduled_'.$event['action']
            );
            
            $event['version_id'] = $version['id'];
            $createdEvents[] = $this->saveEvent($event);
            
            // Send creation notification
            $this->notificationService->send(
                'schedule_created',
                $event['content_id'],
                [
                    'version' => $version,
                    'schedule_id' => $event['id'],
                    'scheduled_at' => $event['scheduled_at']
                ]
            );

            // Schedule execution notification
            $this->scheduleNotification(
                $event['scheduled_at'],
                'schedule_executed',
                $event['content_id'],
                [
                    'schedule_id' => $event['id'],
                    'action' => $event['action']
                ]
            );
        }
        
        return $createdEvents;
    }

    private function generateRecurringInstances(array $eventData): array {
        // Parse RRULE and generate instances
        // This is a simplified implementation - would use a proper RRULE parser
        $events = [];
        $rule = $this->parseRRule($eventData['recurrence_rule']);
        
        $currentDate = new DateTime($eventData['start_date']);
        $endDate = new DateTime($eventData['end_date']);
        
        while ($currentDate <= $endDate) {
            $event = $eventData;
            $event['start_date'] = $currentDate->format('Y-m-d H:i:s');
            $events[] = $event;
            
            // Simple increment - would use RRULE logic in real implementation
            $currentDate->modify($rule['interval'].' '.$rule['frequency']);
        }
        
        return $events;
    }

    private function parseRRule(string $rrule): array {
        // Simplified RRULE parser
        $parts = explode(';', $rrule);
        $rule = [];
        
        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part);
            $rule[strtolower($key)] = $value;
        }
        
        return $rule;
    }

    private function saveEvent(array $eventData): array {
        // Would implement actual database save here
        return [
            'id' => uniqid(),
            'content_id' => $eventData['content_id'],
            'action' => $eventData['action'],
            'scheduled_at' => $eventData['start_date'],
            'version_id' => $eventData['version_id'] ?? null,
            'status' => 'pending',
            'workflow_id' => $eventData['workflow_id'] ?? null,
            'user_id' => $eventData['user_id']
        ];
    }

    private function scheduleNotification(
        string $triggerTime,
        string $eventType,
        string $contentId,
        array $payload = []
    ): void {
        // This would integrate with the system's notification scheduler
        $this->notificationService->schedule(
            $triggerTime,
            $eventType,
            $contentId,
            $payload
        );
    }
}
