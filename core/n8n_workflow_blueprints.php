<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

function n8n_blueprints_load_all(): array
{
    $filePath = CMS_ROOT . '/config/n8n_workflow_blueprints.json';

    if (!file_exists($filePath)) {
        return ['items' => []];
    }

    $contents = file_get_contents($filePath);
    if ($contents === false) {
        error_log('n8n_blueprints: Failed to read file: ' . $filePath);
        return ['items' => []];
    }

    $data = json_decode($contents, true);
    if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
        error_log('n8n_blueprints: Invalid JSON structure in file: ' . $filePath);
        return ['items' => []];
    }

    return $data;
}

function n8n_blueprints_save_all(array $data): bool
{
    $dirPath = CMS_ROOT . '/config';
    $filePath = $dirPath . '/n8n_workflow_blueprints.json';

    if (!is_dir($dirPath)) {
        if (!mkdir($dirPath, 0775, true)) {
            error_log('n8n_blueprints: Failed to create directory: ' . $dirPath);
            return false;
        }
    }

    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) {
        error_log('n8n_blueprints: Failed to encode JSON');
        return false;
    }

    $result = file_put_contents($filePath, $json, LOCK_EX);
    if ($result === false) {
        error_log('n8n_blueprints: Failed to write file: ' . $filePath);
        return false;
    }

    return true;
}

function n8n_blueprints_generate_id(): string
{
    return 'wf_' . date('Ymd_His') . '_' . random_int(1000, 9999);
}

function n8n_blueprints_add(array $payload): array
{
    $data = n8n_blueprints_load_all();

    $now = gmdate('c');

    $entry = [
        'id' => n8n_blueprints_generate_id(),
        'name' => isset($payload['name']) ? (string)$payload['name'] : '',
        'description' => isset($payload['description']) ? (string)$payload['description'] : '',
        'trigger_event' => isset($payload['trigger_event']) ? (string)$payload['trigger_event'] : '',
        'target_type' => isset($payload['target_type']) ? (string)$payload['target_type'] : 'webhook',
        'target_value' => isset($payload['target_value']) ? (string)$payload['target_value'] : '',
        'active' => isset($payload['active']) ? (bool)$payload['active'] : false,
        'created_at' => $now,
        'updated_at' => $now
    ];

    $data['items'][] = $entry;

    n8n_blueprints_save_all($data);

    return $entry;
}

function n8n_blueprints_update(string $id, array $payload): ?array
{
    $data = n8n_blueprints_load_all();

    $found = false;
    $updated = null;

    foreach ($data['items'] as $idx => $item) {
        if ($item['id'] === $id) {
            $data['items'][$idx]['name'] = isset($payload['name']) ? (string)$payload['name'] : $item['name'];
            $data['items'][$idx]['description'] = isset($payload['description']) ? (string)$payload['description'] : $item['description'];
            $data['items'][$idx]['trigger_event'] = isset($payload['trigger_event']) ? (string)$payload['trigger_event'] : $item['trigger_event'];
            $data['items'][$idx]['target_type'] = isset($payload['target_type']) ? (string)$payload['target_type'] : $item['target_type'];
            $data['items'][$idx]['target_value'] = isset($payload['target_value']) ? (string)$payload['target_value'] : $item['target_value'];
            $data['items'][$idx]['active'] = isset($payload['active']) ? (bool)$payload['active'] : $item['active'];
            $data['items'][$idx]['updated_at'] = gmdate('c');

            $updated = $data['items'][$idx];
            $found = true;
            break;
        }
    }

    if (!$found) {
        return null;
    }

    n8n_blueprints_save_all($data);

    return $updated;
}

function n8n_blueprints_delete(string $id): bool
{
    $data = n8n_blueprints_load_all();

    $originalCount = count($data['items']);

    $data['items'] = array_values(array_filter($data['items'], function($item) use ($id) {
        return $item['id'] !== $id;
    }));

    $removed = count($data['items']) < $originalCount;

    if ($removed) {
        n8n_blueprints_save_all($data);
    }

    return $removed;
}
