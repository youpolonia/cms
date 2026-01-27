<?php
/**
 * n8n Bindings Library
 * Centralized helper for loading and accessing n8n event bindings
 * Pure function library - NO classes, NO database access, NO sessions
 */

// Define bindings file path constant
if (!defined('N8N_BINDINGS_FILE')) {
    define('N8N_BINDINGS_FILE', CMS_ROOT . '/config/n8n_bindings.json');
}

/**
 * Load n8n bindings from JSON file
 * Returns associative array keyed by event key
 *
 * @return array Bindings array keyed by event key (e.g., ['content.published' => [...]])
 */
function n8n_bindings_load(): array
{
    // Check if bindings file exists
    if (!file_exists(N8N_BINDINGS_FILE)) {
        return [];
    }

    // Read file contents safely
    $json = @file_get_contents(N8N_BINDINGS_FILE);
    if ($json === false) {
        error_log('n8n_bindings_load: Failed to read bindings file');
        return [];
    }

    // Handle empty or whitespace-only file
    if (trim($json) === '') {
        return [];
    }

    // Decode JSON
    $data = @json_decode($json, true);
    if (!is_array($data)) {
        error_log('n8n_bindings_load: Failed to decode bindings JSON');
        return [];
    }

    // Normalize structure - check for 'bindings' key
    $bindings = [];
    if (isset($data['bindings']) && is_array($data['bindings'])) {
        // Structure is { "bindings": { "event.key": {...}, ... } }
        $bindings = $data['bindings'];
    } else {
        // Top-level is the bindings object itself
        $bindings = $data;
    }

    // Normalize values - ensure each binding is an array
    $normalized = [];
    foreach ($bindings as $eventKey => $binding) {
        if (is_array($binding)) {
            $normalized[$eventKey] = $binding;
        } else {
            // Coerce invalid bindings to empty array
            $normalized[$eventKey] = [];
        }
    }

    return $normalized;
}

/**
 * Get binding for a specific event key
 * Convenience accessor for UI and future triggers
 *
 * @param string $eventKey Event key (e.g., 'content.published')
 * @return array|null Binding array or null if not found
 */
function n8n_bindings_get(string $eventKey): ?array
{
    $bindings = n8n_bindings_load();

    if (isset($bindings[$eventKey]) && is_array($bindings[$eventKey])) {
        return $bindings[$eventKey];
    }

    return null;
}

/**
 * Get list of known CMS events for the builder UI
 * Hard-coded list of event descriptors
 *
 * @return array Array of event descriptors with keys: key, label, description
 */
function n8n_bindings_known_events(): array
{
    return [
        [
            'key' => 'content.published',
            'label' => 'Content published',
            'description' => 'Triggered when a post or page is published.'
        ],
        [
            'key' => 'content.updated',
            'label' => 'Content updated',
            'description' => 'Triggered when an existing post or page is updated.'
        ],
        [
            'key' => 'content.deleted',
            'label' => 'Content deleted',
            'description' => 'Triggered when content is permanently deleted.'
        ],
        [
            'key' => 'user.registered',
            'label' => 'User registered',
            'description' => 'Triggered when a new user account is created.'
        ],
        [
            'key' => 'user.updated',
            'label' => 'User updated',
            'description' => 'Triggered when a user profile is updated.'
        ],
        [
            'key' => 'user.deleted',
            'label' => 'User deleted',
            'description' => 'Triggered when a user account is deleted.'
        ],
        [
            'key' => 'form.submitted',
            'label' => 'Form submitted',
            'description' => 'Triggered when a public form is submitted.'
        ],
        [
            'key' => 'emailqueue.sent',
            'label' => 'Email sent',
            'description' => 'Triggered when an email is successfully sent from the queue.'
        ],
        [
            'key' => 'emailqueue.failed',
            'label' => 'Email send failed',
            'description' => 'Triggered when an email in the queue fails permanently.'
        ],
        [
            'key' => 'scheduler.job.started',
            'label' => 'Scheduled job started',
            'description' => 'Triggered when a scheduled job begins execution.'
        ],
        [
            'key' => 'scheduler.job.completed',
            'label' => 'Scheduled job completed',
            'description' => 'Triggered when a scheduled job finishes successfully.'
        ],
        [
            'key' => 'scheduler.job.failed',
            'label' => 'Scheduled job failed',
            'description' => 'Triggered when a scheduled job fails with errors.'
        ],
        [
            'key' => 'media.uploaded',
            'label' => 'Media uploaded',
            'description' => 'Triggered when a media file is uploaded to the library.'
        ],
        [
            'key' => 'media.deleted',
            'label' => 'Media deleted',
            'description' => 'Triggered when a media file is deleted from the library.'
        ],
        [
            'key' => 'comment.posted',
            'label' => 'Comment posted',
            'description' => 'Triggered when a new comment is posted on content.'
        ],
        [
            'key' => 'comment.approved',
            'label' => 'Comment approved',
            'description' => 'Triggered when a comment is approved by moderator.'
        ]
    ];
}

/**
 * Save n8n bindings to JSON file
 * Normalizes input and persists to config/n8n_bindings.json
 *
 * @param array $bindings Associative array keyed by event key
 * @return bool True on success, false on failure
 */
function n8n_bindings_save(array $bindings): bool
{
    try {
        // Normalize bindings
        $normalizedBindings = [];

        foreach ($bindings as $eventKey => $binding) {
            // Ensure binding is an array
            if (!is_array($binding)) {
                continue;
            }

            // Extract and normalize workflow_id
            $workflowId = isset($binding['workflow_id']) ? trim((string)$binding['workflow_id']) : '';

            // Skip bindings with empty workflow_id
            if ($workflowId === '') {
                continue;
            }

            // Normalize enabled flag
            $enabled = !empty($binding['enabled']);

            // Store normalized binding
            $normalizedBindings[$eventKey] = [
                'workflow_id' => $workflowId,
                'enabled' => $enabled
            ];
        }

        // Build payload in nested format
        $payload = [
            'bindings' => $normalizedBindings
        ];

        // Encode to JSON
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            error_log('n8n_bindings_save: JSON encoding failed');
            return false;
        }

        // Write to file with exclusive lock
        $result = file_put_contents(N8N_BINDINGS_FILE, $json . PHP_EOL, LOCK_EX);

        if ($result === false) {
            error_log('n8n_bindings_save: Failed to write bindings file');
            return false;
        }

        return true;

    } catch (Exception $e) {
        error_log('n8n_bindings_save: ' . $e->getMessage());
        return false;
    }
}
