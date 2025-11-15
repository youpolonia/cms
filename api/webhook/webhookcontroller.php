<?php
/**
 * Webhook Endpoint Controller
 * Handles incoming webhook requests and triggers workflows
 */
class WebhookController {
    public static function handle(string $token): array {
        try {
            $result = WorkflowTrigger::evaluate([
                'type' => 'webhook',
                'params' => [
                    'token' => $token,
                    'payload' => self::getPayload()
                ]
            ]);

            if (!$result['matched']) {
                return [
                    'status' => 403,
                    'error' => $result['output']['error'] ?? 'Invalid webhook'
                ];
            }

            return [
                'status' => 200,
                'data' => $result['output']
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

    private static function getPayload(): array {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}
