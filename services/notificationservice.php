<?php
/**
 * Enhanced Notification Service with multi-channel support
 * Features:
 * - Email, SMS, Webhook, In-App notifications
 * - User preference system
 * - Scheduled notifications
 * - Template management
 * - Webhook integration
 */
class NotificationService {
    private static $instance;
    private $channels = [
        'email' => true,
        'sms' => false,
        'webhook' => false,
        'in_app' => true
    ];
    private $templates = [];
    private $userPreferences = [];
    private $scheduledQueue = [];

    private function __construct() {
        $this->loadTemplates();
        $this->loadUserPreferences();
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadTemplates() {
        $this->templates = [
            'approval' => [
                'email' => 'Your request #{id} has been approved',
                'sms' => 'Approved: Request #{id}',
                'webhook' => ['event' => 'approval', 'message' => 'Request approved'],
                'in_app' => 'Request approved'
            ],
            'rejection' => [
                'email' => 'Your request #{id} has been rejected',
                'sms' => 'Rejected: Request #{id}',
                'webhook' => ['event' => 'rejection', 'message' => 'Request rejected'],
                'in_app' => 'Request rejected'
            ]
        ];
    }

    private function loadUserPreferences() {
        // Load from database or config
        $this->userPreferences = [
            'default' => ['email', 'in_app'],
            'urgent' => ['email', 'sms']
        ];
    }

    public function sendNotification($type, $data, $priority = 'default') {
        if (!isset($this->templates[$type])) {
            throw new Exception("Invalid notification type");
        }

        $channels = $this->userPreferences[$priority] ?? $this->userPreferences['default'];
        
        foreach ($channels as $channel) {
            if ($this->channels[$channel]) {
                $method = 'send' . ucfirst($channel) . 'Notification';
                if (method_exists($this, $method)) {
                    $this->$method($type, $data);
                }
            }
        }
    }

    public function scheduleNotification($type, $data, $sendAt, $priority = 'default') {
        $this->scheduledQueue[] = [
            'type' => $type,
            'data' => $data,
            'send_at' => $sendAt,
            'priority' => $priority
        ];
    }

    public function processScheduledNotifications() {
        $now = time();
        foreach ($this->scheduledQueue as $key => $notification) {
            if ($notification['send_at'] <= $now) {
                $this->sendNotification(
                    $notification['type'],
                    $notification['data'],
                    $notification['priority']
                );
                unset($this->scheduledQueue[$key]);
            }
        }
    }

    private function sendEmailNotification($type, $data) {
        $template = $this->templates[$type]['email'];
        $message = $this->parseTemplate($template, $data);
        // Implementation would use mail() or SMTP
    }

    private function sendSmsNotification($type, $data) {
        $template = $this->templates[$type]['sms'];
        $message = $this->parseTemplate($template, $data);
        // Implementation would use SMS gateway
    }

    private function sendWebhookNotification($type, $data) {
        $payload = $this->templates[$type]['webhook'];
        $payload['data'] = $data;
        // Implementation would use cURL to POST to webhook URL
    }

    private function sendInAppNotification($type, $data) {
        $template = $this->templates[$type]['in_app'];
        $message = $this->parseTemplate($template, $data);
        // Store in database or push to websocket
    }

    private function parseTemplate($template, $data) {
        if (is_array($template)) {
            foreach ($template as $key => $value) {
                $template[$key] = $this->parseTemplate($value, $data);
            }
            return $template;
        }

        foreach ($data as $key => $value) {
            $template = str_replace('{'.$key.'}', $value, $template);
        }
        return $template;
    }

    public function addTemplate($name, $template) {
        $this->templates[$name] = $template;
    }

    public function updateUserPreference($priority, $channels) {
        $this->userPreferences[$priority] = $channels;
    }

    public function enableChannel($channel, $enabled = true) {
        if (isset($this->channels[$channel])) {
            $this->channels[$channel] = $enabled;
        }
    }
}
