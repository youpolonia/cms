<?php
class WebhookHandler {
    private $secret;
    private $reportScheduler;

    public function __construct($secret, $reportScheduler) {
        $this->secret = $secret;
        $this->reportScheduler = $reportScheduler;
    }

    public function handle($payload, $signature) {
        if (!$this->verifySignature($payload, $signature)) {
            throw new Exception('Invalid signature', 403);
        }

        $data = json_decode($payload, true);
        return $this->reportScheduler->processWebhook($data);
    }

    private function verifySignature($payload, $signature) {
        $expected = hash_hmac('sha256', $payload, $this->secret);
        return hash_equals($expected, $signature);
    }
}
