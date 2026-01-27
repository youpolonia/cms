<?php
/**
 * n8n Inbound Webhook Endpoint
 *
 * Public endpoint for receiving POST callbacks from n8n/Zapier/Make.
 * Authenticates via shared secret and logs all accepted events.
 *
 * URL: /public/api/n8n/webhook.php
 * Method: POST only
 * Content-Type: application/json
 */

// Bootstrap
require_once realpath(__DIR__ . '/../../config.php');
require_once (defined('CMS_ROOT') ? CMS_ROOT : dirname(dirname(__DIR__))) . '/core/n8n_inbound.php';

// Method guard: POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed'], JSON_UNESCAPED_SLASHES);
    exit;
}

// Read and decode JSON body
$rawBody = file_get_contents('php://input');
$body = json_decode($rawBody, true);

if (!is_array($body)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'error' => 'invalid_json'], JSON_UNESCAPED_SLASHES);
    exit;
}

// Extract secret from header or body (header takes precedence)
$providedSecret = null;

// Check X-N8N-Secret header (case-insensitive)
foreach ($_SERVER as $key => $value) {
    if (strtoupper($key) === 'HTTP_X_N8N_SECRET') {
        $providedSecret = $value;
        break;
    }
}

// Fall back to body field if header not present
if ($providedSecret === null && isset($body['secret'])) {
    $providedSecret = (string)$body['secret'];
}

// Get remote IP
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;

// Handle the webhook
$result = n8n_inbound_handle($body, $providedSecret, $remoteIp);

// Extract status code
$statusCode = isset($result['status']) ? (int)$result['status'] : 200;
http_response_code($statusCode);

// Build response (exclude internal 'status' field)
$response = $result;
unset($response['status']);

// Emit JSON response
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
