<?php

if (!defined('CMS_ROOT')) {
    http_response_code(403);
    die('Direct access not permitted.');
}

define('AUTOMATION_RULES_CONFIG', CMS_ROOT . '/config/automation_rules.json');
define('AUTOMATION_RULES_EXAMPLE', CMS_ROOT . '/config/automation_rules.json.example');

function automation_rules_load(): array
{
    $configPath = AUTOMATION_RULES_CONFIG;

    if (!file_exists($configPath)) {
        $configPath = AUTOMATION_RULES_EXAMPLE;
        if (!file_exists($configPath)) {
            return ['ok' => true, 'rules' => []];
        }
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        return ['ok' => false, 'error' => 'Failed to read rules configuration file.', 'rules' => []];
    }

    $data = @json_decode($json, true);
    if (!is_array($data)) {
        return ['ok' => false, 'error' => 'Invalid JSON in rules configuration file.', 'rules' => []];
    }

    if (!isset($data['rules']) || !is_array($data['rules'])) {
        return ['ok' => false, 'error' => 'Rules configuration missing "rules" array.', 'rules' => []];
    }

    $normalized = [];
    foreach ($data['rules'] as $rule) {
        if (!is_array($rule)) {
            continue;
        }

        $id = isset($rule['id']) && is_string($rule['id']) && $rule['id'] !== '' ? $rule['id'] : '';
        if ($id === '') {
            continue;
        }

        $actionType = isset($rule['action_type']) && is_string($rule['action_type']) ? $rule['action_type'] : '';
        if ($actionType !== 'n8n_webhook') {
            continue;
        }

        $actionConfig = isset($rule['action_config']) && is_array($rule['action_config']) ? $rule['action_config'] : [];
        if (!isset($actionConfig['event']) || !is_string($actionConfig['event']) || $actionConfig['event'] === '') {
            continue;
        }

        $normalized[] = [
            'id' => $id,
            'name' => isset($rule['name']) && is_string($rule['name']) ? $rule['name'] : '',
            'event_key' => isset($rule['event_key']) && is_string($rule['event_key']) ? $rule['event_key'] : '',
            'action_type' => 'n8n_webhook',
            'action_config' => [
                'event' => $actionConfig['event']
            ],
            'active' => isset($rule['active']) ? (bool)$rule['active'] : true,
            'notes' => isset($rule['notes']) && is_string($rule['notes']) ? $rule['notes'] : ''
        ];
    }

    return ['ok' => true, 'rules' => $normalized];
}

function automation_rules_save(array $rules): array
{
    $data = ['rules' => $rules];

    $json = @json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return ['ok' => false, 'error' => 'Failed to encode rules to JSON.'];
    }

    $configPath = AUTOMATION_RULES_CONFIG;
    $result = @file_put_contents($configPath, $json . "\n", LOCK_EX);

    if ($result === false) {
        return ['ok' => false, 'error' => 'Failed to write rules configuration file.'];
    }

    return ['ok' => true];
}

function automation_rules_handle_event(string $eventKey, array $context = []): void
{
    $loadResult = automation_rules_load();

    if (!$loadResult['ok']) {
        error_log('automation_rules_handle_event: Failed to load rules configuration.');
        return;
    }

    $rules = $loadResult['rules'];

    $matchingRules = [];
    foreach ($rules as $rule) {
        if (!isset($rule['active']) || $rule['active'] !== true) {
            continue;
        }

        if (!isset($rule['event_key']) || $rule['event_key'] !== $eventKey) {
            continue;
        }

        if (!isset($rule['action_type']) || $rule['action_type'] !== 'n8n_webhook') {
            continue;
        }

        $matchingRules[] = $rule;
    }

    if (empty($matchingRules)) {
        return;
    }

    if (!function_exists('n8n_trigger_event')) {
        error_log('automation_rules_handle_event: n8n_trigger_event function not available.');
        return;
    }

    foreach ($matchingRules as $rule) {
        try {
            $n8nEventName = isset($rule['action_config']['event']) ? $rule['action_config']['event'] : '';
            if ($n8nEventName === '') {
                continue;
            }

            $payload = [
                'rule_id' => $rule['id'],
                'rule_name' => $rule['name'],
                'event_key' => $eventKey,
                'context' => $context
            ];

            n8n_trigger_event($n8nEventName, $payload);
        } catch (Exception $e) {
            error_log('automation_rules_handle_event: Exception while triggering rule.');
        }
    }
}
