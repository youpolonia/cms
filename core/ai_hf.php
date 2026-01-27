<?php
/**
 * Hugging Face Client Library
 * Pure function library for Hugging Face Inference API integration
 * NO classes, NO database access, NO sessions
 */

/**
 * Central AI settings file path (shared with ai-settings.php)
 */
if (!defined('AI_SETTINGS_FILE')) {
    define('AI_SETTINGS_FILE', CMS_ROOT . '/config/ai_settings.json');
}

/**
 * Cache directory for HF API responses
 */
if (!defined('AI_HF_CACHE_DIR')) {
    define('AI_HF_CACHE_DIR', CMS_ROOT . '/cms_storage/ai-hf-cache');
}

/**
 * Cache TTL in seconds (default: 7 days)
 */
if (!defined('AI_HF_CACHE_TTL')) {
    define('AI_HF_CACHE_TTL', 604800);
}

/**
 * Generate cache key from prompt and options
 *
 * @param string $prompt The input prompt
 * @param array $options Request options including model parameters
 * @param string $model Model name
 * @return string Cache key (SHA256 hash)
 */
function ai_hf_cache_key(string $prompt, array $options, string $model): string
{
    $data = [
        'prompt' => $prompt,
        'options' => $options,
        'model' => $model,
    ];
    return hash('sha256', json_encode($data));
}

/**
 * Get cached response if exists and not expired
 *
 * @param string $cacheKey Cache key
 * @return array|null Cached data or null if not found/expired
 */
function ai_hf_cache_get(string $cacheKey): ?array
{
    $dir = AI_HF_CACHE_DIR;
    if (!is_dir($dir)) {
        return null;
    }

    $path = $dir . '/' . $cacheKey . '.json';
    if (!file_exists($path)) {
        return null;
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        return null;
    }

    $data = @json_decode($content, true);
    if (!is_array($data)) {
        return null;
    }

    // Check expiration
    $createdAt = $data['created_at'] ?? 0;
    $ttl = $data['ttl'] ?? AI_HF_CACHE_TTL;
    if (time() - $createdAt > $ttl) {
        // Cache expired, delete file
        @unlink($path);
        return null;
    }

    return $data['response'] ?? null;
}

/**
 * Store response in cache
 *
 * @param string $cacheKey Cache key
 * @param array $response Response data to cache
 * @param int|null $ttl Optional TTL override in seconds
 * @return bool True on success
 */
function ai_hf_cache_set(string $cacheKey, array $response, ?int $ttl = null): bool
{
    $dir = AI_HF_CACHE_DIR;

    // Create cache directory if needed
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true)) {
            error_log('ai_hf_cache_set: Failed to create cache directory');
            return false;
        }
    }

    $data = [
        'created_at' => time(),
        'ttl' => $ttl ?? AI_HF_CACHE_TTL,
        'response' => $response,
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    $path = $dir . '/' . $cacheKey . '.json';
    return @file_put_contents($path, $json, LOCK_EX) !== false;
}

/**
 * Clear all cached HF responses
 *
 * @return int Number of cache files deleted
 */
function ai_hf_cache_clear(): int
{
    $dir = AI_HF_CACHE_DIR;
    if (!is_dir($dir)) {
        return 0;
    }

    $files = glob($dir . '/*.json');
    if ($files === false || empty($files)) {
        return 0;
    }

    $deleted = 0;
    foreach ($files as $file) {
        if (@unlink($file)) {
            $deleted++;
        }
    }

    return $deleted;
}

/**
 * Get cache statistics
 *
 * @return array Stats: total_files, total_size_bytes, oldest_file, newest_file
 */
function ai_hf_cache_stats(): array
{
    $dir = AI_HF_CACHE_DIR;
    $stats = [
        'total_files' => 0,
        'total_size_bytes' => 0,
        'oldest_timestamp' => null,
        'newest_timestamp' => null,
    ];

    if (!is_dir($dir)) {
        return $stats;
    }

    $files = glob($dir . '/*.json');
    if ($files === false || empty($files)) {
        return $stats;
    }

    $stats['total_files'] = count($files);

    foreach ($files as $file) {
        $stats['total_size_bytes'] += filesize($file);
        $mtime = filemtime($file);

        if ($stats['oldest_timestamp'] === null || $mtime < $stats['oldest_timestamp']) {
            $stats['oldest_timestamp'] = $mtime;
        }
        if ($stats['newest_timestamp'] === null || $mtime > $stats['newest_timestamp']) {
            $stats['newest_timestamp'] = $mtime;
        }
    }

    return $stats;
}

/**
 * Load Hugging Face settings (alias for ai_hf_config_load with field mapping)
 * Maps 'api_key' to 'api_token' for compatibility with admin UI spec
 *
 * @return array Settings array with keys: enabled, base_url, api_token, model, timeout
 */
function ai_hf_load_settings(): array
{
    $config = ai_hf_config_load();

    // Map api_key to api_token for spec compatibility
    return [
        'enabled'   => $config['enabled'] ?? false,
        'base_url'  => $config['base_url'] ?? '',
        'api_token' => $config['api_key'] ?? '',
        'model'     => $config['model'] ?? '',
        'timeout'   => $config['timeout'] ?? 15,
    ];
}

/**
 * Save Hugging Face settings (alias for ai_hf_config_save with field mapping)
 * Maps 'api_token' to 'api_key' for storage compatibility
 * Clamps timeout to 1-60 seconds as per spec
 *
 * @param array $settings Settings array to save
 * @return bool True on success, false on failure
 */
function ai_hf_save_settings(array $settings): bool
{
    // Normalize and clamp values
    $enabled = !empty($settings['enabled']);
    $baseUrl = isset($settings['base_url']) ? trim((string)$settings['base_url']) : '';
    $token = isset($settings['api_token']) ? trim((string)$settings['api_token']) : '';
    $model = isset($settings['model']) ? trim((string)$settings['model']) : '';

    // Clamp timeout to 1-60 seconds
    $timeout = isset($settings['timeout']) ? (int)$settings['timeout'] : 15;
    if ($timeout < 1) {
        $timeout = 1;
    } elseif ($timeout > 60) {
        $timeout = 60;
    }

    // Map api_token to api_key for storage
    $payload = [
        'enabled'   => $enabled,
        'base_url'  => $baseUrl,
        'api_key'   => $token,
        'model'     => $model,
        'timeout'   => $timeout,
    ];

    return ai_hf_config_save($payload);
}

/**
 * Execute HTTP request to Hugging Face endpoint
 * Simple GET-only helper for health checks
 *
 * @param string $path Request path (will be appended to base_url)
 * @param array $options Optional request options: 'method' (GET|POST), 'body' (string), 'content_type' (string)
 * @return array Response array with keys: ok, statusCode, error, body
 */
function ai_hf_http_request(string $path, array $options = []): array
{
    $settings = ai_hf_load_settings();

    // Check if enabled and configured
    if (!$settings['enabled'] || empty($settings['base_url'])) {
        return [
            'ok'         => false,
            'statusCode' => null,
            'error'      => 'Hugging Face is disabled or not configured.',
            'body'       => null,
        ];
    }

    // Extract options
    $method = isset($options['method']) ? strtoupper($options['method']) : 'GET';
    $body = isset($options['body']) ? $options['body'] : null;
    $contentType = isset($options['content_type']) ? $options['content_type'] : null;

    // Build URL
    $base = rtrim($settings['base_url'], '/');
    $path = ltrim($path, '/');
    $url = empty($path) ? $base : $base . '/' . $path;

    // Initialize cURL
    $ch = curl_init($url);
    if ($ch === false) {
        error_log('ai_hf_http_request: Failed to initialize cURL');
        return [
            'ok'         => false,
            'statusCode' => null,
            'error'      => 'Unable to contact Hugging Face endpoint.',
            'body'       => null,
        ];
    }

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$settings['timeout']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    // Set headers
    $headers = ['Accept: application/json'];
    if (!empty($settings['api_token'])) {
        $headers[] = 'Authorization: Bearer ' . $settings['api_token'];
    }
    if ($contentType !== null) {
        $headers[] = 'Content-Type: ' . $contentType;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Set POST method and body if needed
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
    }

    // Execute request
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle curl error
    if ($errno !== 0 || $body === false) {
        error_log('ai_hf_http_request: cURL error ' . $errno);
        return [
            'ok'         => false,
            'statusCode' => null,
            'error'      => 'Unable to contact Hugging Face endpoint.',
            'body'       => null,
        ];
    }

    // Determine success
    $ok = ($status >= 200 && $status < 300);
    $error = $ok ? null : 'Unexpected HTTP status from Hugging Face endpoint.';

    return [
        'ok'         => $ok,
        'statusCode' => (int)$status,
        'error'      => $error,
        'body'       => is_string($body) ? $body : null,
    ];
}

/**
 * Get the absolute path to the central AI settings file
 *
 * @return string Absolute path to config/ai_settings.json
 */
function ai_hf_config_path(): string
{
    return AI_SETTINGS_FILE;
}

/**
 * Get default Hugging Face configuration
 *
 * @return array Default configuration array
 */
function ai_hf_default_config(): array
{
    return [
        'enabled' => false,
        'base_url' => 'https://router.huggingface.co/hf-inference',
        'api_key' => '',
        'model' => '',
        'timeout' => 30,
        'verify_ssl' => true
    ];
}

/**
 * Get model for specific use case
 * @param string $type Model type: 'text', 'image', 'vision'
 * @return string Model identifier or empty string if not configured
 */
function ai_hf_get_model(string $type = 'text'): string
{
    $configPath = ai_hf_config_path();
    if (!file_exists($configPath)) {
        return '';
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        return '';
    }

    $allSettings = @json_decode($json, true);
    if (!is_array($allSettings)) {
        return '';
    }

    $hf = $allSettings['providers']['huggingface'] ?? [];

    // New format with models object
    if (isset($hf['models']) && is_array($hf['models'])) {
        return trim((string)($hf['models'][$type] ?? ''));
    }

    // Legacy format with default_model (fallback)
    if ($type === 'text' && isset($hf['default_model'])) {
        return trim((string)$hf['default_model']);
    }

    return '';
}

/**
 * Load Hugging Face configuration from central ai_settings.json
 * Reads from providers.huggingface section
 * Returns normalized configuration array with sensible defaults on failure
 *
 * @return array Configuration with keys: enabled, base_url, api_key, model, timeout, verify_ssl
 */
function ai_hf_config_load(): array
{
    $defaults = ai_hf_default_config();
    $configPath = ai_hf_config_path();

    if (!file_exists($configPath)) {
        return $defaults;
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        return $defaults;
    }

    $allSettings = @json_decode($json, true);
    if (!is_array($allSettings)) {
        return $defaults;
    }

    // Extract huggingface provider settings
    $data = $allSettings['providers']['huggingface'] ?? [];
    if (!is_array($data)) {
        return $defaults;
    }

    // Map from central config format to ai_hf format
    $config = [];
    $config['enabled'] = isset($data['enabled']) ? (bool)$data['enabled'] : $defaults['enabled'];
    $config['base_url'] = $defaults['base_url']; // Always use default HF API URL
    $config['api_key'] = isset($data['api_key']) ? trim((string)$data['api_key']) : $defaults['api_key'];
    // Support both new format (models object) and legacy (default_model)
    if (isset($data['models']) && is_array($data['models'])) {
        $config['model'] = trim((string)($data['models']['text'] ?? ''));
        $config['models'] = $data['models'];
    } else {
        $config['model'] = isset($data['default_model']) ? trim((string)$data['default_model']) : $defaults['model'];
        $config['models'] = ['text' => $config['model'], 'image' => '', 'vision' => ''];
    }

    // Timeout from generation_defaults or use default
    $timeout = $allSettings['generation_defaults']['timeout'] ?? $defaults['timeout'];
    $config['timeout'] = max(1, (int)$timeout);

    $config['verify_ssl'] = $defaults['verify_ssl'];

    return $config;
}

/**
 * Save Hugging Face configuration to central ai_settings.json
 * Updates only the providers.huggingface section
 *
 * @param array $new Partial or complete configuration data
 * @return bool True on success, false on failure
 */
function ai_hf_config_save(array $new): bool
{
    $configPath = ai_hf_config_path();
    
    // Load entire settings file
    $allSettings = [];
    if (file_exists($configPath)) {
        $json = @file_get_contents($configPath);
        if ($json !== false) {
            $decoded = @json_decode($json, true);
            if (is_array($decoded)) {
                $allSettings = $decoded;
            }
        }
    }
    
    // Ensure providers.huggingface exists
    if (!isset($allSettings['providers'])) {
        $allSettings['providers'] = [];
    }
    if (!isset($allSettings['providers']['huggingface'])) {
        $allSettings['providers']['huggingface'] = [];
    }
    
    $existing = $allSettings['providers']['huggingface'];
    
    // Update huggingface settings
    $allSettings['providers']['huggingface']['enabled'] = isset($new['enabled']) ? (bool)$new['enabled'] : ($existing['enabled'] ?? false);
    
    // Handle API key: only update if provided and non-empty
    if (isset($new['api_key']) && trim($new['api_key']) !== '') {
        $allSettings['providers']['huggingface']['api_key'] = trim($new['api_key']);
    } elseif (!isset($allSettings['providers']['huggingface']['api_key'])) {
        $allSettings['providers']['huggingface']['api_key'] = '';
    }
    
    // Handle model (map 'model' to 'default_model' for central config format)
    if (isset($new['model'])) {
        $allSettings['providers']['huggingface']['default_model'] = trim((string)$new['model']);
    } elseif (!isset($allSettings['providers']['huggingface']['default_model'])) {
        $allSettings['providers']['huggingface']['default_model'] = '';
    }
    
    // Update timestamp
    $allSettings['updated_at'] = date('Y-m-d H:i:s');

    // Write entire settings file
    $json = json_encode($allSettings, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        error_log('ai_hf_config_save: Failed to encode configuration to JSON');
        return false;
    }

    $result = @file_put_contents($configPath, $json, LOCK_EX);

    if ($result === false) {
        error_log('ai_hf_config_save: Failed to write configuration file');
        return false;
    }

    return true;
}

/**
 * Check if Hugging Face is properly configured
 *
 * @param array $config Configuration array
 * @return bool True if enabled and all required fields are non-empty
 */
function ai_hf_is_configured(array $config): bool
{
    if (!isset($config['enabled']) || !(bool)$config['enabled']) {
        return false;
    }

    $baseUrl = isset($config['base_url']) ? trim((string)$config['base_url']) : '';
    // Check both api_key and api_token for compatibility
    $apiKey = isset($config['api_key']) ? trim((string)$config['api_key']) : '';
    if ($apiKey === '' && isset($config['api_token'])) {
        $apiKey = trim((string)$config['api_token']);
    }

    // Check if at least one model is configured
    $hasModel = false;
    if (isset($config['models']) && is_array($config['models'])) {
        foreach ($config['models'] as $m) {
            if (trim((string)$m) !== '') {
                $hasModel = true;
                break;
            }
        }
    } elseif (isset($config['model']) && trim((string)$config['model']) !== '') {
        $hasModel = true;
    }

    return ($baseUrl !== '' && $apiKey !== '' && $hasModel);
}

/**
 * Execute inference request to Hugging Face API (OpenAI-compatible format)
 *
 * @param array $config Configuration array from ai_hf_config_load()
 * @param string $prompt Input text for the model
 * @param array $options Optional parameters for the model (e.g., max_tokens, temperature)
 * @return array Response array with keys: ok (bool), status (int|null), body (string|null), json (mixed), error (string|null)
 */
function ai_hf_infer(array $config, string $prompt, array $options = []): array
{
    // Check if configured
    if (!ai_hf_is_configured($config)) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'Hugging Face is not configured'
        ];
    }

    // Build request URL - new OpenAI-compatible endpoint
    $url = 'https://router.huggingface.co/v1/chat/completions';

    // Build request body in OpenAI format
    $requestBody = [
        'model' => $config['model'],
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $options['max_tokens'] ?? $options['max_new_tokens'] ?? 1000
    ];
    
    // Add optional parameters
    if (isset($options['temperature'])) {
        $requestBody['temperature'] = (float)$options['temperature'];
    }

    $jsonBody = json_encode($requestBody);
    if ($jsonBody === false) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'Failed to encode request body'
        ];
    }

    // Initialize cURL
    $ch = curl_init($url);
    if ($ch === false) {
        return [
            'ok' => false,
            'status' => null,
            'body' => null,
            'json' => null,
            'error' => 'Failed to initialize cURL'
        ];
    }

    // Set cURL options
    $headers = [
        'Authorization: Bearer ' . $config['api_key'],
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$config['timeout']);

    // SSL verification
    if (isset($config['verify_ssl']) && !(bool)$config['verify_ssl']) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }

    // Execute request
    $responseBody = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Handle cURL failure
    if ($responseBody === false) {
        $errorMessage = !empty($curlError) ? $curlError : 'cURL request failed';
        return [
            'ok' => false,
            'status' => $httpStatus !== 0 ? (int)$httpStatus : null,
            'body' => null,
            'json' => null,
            'error' => $errorMessage
        ];
    }

    // Try to decode JSON response
    $json = null;
    if (!empty($responseBody)) {
        $decoded = @json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $json = $decoded;
        }
    }

    // Determine success
    $ok = ($httpStatus >= 200 && $httpStatus < 300);
    $error = $ok ? null : 'Request failed with status ' . $httpStatus;

    return [
        'ok' => $ok,
        'status' => (int)$httpStatus,
        'body' => $responseBody,
        'json' => $json,
        'error' => $error
    ];
}

/**
 * Perform health check on Hugging Face endpoint
 * Tests connectivity with a simple GET request to base URL
 *
 * @return array Result array with keys: ok, statusCode, error, details
 */
function ai_hf_health_check(): array
{
    try {
        $settings = ai_hf_load_settings();

        // Check required fields
        if (empty($settings['base_url']) || empty($settings['api_token'])) {
            return [
                'ok'         => false,
                'statusCode' => null,
                'error'      => 'Missing Hugging Face base URL or API token.',
                'details'    => null,
            ];
        }

        // Execute simple GET request to base URL
        $result = ai_hf_http_request('');

        // Truncate body for details (max 120 chars)
        $details = null;
        if (!$result['ok'] && !empty($result['body'])) {
            $details = substr((string)$result['body'], 0, 120);
        }

        return [
            'ok'         => $result['ok'],
            'statusCode' => $result['statusCode'],
            'error'      => $result['error'],
            'details'    => $details,
        ];
    } catch (Exception $e) {
        error_log('ai_hf_health_check: Exception - ' . $e->getMessage());
        return [
            'ok'         => false,
            'statusCode' => null,
            'error'      => 'Unexpected error during Hugging Face health check.',
            'details'    => null,
        ];
    }
}

/**
 * Generate ALT text for an image using Hugging Face vision model
 * Sends image binary to HF Inference API and extracts caption
 *
 * @param string $imageAbsolutePath Absolute path to image file
 * @return array Result array with keys: ok (bool), alt (string on success), error (string on failure)
 */
function ai_hf_generate_alt(string $imageAbsolutePath): array
{
    // Validate file exists
    if (!file_exists($imageAbsolutePath)) {
        error_log('ai_hf_generate_alt: File does not exist: ' . $imageAbsolutePath);
        return [
            'ok' => false,
            'error' => 'Image file not found.'
        ];
    }

    // Validate file is an image
    $mimeType = @mime_content_type($imageAbsolutePath);
    if ($mimeType === false || strpos($mimeType, 'image/') !== 0) {
        error_log('ai_hf_generate_alt: File is not an image: ' . $imageAbsolutePath);
        return [
            'ok' => false,
            'error' => 'File is not a valid image.'
        ];
    }

    // Load settings and validate configuration
    $settings = ai_hf_load_settings();
    if (!$settings['enabled']) {
        return [
            'ok' => false,
            'error' => 'Hugging Face is not enabled.'
        ];
    }
    if (empty($settings['base_url']) || empty($settings['api_token']) || empty($settings['model'])) {
        return [
            'ok' => false,
            'error' => 'Hugging Face is not properly configured.'
        ];
    }

    // Read image binary
    $imageData = @file_get_contents($imageAbsolutePath);
    if ($imageData === false) {
        error_log('ai_hf_generate_alt: Failed to read image file: ' . $imageAbsolutePath);
        return [
            'ok' => false,
            'error' => 'Failed to read image file.'
        ];
    }

    // Build request path
    $model = rawurlencode($settings['model']);
    $path = '/models/' . $model;

    // Execute POST request with image binary
    $result = ai_hf_http_request($path, [
        'method' => 'POST',
        'body' => $imageData,
        'content_type' => 'application/octet-stream'
    ]);

    // Check request success
    if (!$result['ok']) {
        error_log('ai_hf_generate_alt: HTTP request failed: ' . ($result['error'] ?? 'Unknown error'));
        return [
            'ok' => false,
            'error' => $result['error'] ?? 'Failed to contact Hugging Face API.'
        ];
    }

    // Parse JSON response
    $responseData = null;
    if (!empty($result['body'])) {
        $responseData = @json_decode($result['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ai_hf_generate_alt: Failed to parse JSON response');
            return [
                'ok' => false,
                'error' => 'Invalid response from Hugging Face API.'
            ];
        }
    }

    // Extract caption from response
    $caption = null;
    if (is_array($responseData)) {
        // Try different response formats
        // Format 1: [{"generated_text": "..."}]
        if (isset($responseData[0]['generated_text'])) {
            $caption = $responseData[0]['generated_text'];
        }
        // Format 2: {"generated_text": "..."}
        elseif (isset($responseData['generated_text'])) {
            $caption = $responseData['generated_text'];
        }
        // Format 3: [{"caption": "..."}]
        elseif (isset($responseData[0]['caption'])) {
            $caption = $responseData[0]['caption'];
        }
        // Format 4: {"caption": "..."}
        elseif (isset($responseData['caption'])) {
            $caption = $responseData['caption'];
        }
    }

    if ($caption === null || trim($caption) === '') {
        error_log('ai_hf_generate_alt: No caption found in response: ' . $result['body']);
        return [
            'ok' => false,
            'error' => 'No caption generated by the model.'
        ];
    }

    // Normalize ALT text
    $alt = trim($caption);
    $alt = str_replace(["\r\n", "\r", "\n"], ' ', $alt);
    $alt = preg_replace('/\s+/', ' ', $alt);
    if (mb_strlen($alt) > 200) {
        $alt = mb_substr($alt, 0, 200);
    }

    return [
        'ok' => true,
        'alt' => $alt
    ];
}

/**
 * Generate text using Hugging Face text model
 * Sends prompt to HF Inference API and extracts generated text
 *
 * @param string $prompt Input prompt for text generation
 * @param array $options Optional parameters: 'params' => array of model parameters (temperature, max_new_tokens, etc.), 'no_cache' => bool to bypass cache
 * @return array Result array with keys: ok (bool), text (string on success), error (string on failure), cached (bool)
 */
function ai_hf_generate_text(string $prompt, array $options = []): array
{
    // Load settings and validate configuration
    $settings = ai_hf_load_settings();
    if (!$settings['enabled']) {
        return [
            'ok' => false,
            'error' => 'Hugging Face is not enabled.'
        ];
    }
    if (empty($settings['base_url']) || empty($settings['api_token']) || empty($settings['model'])) {
        return [
            'ok' => false,
            'error' => 'Hugging Face is not properly configured.'
        ];
    }

    // Validate prompt
    $prompt = trim($prompt);
    if ($prompt === '') {
        return [
            'ok' => false,
            'error' => 'Prompt cannot be empty.'
        ];
    }

    // Check if caching is disabled via options
    $useCache = !isset($options['no_cache']) || !$options['no_cache'];

    // Try to get cached response
    $cacheKey = null;
    if ($useCache) {
        $cacheKey = ai_hf_cache_key($prompt, $options['params'] ?? [], $settings['model']);
        $cached = ai_hf_cache_get($cacheKey);

        if ($cached !== null && isset($cached['text'])) {
            // Return cached response with flag
            return [
                'ok' => true,
                'text' => $cached['text'],
                'cached' => true
            ];
        }
    }

    // Build request for OpenAI-compatible endpoint
    $requestBody = [
        'model' => $settings['model'],
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $options['params']['max_new_tokens'] ?? $options['params']['max_tokens'] ?? 1000
    ];
    
    if (isset($options['params']['temperature'])) {
        $requestBody['temperature'] = (float)$options['params']['temperature'];
    }

    // Encode payload as JSON
    $json = json_encode($requestBody);
    if ($json === false) {
        error_log('ai_hf_generate_text: Failed to encode request payload');
        return [
            'ok' => false,
            'error' => 'Failed to encode HF request payload.'
        ];
    }

    // Use new OpenAI-compatible endpoint
    $url = 'https://router.huggingface.co/v1/chat/completions';

    // Execute POST request directly with cURL
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'error' => 'Failed to initialize cURL'];
    }

    $headers = [
        'Authorization: Bearer ' . $settings['api_token'],
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$settings['timeout']);

    $responseBody = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check request success
    if ($responseBody === false || $httpStatus < 200 || $httpStatus >= 300) {
        error_log('ai_hf_generate_text: HTTP request failed: status=' . $httpStatus);
        return [
            'ok' => false,
            'error' => 'Failed to contact Hugging Face API (HTTP ' . $httpStatus . ').'
        ];
    }

    // Parse JSON response
    $responseData = null;
    if (!empty($responseBody)) {
        $responseData = @json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ai_hf_generate_text: Failed to parse JSON response');
            return [
                'ok' => false,
                'error' => 'Invalid response from Hugging Face API.'
            ];
        }
    }

    // Extract generated text from OpenAI-compatible response
    $text = null;
    if (is_array($responseData)) {
        // New OpenAI format: {"choices": [{"message": {"content": "..."}}]}
        if (isset($responseData['choices'][0]['message']['content'])) {
            $text = $responseData['choices'][0]['message']['content'];
        }
        // Legacy format 1: [{"generated_text": "..."}]
        elseif (isset($responseData[0]['generated_text'])) {
            $text = $responseData[0]['generated_text'];
        }
        // Legacy format 2: {"generated_text": "..."}
        elseif (isset($responseData['generated_text'])) {
            $text = $responseData['generated_text'];
        }
        // Check for error in response
        elseif (isset($responseData['error'])) {
            return [
                'ok' => false,
                'error' => $responseData['error']['message'] ?? $responseData['error']
            ];
        }
    }

    if ($text === null || trim($text) === '') {
        error_log('ai_hf_generate_text: No text found in response: ' . $result['body']);
        return [
            'ok' => false,
            'error' => 'No text generated by the model.'
        ];
    }

    // Normalize generated text
    $text = trim($text);
    
    // Remove AI thinking/reasoning tags (SmolLM3 and similar models)
    $text = preg_replace('/<think>.*?<\/think>/s', '', $text);
    $text = trim($text);
    
    // Replace multiple newlines with double newline (preserve paragraph breaks)
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    // Clamp to max length to avoid extreme outputs
    if (mb_strlen($text) > 8000) {
        $text = mb_substr($text, 0, 8000);
    }

    // Cache the successful response
    if ($useCache && $cacheKey !== null) {
        ai_hf_cache_set($cacheKey, ['text' => $text]);
    }

    return [
        'ok' => true,
        'text' => $text,
        'cached' => false
    ];
}
