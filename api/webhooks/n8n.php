<?php
// Secure webhook endpoint for n8n automation
header('Content-Type: application/json');

// Log if token is missing
// .env is not parsed; reading $_ENV is allowed, but ensure safe default handling
if (empty($_ENV['N8N_WEBHOOK_TOKEN'] ?? '')) {
    $logMessage = date('Y-m-d H:i:s') . " - ERROR: N8N_WEBHOOK_TOKEN not found in .env\n";
    file_put_contents(__DIR__ . '/../../logs/n8n_webhook.log', $logMessage, FILE_APPEND);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method Not Allowed']));
}

// Safe request logging (no credentials or sensitive data)
$safeLogMessage = date('Y-m-d H:i:s') . " - Webhook request received" .
    " | Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') .
    " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') .
    " | Token provided: " . (!empty($_SERVER['HTTP_X_N8N_TOKEN']) ? 'yes' : 'no') . "\n";
file_put_contents(__DIR__ . '/../../logs/n8n_debug.log', $safeLogMessage, FILE_APPEND);

// Validate token
$providedToken = $_POST['N8N_WEBHOOK_TOKEN'] ?? $_SERVER['HTTP_X_N8N_TOKEN'] ?? null;
if (empty($providedToken) || $providedToken !== ($_ENV['N8N_WEBHOOK_TOKEN'] ?? '')) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid or missing token']));
}

// Sanitize and log payload
$payload = [
    'timestamp' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'data' => array_map('htmlspecialchars', $_POST)
];

$logEntry = json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL;
file_put_contents(__DIR__ . '/../../logs/n8n_webhook.log', $logEntry, FILE_APPEND);

// Success response
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Webhook received and logged',
    'timestamp' => $payload['timestamp']
]);
