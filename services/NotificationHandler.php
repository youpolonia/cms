<?php
/**
 * AI Notification Handler
 * Processes AI-generated notifications and webhooks
 */
class NotificationHandler {
    private $notificationService;
    private $aiConfig;

    public function __construct($notificationService) {
        $this->notificationService = $notificationService;
        $this->aiConfig = require_once __DIR__ . '/../config/ai.php';
    }

    public function processAiNotification($userId, $aiResponse) {
        $alertLevel = $this->determineAlertLevel($aiResponse);
        $message = $this->formatAiMessage($aiResponse);
        
        return $this->notificationService->sendAlert(
            $userId,
            $alertLevel,
            $message
        );
    }

    private function determineAlertLevel($aiResponse) {
        // Implement logic to determine alert level from AI response
        return $aiResponse['severity'] ?? 'medium';
    }

    private function formatAiMessage($aiResponse) {
        // Format AI response into human-readable message
        return "[AI Alert] " . ($aiResponse['message'] ?? 'Unknown alert');
    }

    public function handleWebhook($payload) {
        // Validate webhook signature
        if (!$this->validateWebhook($payload)) {
            throw new Exception("Invalid webhook signature");
        }

        return $this->processAiNotification(
            $payload['user_id'],
            $payload['ai_response']
        );
    }

    private function validateWebhook($payload) {
        // Verify required fields
        if (empty($payload['timestamp']) || empty($payload['signature'])) {
            return false;
        }

        // Replay attack protection (5 minute window)
        $timestamp = (int)$payload['timestamp'];
        $currentTime = time();
        if (abs($currentTime - $timestamp) > 300) {
            return false;
        }

        // Verify HMAC signature
        $secret = $this->aiConfig['webhook_secret'];
        $expectedSignature = hash_hmac('sha256', $payload['timestamp'].$payload['user_id'], $secret);
        
        return hash_equals($expectedSignature, $payload['signature']);
    }

    private function checkRetryLimit($payload) {
        // Implement retry limit logic (max 3 attempts)
        $retryCount = $payload['retry_count'] ?? 0;
        return $retryCount <= 3;
    }
}
