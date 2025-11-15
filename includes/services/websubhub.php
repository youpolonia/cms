<?php
/**
 * WebSub Hub Implementation
 * Version: 1.0
 */

class WebSubHub {
    private $db;
    private $subscriptions = [];
    private $contentQueue = [];

    public function __construct() {
        $this->db = \core\Database::connection();
        $this->loadSubscriptions();
    }

    private function loadSubscriptions() {
        $query = "SELECT * FROM websub_subscriptions WHERE status = 'active'";
        $this->subscriptions = $this->db->query($query)->fetchAll();
    }

    public function publishUpdate($topic, $content) {
        // Queue content update
        $this->contentQueue[$topic][] = $content;
        
        // Notify subscribers
        $this->notifySubscribers($topic);
    }

    private function notifySubscribers($topic) {
        $subscribers = array_filter($this->subscriptions, function($sub) use ($topic) {
            return $sub['topic'] === $topic;
        });

        foreach ($subscribers as $sub) {
            $this->sendNotification(
                $sub['callback_url'],
                $this->contentQueue[$topic],
                $sub['secret']
            );
        }
    }

    private function sendNotification($callbackUrl, $content, $secret) {
        $payload = json_encode([
            'content' => $content,
            'timestamp' => time()
        ]);

        $headers = [
            'Content-Type: application/json',
            'Link: <' . $callbackUrl . '>; rel="hub"',
            'X-Hub-Signature: ' . $this->generateSignature($payload, $secret)
        ];

        $ch = curl_init($callbackUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    private function generateSignature($payload, $secret) {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }

    public function verifyIntent($mode, $topic, $challenge, $leaseSeconds) {
        if ($mode === 'subscribe' || $mode === 'unsubscribe') {
            return $challenge;
        }
        return false;
    }

    public function handleCallbackVerification($data) {
        // Implementation for callback verification
    }
}
