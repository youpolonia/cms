<?php
declare(strict_types=1);

class NotificationService {
    private static function sendWebhook(string $url, array $payload): bool {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response !== false;
    }

    public static function sendApprovalRequest(int $requestId, int $contentId): void {
        $payload = [
            'event' => 'approval_request',
            'request_id' => $requestId,
            'content_id' => $contentId,
            'timestamp' => time()
        ];
        self::sendWebhook('https://n8n.example.com/webhook/approval', $payload);
    }

    public static function sendApprovalDecision(int $requestId, bool $approved): void {
        $payload = [
            'event' => 'approval_decision',
            'request_id' => $requestId,
            'approved' => $approved,
            'timestamp' => time()
        ];
        self::sendWebhook('https://n8n.example.com/webhook/decision', $payload);
    }
}
