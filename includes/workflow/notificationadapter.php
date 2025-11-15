<?php
/**
 * NotificationAdapter - Bridge between WorkflowManager and NotificationService
 */
class NotificationAdapter {
    private $notificationService;
    private $logger;
    private $maxRetries = 3;
    private $retryDelay = 1000; // ms

    public function __construct($notificationService, $logger) {
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    /**
     * Send email notification with retry logic
     */
    public function sendEmailNotification($recipient, $subject, $message, $template = null) {
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $this->maxRetries) {
            try {
                $result = $this->notificationService->sendEmail(
                    $recipient,
                    $subject,
                    $message,
                    $template
                );
                
                $this->logNotificationEvent('email', $recipient, 'success');
                return $result;
            } catch (Exception $e) {
                $lastError = $e;
                $this->logNotificationEvent('email', $recipient, 'failed', $e->getMessage());
                $attempt++;
                
                if ($attempt < $this->maxRetries) {
                    usleep($this->retryDelay * pow(2, $attempt - 1) * 1000);
                }
            }
        }
        
        throw new Exception("Failed after {$this->maxRetries} attempts: " . $lastError->getMessage());
    }

    /**
     * Send webhook notification with retry logic
     */
    public function sendWebhookNotification($url, $payload) {
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $this->maxRetries) {
            try {
                $result = $this->notificationService->sendWebhook(
                    $url,
                    $payload
                );
                
                $this->logNotificationEvent('webhook', $url, 'success');
                return $result;
            } catch (Exception $e) {
                $lastError = $e;
                $this->logNotificationEvent('webhook', $url, 'failed', $e->getMessage());
                $attempt++;
                
                if ($attempt < $this->maxRetries) {
                    usleep($this->retryDelay * pow(2, $attempt - 1) * 1000);
                }
            }
        }
        
        throw new Exception("Failed after {$this->maxRetries} attempts: " . $lastError->getMessage());
    }

    /**
     * Log notification events for auditing
     */
    public function logNotificationEvent($type, $target, $status, $error = null) {
        $logData = [
            'type' => $type,
            'target' => $target,
            'status' => $status,
            'timestamp' => time(),
            'error' => $error
        ];
        
        $this->logger->log('notification_event', json_encode($logData));
    }
}
