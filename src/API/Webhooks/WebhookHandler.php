<?php
namespace CMS\API\Webhooks;

class WebhookHandler {
    /**
     * Process n8n webhook with HMAC verification
     * @param array $request Webhook payload
     * @return array Response data
     */
    public function processN8nWebhook(array $request): array {
        if (!$this->verifyHmac($request)) {
            return ['error' => 'Invalid HMAC signature'];
        }

        // Process valid webhook
        return $this->handleWebhookPayload($request);
    }

    /**
     * Verify HMAC signature
     * @param array $request Webhook payload
     * @return bool Verification result
     */
    private function verifyHmac(array $request): bool {
        $secret = $this->getWebhookSecret();
        $expected = hash_hmac('sha256', json_encode($request['payload']), $secret);
        return hash_equals($expected, $request['headers']['x-n8n-signature']);
    }

    /**
     * Get webhook secret from configuration
     * @return string
     */
    private function getWebhookSecret(): string {
        return $_ENV['N8N_WEBHOOK_SECRET'] ?? '';
    }

    /**
     * Process validated webhook payload
     * @param array $payload Validated payload
     * @return array Response data
     */
    private function handleWebhookPayload(array $payload): array {
        // TODO: Implement integration with ReportScheduler
        return ['status' => 'processed'];
    }
}
