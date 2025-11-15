<?php
require_once __DIR__ . '/../core/triggerregistry.php';
require_once __DIR__ . '/../core/workflowtrigger.php';

header('Content-Type: application/json');

try {
    // Validate token from URL
    $token = $_GET['token'] ?? '';
    if (empty($token)) {
        throw new Exception('Missing webhook token');
    }

    // Get raw POST data
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true) ?? [];

    // Create webhook trigger
    $trigger = TriggerRegistry::createTrigger([
        'type' => 'webhook',
        'webhook_token' => $token,
        'payload' => $data
    ]);

    // Execute workflows for this trigger
    $result = $trigger->execute();

    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
