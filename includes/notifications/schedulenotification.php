<?php

require_once __DIR__ . '/../../models/schedulenotification.php';
require_once __DIR__ . '/optimizednotificationhandler.php';

class ScheduleNotificationHandler {
    private $db;
    private $mailer;
    private $webhookService;

    private $optimizedHandler;

    public function __construct($db, $mailer, $webhookService) {
        $this->db = $db;
        $this->mailer = $mailer;
        $this->webhookService = $webhookService;
        $this->optimizedHandler = new OptimizedNotificationHandler($db, $mailer, $webhookService);
    }

    protected function sendCreatedNotification($userId, $scheduleId, $scheduleData) {
        $this->sendNotification(
            $userId,
            $scheduleId,
            ScheduleNotification::TYPE_CREATED,
            $scheduleData,
            'New Schedule Created',
            $this->getCreatedMessage($scheduleData)
        );
    }

    protected function sendUpdatedNotification($userId, $scheduleId, $scheduleData) {
        $this->sendNotification(
            $userId,
            $scheduleId,
            ScheduleNotification::TYPE_UPDATED,
            $scheduleData,
            'Schedule Updated',
            $this->getUpdatedMessage($scheduleData)
        );
    }

    protected function sendExecutingNotification($userId, $scheduleId, $scheduleData) {
        $this->sendNotification(
            $userId,
            $scheduleId,
            ScheduleNotification::TYPE_EXECUTING,
            $scheduleData,
            'Schedule About to Execute',
            $this->getExecutingMessage($scheduleData)
        );
    }

    protected function sendCompletedNotification($userId, $scheduleId, $scheduleData) {
        $this->sendNotification(
            $userId,
            $scheduleId,
            ScheduleNotification::TYPE_COMPLETED,
            $scheduleData,
            'Schedule Completed',
            $this->getCompletedMessage($scheduleData)
        );
    }

    protected function sendConflictNotification($userId, $scheduleId, $conflictData) {
        $this->sendNotification(
            $userId,
            $scheduleId,
            ScheduleNotification::TYPE_CONFLICT,
            $conflictData,
            'Schedule Conflict Detected',
            $this->getConflictMessage($conflictData)
        );
    }

    private function sendNotification($userId, $scheduleId, $type, $data, $subject, $message) {
        $this->optimizedHandler->sendNotification($userId, $scheduleId, $type, $data, $subject, $message);
    }

    public function processBatchNotifications() {
        $this->optimizedHandler->processBatch();
    }

    private function getUserPreferences($userId) {
        // Default to system defaults
        $defaults = require_once __DIR__ . '/../../config/notification.php';
        $preferences = $defaults['default_preferences'];

        // Override with user preferences if available
        $query = "SELECT preferences FROM user_notification_preferences WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $userPrefs = json_decode($result['preferences'], true);
            $preferences = array_merge($preferences, $userPrefs);
        }

        return $preferences;
    }

    public function getCreatedMessage($scheduleData) {
        return "A new schedule '{$scheduleData['title']}' has been created for {$scheduleData['start_date']}";
    }

    public function getUpdatedMessage($scheduleData) {
        return "Schedule '{$scheduleData['title']}' has been updated";
    }

    public function getExecutingMessage($scheduleData) {
        return "Schedule '{$scheduleData['title']}' is about to execute";
    }

    public function getCompletedMessage($scheduleData) {
        return "Schedule '{$scheduleData['title']}' has completed successfully";
    }

    public function getConflictMessage($conflictData) {
        return "Conflict detected with schedule '{$conflictData['title']}'";
    }
}
