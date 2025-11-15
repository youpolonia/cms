<?php

require_once __DIR__ . '/schedulenotification.php';

class OptimizedNotificationHandler {
    private $db;
    private $mailer;
    private $webhookService;
    private $preferenceCache = [];
    private $batchQueue = [];
    private $batchSize = 50;
    private $batchInterval = 300; // 5 minutes

    public function __construct($db, $mailer, $webhookService) {
        $this->db = $db;
        $this->mailer = $mailer;
        $this->webhookService = $webhookService;
    }

    public function sendNotification($userId, $scheduleId, $type, $data, $subject = null, $message = null, $templateId = null) {
        $priority = $this->getNotificationPriority($type);
        
        if ($templateId) {
            $template = NotificationTemplate::loadById($this->db, $templateId);
            $rendered = $template->render($data);
            $subject = $rendered['subject'];
            $message = $rendered['message'];
        } elseif (!$subject || !$message) {
            throw new InvalidArgumentException('Either templateId or both subject/message must be provided');
        }

        if ($priority <= 2) { // High priority (critical/warning)
            $this->sendImmediateNotification($userId, $scheduleId, $type, $data, $subject, $message);
        } else { // Normal/low priority
            $this->queueForBatchProcessing($userId, $scheduleId, $type, $data, $subject, $message, $priority);
        }
    }

    private function sendImmediateNotification($userId, $scheduleId, $type, $data, $subject, $message, $templateId = null) {
        $preferences = $this->getCachedPreferences($userId);
        $notificationId = $this->createDatabaseNotification($userId, $scheduleId, $type, $data, null, 1);

        foreach ($preferences['channels'] as $channel) {
            $this->sendToChannel($userId, $subject, $message, $channel);
        }
    }

    private function queueForBatchProcessing($userId, $scheduleId, $type, $data, $subject, $message, $priority, $templateId = null) {
        $this->batchQueue[] = [
            'user_id' => $userId,
            'schedule_id' => $scheduleId,
            'type' => $type,
            'data' => $data,
            'subject' => $subject,
            'message' => $message,
            'priority' => $priority
        ];

        if (count($this->batchQueue) >= $this->batchSize) {
            $this->processBatch();
        }
    }

    public function processBatch() {
        if (empty($this->batchQueue)) {
            return;
        }

        $batchId = uniqid();
        $this->createBatchRecord($batchId);

        // Group notifications by user to minimize preference lookups
        $groupedNotifications = [];
        foreach ($this->batchQueue as $notification) {
            $groupedNotifications[$notification['user_id']][] = $notification;
        }

        // Process each user's notifications
        foreach ($groupedNotifications as $userId => $notifications) {
            $preferences = $this->getCachedPreferences($userId);
            
            foreach ($notifications as $notification) {
                $notificationId = $this->createDatabaseNotification(
                    $userId,
                    $notification['schedule_id'],
                    $notification['type'],
                    $notification['data'],
                    $batchId,
                    $notification['priority']
                );

                foreach ($preferences['channels'] as $channel) {
                    $this->sendToChannel($userId, $notification['subject'], $notification['message'], $channel);
                }
            }
        }

        $this->updateBatchRecord($batchId, count($this->batchQueue));
        $this->batchQueue = [];
    }

    private function getCachedPreferences($userId) {
        if (isset($this->preferenceCache[$userId])) {
            return $this->preferenceCache[$userId];
        }

        // Check cache table first
        $query = "SELECT preferences FROM notification_preference_cache 
                 WHERE user_id = ? AND expires_at > NOW()";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $preferences = json_decode($result['preferences'], true);
        } else {
            $preferences = $this->getUserPreferences($userId);
            $this->cachePreferences($userId, $preferences);
        }

        $this->preferenceCache[$userId] = $preferences;
        return $preferences;
    }

    protected function getNotificationPriority($type) {
        $priorityMap = [
            ScheduleNotification::TYPE_CONFLICT => 1,
            ScheduleNotification::TYPE_EXECUTING => 2,
            ScheduleNotification::TYPE_UPDATED => 3,
            ScheduleNotification::TYPE_CREATED => 3,
            ScheduleNotification::TYPE_COMPLETED => 4
        ];

        return $priorityMap[$type] ?? 3;
    }

    // ... (require_once other existing methods from ScheduleNotificationHandler with optimizations)
}
