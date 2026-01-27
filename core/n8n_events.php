<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', __DIR__ . '/..');
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/n8n_client.php';
require_once CMS_ROOT . '/core/sites_context.php';

function n8n_bindings_config_path(): string
{
    return CMS_ROOT . '/config/n8n_bindings.json';
}

function n8n_bindings_load(): array
{
    $configPath = n8n_bindings_config_path();

    if (!file_exists($configPath)) {
        return ['bindings' => []];
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        error_log('n8n_bindings_load: Failed to read bindings config file');
        return ['bindings' => []];
    }

    $data = @json_decode($json, true);
    if (!is_array($data)) {
        error_log('n8n_bindings_load: Invalid JSON in bindings config');
        return ['bindings' => []];
    }

    if (!isset($data['bindings']) || !is_array($data['bindings'])) {
        return ['bindings' => []];
    }

    $normalized = [];
    foreach ($data['bindings'] as $binding) {
        if (!is_array($binding)) {
            continue;
        }

        $normalized[] = [
            'id' => isset($binding['id']) ? (string)$binding['id'] : '',
            'name' => isset($binding['name']) ? (string)$binding['name'] : '',
            'event_key' => isset($binding['event_key']) ? (string)$binding['event_key'] : '',
            'workflow_id' => isset($binding['workflow_id']) ? $binding['workflow_id'] : '',
            'webhook_path' => isset($binding['webhook_path']) ? (string)$binding['webhook_path'] : '',
            'active' => isset($binding['active']) ? (bool)$binding['active'] : false
        ];
    }

    return ['bindings' => $normalized];
}

function n8n_trigger_event(string $eventKey, array $payload = []): array
{
    $config = n8n_config_load();

    if (!n8n_is_configured($config)) {
        return [
            'ok' => false,
            'reason' => 'n8n_not_configured',
            'triggers' => []
        ];
    }

    $bindingsData = n8n_bindings_load();
    $bindings = $bindingsData['bindings'];

    $siteSummary = null;
    if (function_exists('sites_bootstrap_current_site')) {
        $site = sites_bootstrap_current_site();
        if (is_array($site)) {
            $siteSummary = [
                'id'     => isset($site['id']) ? (string)$site['id'] : null,
                'domain' => isset($site['domain']) ? (string)$site['domain'] : null,
                'locale' => isset($site['locale']) ? (string)$site['locale'] : null,
            ];
        }
    }

    $matchingBindings = [];
    foreach ($bindings as $binding) {
        if (!isset($binding['active']) || $binding['active'] !== true) {
            continue;
        }

        if (!isset($binding['event_key']) || $binding['event_key'] !== $eventKey) {
            continue;
        }

        if (!isset($binding['webhook_path']) || trim((string)$binding['webhook_path']) === '') {
            continue;
        }

        $matchingBindings[] = $binding;
    }

    if (empty($matchingBindings)) {
        return [
            'ok' => true,
            'event_key' => $eventKey,
            'triggered_count' => 0,
            'triggers' => []
        ];
    }

    $triggers = [];
    $baseUrl = rtrim($config['base_url'], '/');

    foreach ($matchingBindings as $binding) {
        $webhookPath = trim((string)$binding['webhook_path']);
        $fullUrl = $baseUrl . '/webhook/' . $webhookPath;

        $requestBody = [
            'event_key' => $eventKey,
            'binding_id' => $binding['id'],
            'workflow_id' => $binding['workflow_id'],
            'payload' => $payload,
            'site' => $siteSummary
        ];

        // Use webhook auth (webhook_secret) not API key auth
        $result = n8n_webhook_request('POST', $fullUrl, $requestBody);

        $triggers[] = [
            'binding_id' => (string)$binding['id'],
            'workflow_id' => $binding['workflow_id'],
            'webhook_path' => $webhookPath,
            'ok' => isset($result['ok']) ? (bool)$result['ok'] : false,
            'status' => isset($result['status']) ? $result['status'] : null,
            'error' => isset($result['error']) ? $result['error'] : null
        ];
    }

    return [
        'ok' => true,
        'event_key' => $eventKey,
        'triggered_count' => count($triggers),
        'triggers' => $triggers
    ];
}
