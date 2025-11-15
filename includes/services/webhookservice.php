<?php
/**
 * Webhook Service Implementation
 * Version: 1.0
 */

class WebhookService {
    private $db;
    private $retryQueue = [];
    private $maxRetries = 3;
    private $retryDelay = 5000; // 5 seconds in milliseconds

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function registerWebhook($url, $events, $secret = null) {
        $query = "INSERT INTO webhooks (url, events, secret, status) VALUES (?, ?, ?, 'active')";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$url, json_encode($events), $secret]);
        return $this->db->lastInsertId();
    }

    public function triggerEvent($eventType, $payload) {
        $webhooks = $this->getWebhooksForEvent($eventType);
        
        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $payload);
        }
    }

    private function getWebhooksForEvent($eventType) {
        $query = "SELECT * FROM webhooks WHERE status = 'active' AND JSON_CONTAINS(events, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([json_encode($eventType)]);
        return $stmt->fetchAll();
    }

    private function dispatchWebhook($webhook, $payload) {
        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Event: ' . $webhook['event_type'],
            'X-Webhook-Signature: ' . $this->generateSignature($payload, $webhook['secret'])
        ];

        $ch = curl_init($webhook['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->queueForRetry($webhook, $payload);
        }
    }

    private function generateSignature($payload, $secret) {
        return $secret ? hash_hmac('sha256', json_encode($payload), $secret) : '';
    }

    private function queueForRetry($webhook, $payload) {
        $retryCount = isset($this->retryQueue[$webhook['id']]) ? 
            $this->retryQueue[$webhook['id']]['retry_count'] + 1 : 1;

        if ($retryCount <= $this->maxRetries) {
            $this->retryQueue[$webhook['id']] = [
                'webhook' => $webhook,
                'payload' => $payload,
                'retry_count' => $retryCount,
                'next_retry' => microtime(true) * 1000 + $this->retryDelay
            ];
        }
    }

    public function processRetryQueue() {
        $currentTime = microtime(true) * 1000;
        foreach ($this->retryQueue as $id => $item) {
            if ($item['next_retry'] <= $currentTime) {
                $this->dispatchWebhook($item['webhook'], $item['payload']);
                unset($this->retryQueue[$id]);
            }
        }
    }

    // Template payload generators
    public function getContentCreatedPayload($content) {
        return [
            'event' => 'content.created',
            'data' => $content,
            'timestamp' => time()
        ];
    }

    public function getContentUpdatedPayload($content) {
        return [
            'event' => 'content.updated',
            'data' => $content,
            'timestamp' => time()
        ];
    }

    // Additional template payload methods...
}
