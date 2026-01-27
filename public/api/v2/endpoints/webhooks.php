<?php
/**
 * Webhooks API Endpoint
 * Manage automation webhooks
 */

define('WEBHOOKS_FILE', CMS_ROOT . '/cms_storage/api_webhooks.json');

function load_webhooks(): array
{
    if (!file_exists(WEBHOOKS_FILE)) {
        return [];
    }
    $data = json_decode(file_get_contents(WEBHOOKS_FILE), true);
    return is_array($data) ? $data : [];
}

function save_webhooks(array $webhooks): bool
{
    $dir = dirname(WEBHOOKS_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return file_put_contents(WEBHOOKS_FILE, json_encode($webhooks, JSON_PRETTY_PRINT)) !== false;
}

function handle_webhooks(string $method, ?string $id, ?string $action): void
{
    switch ($method) {
        case 'GET':
            if ($id) {
                get_webhook($id);
            } else {
                list_webhooks();
            }
            break;

        case 'POST':
            if ($id && $action === 'test') {
                test_webhook($id);
            } else {
                create_webhook();
            }
            break;

        case 'PUT':
            if (!$id) api_error('Webhook ID required', 400);
            update_webhook($id);
            break;

        case 'DELETE':
            if (!$id) api_error('Webhook ID required', 400);
            delete_webhook($id);
            break;

        default:
            api_error('Method not allowed', 405);
    }
}

function list_webhooks(): void
{
    $webhooks = load_webhooks();
    api_response(['items' => array_values($webhooks)]);
}

function get_webhook(string $id): void
{
    $webhooks = load_webhooks();

    if (!isset($webhooks[$id])) {
        api_error('Webhook not found', 404);
    }

    api_response($webhooks[$id]);
}

function create_webhook(): void
{
    $data = get_request_body();

    if (empty($data['url'])) {
        api_error('Webhook URL is required', 400);
    }

    if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
        api_error('Invalid URL format', 400);
    }

    $webhooks = load_webhooks();

    $id = 'wh_' . uniqid();
    $secret = bin2hex(random_bytes(16));

    $webhook = [
        'id' => $id,
        'url' => $data['url'],
        'secret' => $secret,
        'events' => $data['events'] ?? ['*'],
        'active' => $data['active'] ?? true,
        'created_at' => gmdate('Y-m-d H:i:s'),
        'last_triggered' => null,
        'trigger_count' => 0,
    ];

    $webhooks[$id] = $webhook;
    save_webhooks($webhooks);

    api_response([
        'id' => $id,
        'secret' => $secret,
        'message' => 'Webhook created. Save the secret - it won\'t be shown again.',
    ], 201);
}

function update_webhook(string $id): void
{
    $webhooks = load_webhooks();

    if (!isset($webhooks[$id])) {
        api_error('Webhook not found', 404);
    }

    $data = get_request_body();

    if (isset($data['url'])) {
        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            api_error('Invalid URL format', 400);
        }
        $webhooks[$id]['url'] = $data['url'];
    }

    if (isset($data['events'])) {
        $webhooks[$id]['events'] = $data['events'];
    }

    if (isset($data['active'])) {
        $webhooks[$id]['active'] = (bool)$data['active'];
    }

    save_webhooks($webhooks);

    api_response(['message' => 'Webhook updated successfully']);
}

function delete_webhook(string $id): void
{
    $webhooks = load_webhooks();

    if (!isset($webhooks[$id])) {
        api_error('Webhook not found', 404);
    }

    unset($webhooks[$id]);
    save_webhooks($webhooks);

    api_response(['message' => 'Webhook deleted successfully']);
}

function test_webhook(string $id): void
{
    $webhooks = load_webhooks();

    if (!isset($webhooks[$id])) {
        api_error('Webhook not found', 404);
    }

    $webhook = $webhooks[$id];

    $payload = [
        'event' => 'test',
        'timestamp' => gmdate('c'),
        'data' => ['message' => 'This is a test webhook'],
    ];

    $signature = hash_hmac('sha256', json_encode($payload), $webhook['secret']);

    $ch = curl_init($webhook['url']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Webhook-Signature: ' . $signature,
            'X-Webhook-Event: test',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        api_response([
            'success' => false,
            'error' => $error,
        ]);
    } else {
        api_response([
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response,
        ]);
    }
}
