<?php

namespace CMS\API\Webhooks;

class WebhookHandler
{
    public function processN8nWebhook($request)
    {
        // Verify HMAC signature
        if (!$this->verifyHmac($request)) {
            throw new \Exception('Invalid HMAC signature', 403);
        }

        $payload = json_decode($request->getBody(), true);
        
        // Process n8n webhook payload
        // TODO: Implement specific n8n workflow processing
        
        return [
            'status' => 'success',
            'processed_at' => date('c')
        ];
    }

    private function verifyHmac($request)
    {
        // TODO: Implement HMAC verification
        return true; // Temporary for testing
    }
}

namespace API\Webhooks;

use Includes\RoutingV2\Response;
use Report\ReportScheduler;
use InvalidArgumentException;

class WebhookHandler {
    private $secret;
    private $reportScheduler;

    public function __construct(string $secret, ReportScheduler $reportScheduler) {
        $this->secret = $secret;
        $this->reportScheduler = $reportScheduler;
    }

    public function handle(array $request): Response {
        $this->validateRequest($request);
        $this->verifySignature($request);
        
        $payload = json_decode($request['payload'], true);
        return $this->processPayload($payload);
    }

    private function validateRequest(array $request): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new InvalidArgumentException('Only POST requests accepted');
        }

        if (empty($request['payload']) || empty($request['signature'])) {
            throw new InvalidArgumentException('Missing required fields');
        }
    }

    private function verifySignature(array $request): void {
        $computed = hash_hmac('sha256', $request['payload'], $this->secret);
        if (!hash_equals($computed, $request['signature'])) {
            throw new InvalidArgumentException('Invalid signature');
        }
    }

    private function processPayload(array $payload): Response {
        // Process different webhook event types
        switch ($payload['event_type']) {
            case 'report_scheduled':
                return $this->handleReportScheduled($payload);
            default:
                return new Response(200, ['status' => 'unhandled_event']);
        }
    }

    private function handleReportScheduled(array $payload): Response {
        // Extract and validate required fields
        $required = ['report_id', 'schedule', 'recipients'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Process with ReportScheduler
        $result = $this->reportScheduler->schedule(
            $payload['report_id'],
            $payload['schedule'],
            $payload['recipients'],
            $payload['custom_schedule'] ?? null
        );

        return new Response(200, $result);
    }
}
