<?php
/**
 * n8n Client Library
 * Pure function library for n8n workflow integration
 * NO classes, NO database access, NO sessions
 */

/**
 * Get the absolute path to the n8n configuration file
 *
 * @return string Absolute path to config/n8n_settings.json
 */
function n8n_config_path(): string
{
    return CMS_ROOT . '/config/n8n_settings.json';
}

/**
 * Load n8n configuration from JSON file
 * Returns normalized configuration array with sensible defaults on failure
 *
 * @return array Configuration with keys: enabled, base_url, auth_type, api_key, webhook_secret, username, password, timeout, verify_ssl
 */
function n8n_config_load(): array
{
    $defaults = [
        'enabled' => false,
        'base_url' => '',
        'auth_type' => 'none',
        'api_key' => '',
        'webhook_secret' => '',
        'username' => '',
        'password' => '',
        'timeout' => 10,
        'verify_ssl' => true
    ];

    $configPath = n8n_config_path();

    if (!file_exists($configPath)) {
        return $defaults;
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        return $defaults;
    }

    $data = @json_decode($json, true);
    if (!is_array($data)) {
        return $defaults;
    }

    // Normalize types and values
    $config = [];
    $config['enabled'] = isset($data['enabled']) ? (bool)$data['enabled'] : false;
    $config['base_url'] = isset($data['base_url']) ? (string)$data['base_url'] : '';

    // Validate auth_type
    $authType = isset($data['auth_type']) ? (string)$data['auth_type'] : 'none';
    $config['auth_type'] = in_array($authType, ['none', 'apikey', 'basic'], true) ? $authType : 'none';

    $config['api_key'] = isset($data['api_key']) ? (string)$data['api_key'] : '';
    $config['webhook_secret'] = isset($data['webhook_secret']) ? (string)$data['webhook_secret'] : '';
    $config['username'] = isset($data['username']) ? (string)$data['username'] : '';
    $config['password'] = isset($data['password']) ? (string)$data['password'] : '';

    // Normalize timeout (1-60 seconds)
    $timeout = isset($data['timeout']) ? (int)$data['timeout'] : 10;
    $config['timeout'] = max(1, min(60, $timeout));

    $config['verify_ssl'] = isset($data['verify_ssl']) ? (bool)$data['verify_ssl'] : true;

    return $config;
}

/**
 * Save n8n configuration to JSON file
 * Merges provided data with existing config and normalizes all values
 *
 * @param array $data Partial or complete configuration data
 * @return bool True on success, false on failure
 */
function n8n_config_save(array $data): bool
{
    // Load existing config
    $existing = n8n_config_load();

    // Merge and normalize
    $config = [];
    $config['enabled'] = isset($data['enabled']) ? (bool)$data['enabled'] : $existing['enabled'];

    // Normalize base_url: trim and remove trailing slashes
    $baseUrl = isset($data['base_url']) ? trim((string)$data['base_url']) : $existing['base_url'];
    $config['base_url'] = rtrim($baseUrl, '/');

    // Validate auth_type
    $authType = isset($data['auth_type']) ? (string)$data['auth_type'] : $existing['auth_type'];
    $config['auth_type'] = in_array($authType, ['none', 'apikey', 'basic'], true) ? $authType : 'none';

    // Handle API key: only update if provided and non-empty
    if (isset($data['api_key']) && trim($data['api_key']) !== '') {
        $config['api_key'] = trim($data['api_key']);
    } else {
        $config['api_key'] = $existing['api_key'];
    }

    // Handle webhook secret: only update if provided and non-empty
    if (isset($data['webhook_secret']) && trim($data['webhook_secret']) !== '') {
        $config['webhook_secret'] = trim($data['webhook_secret']);
    } else {
        $config['webhook_secret'] = $existing['webhook_secret'];
    }

    // Handle username
    $config['username'] = isset($data['username']) ? trim((string)$data['username']) : $existing['username'];

    // Handle password: only update if provided and non-empty
    if (isset($data['password']) && trim($data['password']) !== '') {
        $config['password'] = trim($data['password']);
    } else {
        $config['password'] = $existing['password'];
    }

    // Normalize timeout (1-60 seconds)
    $timeout = isset($data['timeout']) ? (int)$data['timeout'] : $existing['timeout'];
    $config['timeout'] = max(1, min(60, $timeout));

    $config['verify_ssl'] = isset($data['verify_ssl']) ? (bool)$data['verify_ssl'] : $existing['verify_ssl'];

    // Write to file
    $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        error_log('n8n_config_save: Failed to encode configuration to JSON');
        return false;
    }

    $configPath = n8n_config_path();
    $result = @file_put_contents($configPath, $json . "\n", LOCK_EX);

    if ($result === false) {
        error_log('n8n_config_save: Failed to write configuration file');
        return false;
    }

    return true;
}

/**
 * Execute an HTTP request to the n8n API
 * Generic helper for future phases - not called anywhere yet
 *
 * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
 * @param string $path Relative path to append to base_url (e.g., "/rest/workflows")
 * @param array $options Optional parameters: 'query' (array), 'body' (array|string), 'timeout' (int)
 * @return array Response array with keys: ok (bool), status (int|null), body (string|null), json (mixed), error (string|null)
 */
function n8n_http_request(string $method, string $path, array $options = []): array
{
    $config = n8n_config_load();

    // Check if n8n is configured and enabled
    if (!$config['enabled'] || empty($config['base_url'])) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'n8n is not configured or disabled.'
        ];
    }

    // Build URL
    $baseUrl = rtrim($config['base_url'], '/');
    $path = '/' . ltrim($path, '/');
    $url = $baseUrl . $path;

    // Append query string if provided
    if (!empty($options['query']) && is_array($options['query'])) {
        $queryString = http_build_query($options['query']);
        $url .= '?' . $queryString;
    }

    // Determine timeout
    $timeout = isset($options['timeout']) ? max(1, min(60, (int)$options['timeout'])) : $config['timeout'];

    // Build headers
    $headers = [];

    // Prepare body
    $body = null;
    if (isset($options['body'])) {
        if (is_array($options['body'])) {
            $body = json_encode($options['body']);
            $headers[] = 'Content-Type: application/json';
        } elseif (is_string($options['body'])) {
            $body = $options['body'];
            $headers[] = 'Content-Type: application/json';
        }
    }

    // Add authorization headers
    if ($config['auth_type'] === 'apikey' && !empty($config['api_key'])) {
        $headers[] = 'X-API-Key: ' . $config['api_key'];
    } elseif ($config['auth_type'] === 'basic' && !empty($config['username'])) {
        $credentials = base64_encode($config['username'] . ':' . $config['password']);
        $headers[] = 'Authorization: Basic ' . $credentials;
    }

    // Build stream context
    $contextOptions = [
        'http' => [
            'method' => strtoupper($method),
            'timeout' => $timeout,
            'ignore_errors' => true
        ]
    ];

    if (!empty($headers)) {
        $contextOptions['http']['header'] = implode("\r\n", $headers);
    }

    if ($body !== null) {
        $contextOptions['http']['content'] = $body;
    }

    // SSL verification
    if (!$config['verify_ssl']) {
        $contextOptions['ssl'] = [
            'verify_peer' => false,
            'verify_peer_name' => false
        ];
    }

    $context = stream_context_create($contextOptions);

    // Execute request
    $responseBody = @file_get_contents($url, false, $context);

    // Parse response
    $status = null;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $status = (int)$matches[1];
                break;
            }
        }
    }

    // Handle failure
    if ($responseBody === false) {
        error_log('n8n_http_request: Request failed');
        return [
            'ok' => false,
            'status' => $status,
            'body' => null,
            'json' => null,
            'error' => 'HTTP request failed'
        ];
    }

    // Try to decode JSON
    $json = null;
    if (!empty($responseBody)) {
        $decoded = @json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $json = $decoded;
        }
    }

    // Determine success
    $ok = ($status !== null && $status >= 200 && $status < 300);

    return [
        'ok' => $ok,
        'status' => $status,
        'body' => $responseBody,
        'json' => $json,
        'error' => $ok ? null : 'Request failed with status ' . $status
    ];
}

/**
 * Execute an HTTP request to an n8n webhook endpoint
 * Uses webhook_secret header instead of API key - for EXECUTE channel only
 *
 * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
 * @param string $url Full webhook URL (not relative path)
 * @param array $body Optional request body (will be JSON-encoded)
 * @return array Response array with keys: ok (bool), status (int|null), body (string|null), json (mixed), error (string|null)
 */
function n8n_webhook_request(string $method, string $url, array $body = []): array
{
    $config = n8n_config_load();

    // Check if n8n is configured and enabled
    if (!$config['enabled']) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'n8n is not configured or disabled.'
        ];
    }

    // Validate URL
    $url = trim($url);
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'Invalid webhook URL.'
        ];
    }

    // Build headers - NO API Key, use webhook secret instead
    $headers = [];
    $headers[] = 'Content-Type: application/json';

    // Add webhook secret header if configured
    if (!empty($config['webhook_secret'])) {
        $headers[] = 'X-N8N-Webhook-Secret: ' . $config['webhook_secret'];
    }

    // Prepare body
    $bodyContent = null;
    if (!empty($body)) {
        $bodyContent = json_encode($body);
        if ($bodyContent === false) {
            return [
                'ok' => false,
                'status' => null,
                'body' => null,
                'json' => null,
                'error' => 'Failed to encode request body as JSON.'
            ];
        }
    }

    // Build stream context
    $contextOptions = [
        'http' => [
            'method' => strtoupper($method),
            'timeout' => $config['timeout'],
            'ignore_errors' => true
        ]
    ];

    if (!empty($headers)) {
        $contextOptions['http']['header'] = implode("\r\n", $headers);
    }

    if ($bodyContent !== null) {
        $contextOptions['http']['content'] = $bodyContent;
    }

    // SSL verification
    if (!$config['verify_ssl']) {
        $contextOptions['ssl'] = [
            'verify_peer' => false,
            'verify_peer_name' => false
        ];
    }

    $context = stream_context_create($contextOptions);

    // Execute request
    $responseBody = @file_get_contents($url, false, $context);

    // Parse response
    $status = null;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $status = (int)$matches[1];
                break;
            }
        }
    }

    // Handle failure
    if ($responseBody === false) {
        error_log('n8n_webhook_request: Request failed to ' . $url);
        return [
            'ok' => false,
            'status' => $status,
            'body' => null,
            'json' => null,
            'error' => 'HTTP request failed'
        ];
    }

    // Try to decode JSON
    $json = null;
    if (!empty($responseBody)) {
        $decoded = @json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $json = $decoded;
        }
    }

    // Determine success
    $ok = ($status !== null && $status >= 200 && $status < 300);

    return [
        'ok' => $ok,
        'status' => $status,
        'body' => $responseBody,
        'json' => $json,
        'error' => $ok ? null : 'Request failed with status ' . $status
    ];
}

/**
 * Check if n8n is properly configured and enabled
 *
 * @param array|null $config Optional configuration array; if null, loads from file
 * @return bool True if enabled and base_url is non-empty
 */
function n8n_is_configured(?array $config = null): bool
{
    if ($config === null) {
        $config = n8n_config_load();
    }

    if (!is_array($config)) {
        return false;
    }

    $enabled = isset($config['enabled']) ? (bool)$config['enabled'] : false;
    $baseUrl = isset($config['base_url']) ? trim((string)$config['base_url']) : '';

    return ($enabled && $baseUrl !== '');
}

/**
 * Fetch list of workflows from n8n REST API
 * Read-only helper for admin workflow listing
 *
 * @param int $limit Maximum number of workflows to fetch (1-200, default 50)
 * @return array Response with keys: ok (bool), error (string|null), workflows (array)
 */
function n8n_list_workflows(int $limit = 50): array
{
    // Normalize limit
    if ($limit < 1) {
        $limit = 1;
    }
    if ($limit > 200) {
        $limit = 200;
    }

    // Load configuration
    $config = n8n_config_load();

    // Check if configured
    if (!n8n_is_configured($config)) {
        return [
            'ok' => false,
            'error' => 'n8n is not configured or disabled.',
            'workflows' => []
        ];
    }

    // Execute HTTP request
    $result = n8n_http_request('GET', '/api/v1/workflows', [
        'query' => [
            'limit' => $limit
        ]
    ]);

    // Handle HTTP failure
    if (!$result['ok'] || $result['status'] === null || $result['status'] < 200 || $result['status'] >= 300) {
        $errorMessage = !empty($result['error']) ? $result['error'] : 'Failed to fetch workflows from n8n.';
        return [
            'ok' => false,
            'error' => $errorMessage,
            'workflows' => []
        ];
    }

    // Parse workflows from JSON response
    $rawData = $result['json'];
    $workflowsList = [];

    if (is_array($rawData)) {
        // Check if data is nested under 'data' key
        if (isset($rawData['data']) && is_array($rawData['data'])) {
            $workflowsList = $rawData['data'];
        } else {
            $workflowsList = $rawData;
        }
    }

    // Normalize each workflow
    $normalized = [];
    foreach ($workflowsList as $workflow) {
        if (!is_array($workflow)) {
            continue;
        }

        $normalized[] = [
            'id' => isset($workflow['id']) ? $workflow['id'] : null,
            'name' => isset($workflow['name']) ? (string)$workflow['name'] : 'Unnamed workflow',
            'active' => isset($workflow['active']) ? (bool)$workflow['active'] : false,
            'created' => isset($workflow['createdAt']) ? (string)$workflow['createdAt'] : null,
            'updated' => isset($workflow['updatedAt']) ? (string)$workflow['updatedAt'] : null
        ];
    }

    return [
        'ok' => true,
        'error' => null,
        'workflows' => $normalized
    ];
}

/**
 * Check n8n health endpoint
 * Verifies n8n server connectivity and authentication
 *
 * @param array|null $config Optional configuration array; if null, loads from file
 * @return array Response with keys: ok (bool), status (int|null), body (string|null), json (mixed), error (string|null)
 */
function n8n_health_check(?array $config = null): array
{
    // Load config if not provided
    if ($config === null) {
        $config = n8n_config_load();
    }

    // Check if n8n is configured
    if (!n8n_is_configured($config)) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'n8n not configured'
        ];
    }

    // Execute health check request
    // Note: n8n_http_request() loads config internally, so we just pass the endpoint
    return n8n_http_request('GET', '/healthz');
}

/**
 * Create a new workflow in n8n
 * Posts workflow JSON to n8n REST API
 *
 * @param array $workflowJson Complete workflow definition including name, nodes, connections
 * @return array Response with keys: ok (bool), error (string|null), workflow (array|null - created workflow data)
 */
function n8n_create_workflow(array $workflowJson): array
{
    // Load configuration
    $config = n8n_config_load();

    // Check if configured
    if (!n8n_is_configured($config)) {
        return [
            'ok' => false,
            'error' => 'n8n is not configured or disabled.',
            'workflow' => null
        ];
    }

    // Validate workflow has required fields
    if (empty($workflowJson['name'])) {
        return [
            'ok' => false,
            'error' => 'Workflow name is required.',
            'workflow' => null
        ];
    }

    if (!isset($workflowJson['nodes']) || !is_array($workflowJson['nodes'])) {
        return [
            'ok' => false,
            'error' => 'Workflow must contain nodes array.',
            'workflow' => null
        ];
    }

    // Execute HTTP request to create workflow
    $result = n8n_http_request('POST', '/api/v1/workflows', [
        'body' => $workflowJson
    ]);

    // Handle HTTP failure
    if (!$result['ok'] || $result['status'] === null || $result['status'] < 200 || $result['status'] >= 300) {
        $errorMessage = 'Failed to create workflow in n8n.';
        if (!empty($result['json']['message'])) {
            $errorMessage .= ' ' . $result['json']['message'];
        } elseif (!empty($result['error'])) {
            $errorMessage .= ' ' . $result['error'];
        }
        return [
            'ok' => false,
            'error' => $errorMessage,
            'workflow' => null
        ];
    }

    // Extract workflow data from response
    $workflowData = $result['json'];

    return [
        'ok' => true,
        'error' => null,
        'workflow' => $workflowData
    ];
}

/**
 * Activate or deactivate a workflow in n8n
 * Patches workflow active status via n8n REST API
 *
 * @param string $workflowId The workflow ID in n8n
 * @param bool $active True to activate, false to deactivate
 * @return array Response with keys: ok (bool), error (string|null), workflow (array|null - updated workflow data)
 */
function n8n_activate_workflow(string $workflowId, bool $active = true): array
{
    // Load configuration
    $config = n8n_config_load();

    // Check if configured
    if (!n8n_is_configured($config)) {
        return [
            'ok' => false,
            'error' => 'n8n is not configured or disabled.',
            'workflow' => null
        ];
    }

    // Validate workflow ID
    $workflowId = trim($workflowId);
    if ($workflowId === '') {
        return [
            'ok' => false,
            'error' => 'Workflow ID is required.',
            'workflow' => null
        ];
    }

    // n8n API uses POST to /activate or /deactivate endpoints
    $endpoint = '/api/v1/workflows/' . urlencode($workflowId) . ($active ? '/activate' : '/deactivate');

    // Execute HTTP request
    $result = n8n_http_request('POST', $endpoint);

    // Handle HTTP failure
    if (!$result['ok'] || $result['status'] === null || $result['status'] < 200 || $result['status'] >= 300) {
        $errorMessage = $active ? 'Failed to activate workflow.' : 'Failed to deactivate workflow.';
        if (!empty($result['json']['message'])) {
            $errorMessage .= ' ' . $result['json']['message'];
        } elseif (!empty($result['error'])) {
            $errorMessage .= ' ' . $result['error'];
        }
        return [
            'ok' => false,
            'error' => $errorMessage,
            'workflow' => null
        ];
    }

    // Extract workflow data from response
    $workflowData = $result['json'];

    return [
        'ok' => true,
        'error' => null,
        'workflow' => $workflowData
    ];
}

/**
 * Robust health check helper for n8n connectivity
 * Returns a standardized response structure for admin UI consumption
 *
 * @param array $overrideSettings Optional settings override; if not provided, loads from config file
 * @return array Response with keys: ok (bool), statusCode (int|null), error (string|null), details (mixed)
 */
function n8n_client_health_check(array $overrideSettings = []): array
{
    // Load configuration
    $config = empty($overrideSettings) ? n8n_config_load() : $overrideSettings;

    // Validate base URL is present
    $baseUrl = isset($config['base_url']) ? trim((string)$config['base_url']) : '';
    if ($baseUrl === '') {
        return [
            'ok' => false,
            'statusCode' => null,
            'error' => 'Missing n8n base URL in settings.',
            'details' => null,
        ];
    }

    // Execute health check using existing infrastructure
    try {
        // Use the existing n8n_http_request wrapper
        $result = n8n_http_request('GET', '/healthz', [
            'timeout' => isset($config['timeout']) ? (int)$config['timeout'] : 10
        ]);

        // Extract status code
        $statusCode = isset($result['status']) ? (int)$result['status'] : null;

        // Check for successful HTTP response (2xx status codes)
        if ($statusCode !== null && $statusCode >= 200 && $statusCode < 300) {
            return [
                'ok' => true,
                'statusCode' => $statusCode,
                'error' => null,
                'details' => $result['json'] ?? $result['body'] ?? null,
            ];
        }

        // Handle non-2xx HTTP status codes
        return [
            'ok' => false,
            'statusCode' => $statusCode,
            'error' => 'Unexpected HTTP status from n8n health endpoint.',
            'details' => isset($result['error']) && is_string($result['error']) ? substr($result['error'], 0, 100) : null,
        ];
    } catch (Exception $e) {
        // Log detailed error internally, return sanitized message
        error_log('n8n_client_health_check: Connection error - ' . $e->getMessage());
        return [
            'ok' => false,
            'statusCode' => null,
            'error' => 'Unable to contact n8n instance.',
            'details' => null,
        ];
    }
}
