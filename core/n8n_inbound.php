<?php
/**
 * n8n Inbound Webhook Handler
 *
 * Provides generic inbound webhook support for n8n/Zapier/Make callbacks.
 * Handles secret validation, event logging, and basic payload processing.
 */

if (!function_exists('n8n_inbound_load_settings')) {

    /**
     * Load n8n settings from config/n8n_settings.json
     *
     * @return array Settings array, or empty array on failure
     */
    function n8n_inbound_load_settings(): array
    {
        $root = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__);
        $configPath = $root . '/config/n8n_settings.json';

        if (!file_exists($configPath)) {
            error_log("n8n_inbound: Settings file not found at {$configPath}");
            return [];
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            error_log("n8n_inbound: Failed to read settings file at {$configPath}");
            return [];
        }

        $settings = json_decode($content, true);
        if (!is_array($settings)) {
            error_log("n8n_inbound: Invalid JSON in settings file at {$configPath}");
            return [];
        }

        return $settings;
    }

    /**
     * Extract shared secret from settings
     *
     * Checks multiple possible keys in priority order:
     * 1. incoming_webhook_secret
     * 2. inbound_secret
     * 3. webhook_secret
     * 4. shared_secret
     *
     * @param array $settings Settings array
     * @return string|null Secret string, or null if not found
     */
    function n8n_inbound_get_shared_secret(array $settings): ?string
    {
        $keys = [
            'incoming_webhook_secret',
            'inbound_secret',
            'webhook_secret',
            'shared_secret'
        ];

        foreach ($keys as $key) {
            if (isset($settings[$key]) && is_string($settings[$key]) && $settings[$key] !== '') {
                return $settings[$key];
            }
        }

        return null;
    }

    /**
     * Log inbound webhook event to JSONL file
     *
     * @param array $event Event data to log
     * @return void
     */
    function n8n_inbound_log_event(array $event): void
    {
        $root = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__);
        $logPath = $root . '/logs/n8n_inbound.log';

        // Ensure logs directory exists
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                error_log("n8n_inbound: Failed to create logs directory at {$logDir}");
                return;
            }
        }

        // Encode as single-line JSON
        $json = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            error_log("n8n_inbound: Failed to encode event as JSON");
            return;
        }

        // Append to log file
        $result = file_put_contents($logPath, $json . "\n", FILE_APPEND | LOCK_EX);
        if ($result === false) {
            error_log("n8n_inbound: Failed to write to log file at {$logPath}");
        }
    }

    /**
     * Handle inbound webhook request
     *
     * @param array $requestBody Decoded JSON request body
     * @param string|null $providedSecret Secret provided by caller
     * @param string|null $remoteIp Remote IP address
     * @return array Response array with 'status', 'ok', and message fields
     */
    function n8n_inbound_handle(array $requestBody, ?string $providedSecret, ?string $remoteIp = null): array
    {
        // Load settings
        $settings = n8n_inbound_load_settings();
        $expectedSecret = n8n_inbound_get_shared_secret($settings);

        // Check if inbound webhooks are configured
        if ($expectedSecret === null) {
            return [
                'status' => 503,
                'ok' => false,
                'error' => 'inbound_disabled',
                'message' => 'Inbound webhooks are not configured.'
            ];
        }

        // Validate secret (timing-safe comparison)
        if (!constant_time_compare($providedSecret ?? '', $expectedSecret)) {
            return [
                'status' => 401,
                'ok' => false,
                'error' => 'invalid_secret'
            ];
        }

        // Extract and validate type
        $type = isset($requestBody['type']) ? (string)$requestBody['type'] : '';
        if ($type === '') {
            return [
                'status' => 400,
                'ok' => false,
                'error' => 'missing_type'
            ];
        }

        // Extract payload
        $payload = isset($requestBody['payload']) && is_array($requestBody['payload'])
            ? $requestBody['payload']
            : [];

        // Build event for logging
        $event = [
            'timestamp' => gmdate('c'),
            'remote_ip' => $remoteIp,
            'type' => $type,
            'payload' => $payload
        ];

        // Log the event
        n8n_inbound_log_event($event);

        // Handle reserved "test" type
        if ($type === 'test') {
            return [
                'status' => 200,
                'ok' => true,
                'message' => 'test_ok'
            ];
        }

        // Default success response
        return [
            'status' => 200,
            'ok' => true,
            'message' => 'received',
            'type' => $type
        ];
    }

    /**
     * Timing-safe string comparison
     *
     * @param string $a First string
     * @param string $b Second string
     * @return bool True if strings match
     */
    function constant_time_compare(string $a, string $b): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }

        // Fallback: simple constant-time comparison
        $lenA = strlen($a);
        $lenB = strlen($b);
        $result = $lenA ^ $lenB;

        for ($i = 0; $i < $lenA && $i < $lenB; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $result === 0 && $lenA === $lenB;
    }

} // end if !function_exists guard
