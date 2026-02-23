<?php
/**
 * JTB AI Core
 * Central AI integration layer for Jessie Theme Builder
 * Manages communication with HuggingFace and Cloud AI providers
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Core
{
    // ========================================
    // Singleton Pattern
    // ========================================

    private static ?self $instance = null;

    /** @var string|null Last error message */
    private ?string $lastError = null;

    /** @var array Usage statistics */
    private array $usageStats = [
        'requests' => 0,
        'tokens_used' => 0,
        'cache_hits' => 0,
        'errors' => 0,
        'total_time_ms' => 0
    ];

    /** @var string Current AI provider: 'huggingface', 'openai', 'anthropic', 'deepseek' */
    private string $provider = 'huggingface';

    /** @var array Configuration cache */
    private array $config = [];

    /** @var bool Whether AI is enabled and configured */
    private bool $configured = false;

    // ========================================
    // Singleton Methods
    // ========================================

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    // ========================================
    // Configuration Methods
    // ========================================

    /**
     * Load AI configuration from central settings
     */
    private function loadConfiguration(): void
    {
        // Load HuggingFace settings
        if (function_exists('ai_hf_config_load')) {
            $hfConfig = ai_hf_config_load();
            if (ai_hf_is_configured($hfConfig)) {
                $this->config['huggingface'] = $hfConfig;
                $this->provider = 'huggingface';
                $this->configured = true;
            }
        }

        // Check for central AI settings
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            if (is_array($settings)) {
                // Check OpenAI
                if (!empty($settings['providers']['openai']['enabled']) &&
                    !empty($settings['providers']['openai']['api_key'])) {
                    $this->config['openai'] = $settings['providers']['openai'];
                    if (!$this->configured) {
                        $this->provider = 'openai';
                        $this->configured = true;
                    }
                }

                // Check Anthropic
                if (!empty($settings['providers']['anthropic']['enabled']) &&
                    !empty($settings['providers']['anthropic']['api_key'])) {
                    $this->config['anthropic'] = $settings['providers']['anthropic'];
                    if (!$this->configured) {
                        $this->provider = 'anthropic';
                        $this->configured = true;
                    }
                }

                // Check Google Gemini
                if (!empty($settings['providers']['google']['enabled']) &&
                    !empty($settings['providers']['google']['api_key'])) {
                    $this->config['google'] = $settings['providers']['google'];
                    if (!$this->configured) {
                        $this->provider = 'google';
                        $this->configured = true;
                    }
                }

                // Check DeepSeek via OpenRouter
                if (!empty($settings['providers']['deepseek']['enabled']) &&
                    !empty($settings['providers']['deepseek']['api_key'])) {
                    $this->config['deepseek'] = $settings['providers']['deepseek'];
                    if (!$this->configured) {
                        $this->provider = 'deepseek';
                        $this->configured = true;
                    }
                }

                // Store generation defaults
                if (!empty($settings['generation_defaults'])) {
                    $this->config['defaults'] = $settings['generation_defaults'];
                }

                // Respect default_provider setting
                if (!empty($settings['default_provider'])) {
                    $preferred = $settings['default_provider'];
                    if (isset($this->config[$preferred]) && !empty($this->config[$preferred]['api_key'])) {
                        $this->provider = $preferred;
                        $this->configured = true;
                    }
                }
            }
        }
    }

    /**
     * Check if AI is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Get current AI provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Set preferred provider
     */
    public function setProvider(string $provider): bool
    {
        $validProviders = ['huggingface', 'openai', 'anthropic', 'google', 'deepseek'];
        if (!in_array($provider, $validProviders)) {
            $this->lastError = "Invalid provider: {$provider}";
            return false;
        }

        if (!isset($this->config[$provider])) {
            $this->lastError = "Provider {$provider} is not configured";
            return false;
        }

        $this->provider = $provider;
        return true;
    }

    /**
     * Get available providers
     */

    /**
     * Get classified info about the last error.
     */
    public function getLastErrorInfo(): ?array
    {
        if (empty($this->lastError)) return null;
        return self::classifyError($this->lastError, $this->provider);
    }

    public function getAvailableProviders(): array
    {
        $available = [];
        foreach (['huggingface', 'openai', 'anthropic', 'google', 'deepseek'] as $provider) {
            if (isset($this->config[$provider])) {
                $available[] = $provider;
            }
        }
        return $available;
    }

    // ========================================
    // Query Methods
    // ========================================

    /**
     * Execute AI query
     *
     * @param string $prompt The prompt to send
     * @param array $options Optional parameters:
     *   - system_prompt: System message for context
     *   - max_tokens: Maximum tokens to generate (default: 2000)
     *   - temperature: Creativity level 0-1 (default: 0.7)
     *   - provider: Override current provider
     *   - no_cache: Skip cache (default: false)
     *   - json_mode: Request JSON response (default: false)
     * @return array Response with keys: ok, text, json, cached, error, tokens_used, time_ms
     */
    public function query(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        $this->usageStats['requests']++;

        if (!$this->isConfigured()) {
            $this->lastError = 'AI is not configured. Please configure an AI provider.';
            $this->usageStats['errors']++;
            return [
                'ok' => false,
                'text' => null,
                'json' => null,
                'cached' => false,
                'error' => $this->lastError,
                'tokens_used' => 0,
                'time_ms' => 0
            ];
        }

        // Determine provider
        $provider = $options['provider'] ?? $this->provider;

        // Get generation defaults
        $defaults = $this->config['defaults'] ?? [];
        $maxTokens = $options['max_tokens'] ?? $defaults['max_tokens'] ?? 2000;
        $temperature = $options['temperature'] ?? $defaults['temperature'] ?? 0.7;
        $systemPrompt = $options['system_prompt'] ?? '';
        $noCache = $options['no_cache'] ?? false;
        $jsonMode = $options['json_mode'] ?? false;
        $model = $options['model'] ?? null; // Model override from caller

        // Build full prompt with system context
        $fullPrompt = $prompt;
        if (!empty($systemPrompt)) {
            $fullPrompt = "System: {$systemPrompt}\n\nUser: {$prompt}";
        }

        // Call appropriate provider
        file_put_contents('/tmp/ai_core_debug.log', date('H:i:s') . " QUERY - provider=$provider, model=" . ($model ?? 'NULL') . ", max_tokens=$maxTokens\n", FILE_APPEND);
        $result = match($provider) {
            'huggingface' => $this->callHuggingFace($fullPrompt, [
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'no_cache' => $noCache
            ]),
            'openai' => $this->callOpenAI($prompt, $systemPrompt, [
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'json_mode' => $jsonMode,
                'model' => $model
            ]),
            'anthropic' => $this->callAnthropic($prompt, $systemPrompt, [
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'model' => $model
            ]),
            'google' => $this->callGoogle($prompt, $systemPrompt, [
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'json_mode' => $jsonMode,
                'model' => $model
            ]),
            'deepseek' => $this->callDeepSeek($prompt, $systemPrompt, [
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'json_mode' => $jsonMode,
                'model' => $model
            ]),
            default => ['ok' => false, 'error' => 'Unknown provider: ' . $provider]
        };

        // Calculate time
        $endTime = microtime(true);
        $timeMs = (int)(($endTime - $startTime) * 1000);
        $this->usageStats['total_time_ms'] += $timeMs;

        // Update stats
        if ($result['ok']) {
            if (!empty($result['cached'])) {
                $this->usageStats['cache_hits']++;
            }
            if (!empty($result['tokens_used'])) {
                $this->usageStats['tokens_used'] += $result['tokens_used'];
            }
        } else {
            $this->usageStats['errors']++;
            $this->lastError = $result['error'] ?? 'Unknown error';
            // Classify the error for user-friendly display
            $result['error_info'] = self::classifyError(
                $this->lastError,
                $result['provider'] ?? $provider,
                $result['http_code'] ?? 0
            );
        }

        // Parse JSON if requested and response is valid
        $result['json'] = null;
        if ($result['ok'] && $jsonMode && !empty($result['text'])) {
            $result['json'] = $this->parseJsonResponse($result['text']);
        }

        $result['time_ms'] = $timeMs;
        $result['provider'] = $provider;
        $result['model'] = $model ?? ($this->config[$provider]['default_model'] ?? 'unknown');
        return $result;
    }

    /**
     * Execute query with automatic retry on failure
     *
     * @param string $prompt The prompt to send
     * @param int $maxRetries Maximum retry attempts (default: 3)
     * @param array $options Query options
     * @return array Response array
     */
    public function queryWithRetry(string $prompt, int $maxRetries = 3, array $options = []): array
    {
        $lastResult = null;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;
            $result = $this->query($prompt, $options);

            if ($result['ok']) {
                return $result;
            }

            $lastResult = $result;

            // Check if error is retryable
            $error = strtolower($result['error'] ?? '');
            $retryable = (
                strpos($error, 'timeout') !== false ||
                strpos($error, 'rate limit') !== false ||
                strpos($error, '503') !== false ||
                strpos($error, '502') !== false ||
                strpos($error, '429') !== false
            );

            if (!$retryable) {
                break;
            }

            // Exponential backoff: 1s, 2s, 4s
            if ($attempt < $maxRetries) {
                usleep((int)(pow(2, $attempt - 1) * 1000000));
            }
        }

        return $lastResult ?? [
            'ok' => false,
            'text' => null,
            'json' => null,
            'cached' => false,
            'error' => 'All retry attempts failed',
            'tokens_used' => 0,
            'time_ms' => 0
        ];
    }

    /**
     * Stream AI response with callback
     *
     * @param string $prompt The prompt to send
     * @param callable $callback Function called with each text chunk
     * @param array $options Query options
     */
    public function streamQuery(string $prompt, callable $callback, array $options = []): void
    {
        // For now, fall back to regular query and simulate streaming
        // Full streaming implementation would require SSE or WebSocket support
        $result = $this->query($prompt, $options);

        if ($result['ok'] && !empty($result['text'])) {
            // Simulate streaming by sending chunks
            $text = $result['text'];
            $chunkSize = 50;
            $chunks = str_split($text, $chunkSize);

            foreach ($chunks as $chunk) {
                $callback([
                    'type' => 'chunk',
                    'text' => $chunk,
                    'done' => false
                ]);
            }

            $callback([
                'type' => 'done',
                'text' => '',
                'done' => true,
                'full_text' => $text
            ]);
        } else {
            $callback([
                'type' => 'error',
                'text' => '',
                'done' => true,
                'error' => $result['error'] ?? 'Unknown error'
            ]);
        }
    }

    // ========================================
    // Provider-Specific Methods
    // ========================================

    /**
     * Call HuggingFace API
     */


    /**
     * Check health/status of all configured AI providers.
     * Returns array of provider statuses with balance info where available.
     * Uses minimal API calls (tiny prompt or balance endpoints).
     */
    public function checkProviderHealth(): array
    {
        $results = [];
        $providers = ['openai', 'anthropic', 'deepseek', 'google'];

        foreach ($providers as $provider) {
            $config = $this->config[$provider] ?? [];
            if (empty($config['api_key'])) {
                $results[$provider] = [
                    'status' => 'not_configured',
                    'message' => 'No API key',
                    'icon' => '⚙️',
                ];
                continue;
            }

            try {
                $result = $this->probeProvider($provider, $config);
                $results[$provider] = $result;
            } catch (\Throwable $e) {
                $results[$provider] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'icon' => '❌',
                ];
            }
        }

        return $results;
    }

    /**
     * Probe a single provider for health status.
     */
    private function probeProvider(string $provider, array $config): array
    {
        switch ($provider) {
            case 'deepseek':
                return $this->probeDeepSeek($config);
            case 'anthropic':
                return $this->probeAnthropic($config);
            case 'openai':
                return $this->probeOpenAI($config);
            case 'google':
                return $this->probeGoogle($config);
            default:
                return ['status' => 'unknown', 'message' => 'Unknown provider', 'icon' => '❓'];
        }
    }

    private function probeDeepSeek(array $config): array
    {
        $baseUrl = rtrim($config['base_url'] ?? 'https://api.deepseek.com', '/');

        // DeepSeek has a balance endpoint
        $ch = curl_init($baseUrl . '/user/balance');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $config['api_key']],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $balance = null;
            if (!empty($data['balance_infos'])) {
                foreach ($data['balance_infos'] as $bi) {
                    if (($bi['currency'] ?? '') === 'USD') {
                        $balance = (float)($bi['total_balance'] ?? 0);
                    }
                }
            }
            if ($balance !== null) {
                if ($balance < 0.10) {
                    return ['status' => 'low_balance', 'balance' => $balance, 'currency' => 'USD',
                            'message' => "Balance: \${$balance} — critically low!", 'icon' => '🔴'];
                } elseif ($balance < 1.00) {
                    return ['status' => 'warning', 'balance' => $balance, 'currency' => 'USD',
                            'message' => "Balance: \${$balance} — running low", 'icon' => '🟡'];
                } else {
                    return ['status' => 'ok', 'balance' => $balance, 'currency' => 'USD',
                            'message' => "Balance: \${$balance}", 'icon' => '🟢'];
                }
            }
            return ['status' => 'ok', 'message' => 'Connected', 'icon' => '🟢'];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return ['status' => 'auth_error', 'message' => 'Invalid API key', 'icon' => '🔑'];
        }
        return ['status' => 'error', 'message' => "HTTP {$httpCode}", 'icon' => '❌'];
    }

    private function probeAnthropic(array $config): array
    {
        // Anthropic has no balance API. Send a minimal request to check auth + credits.
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 5,
                'messages' => [['role' => 'user', 'content' => 'Hi']],
            ]),
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $config['api_key'],
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200) {
            return ['status' => 'ok', 'message' => 'Connected & working', 'icon' => '🟢'];
        }

        $errorMsg = $data['error']['message'] ?? '';
        $errorType = $data['error']['type'] ?? '';
        $errorLower = strtolower($errorMsg);

        if (str_contains($errorLower, 'credit balance') || str_contains($errorLower, 'billing')
            || $httpCode === 402) {
            return ['status' => 'no_credits', 'message' => 'No credits — top up required',
                    'icon' => '🔴', 'link' => 'https://console.anthropic.com/settings/billing'];
        }
        if ($httpCode === 401 || str_contains($errorLower, 'invalid') || $errorType === 'authentication_error') {
            return ['status' => 'auth_error', 'message' => 'Invalid API key', 'icon' => '🔑'];
        }
        if ($httpCode === 429 || str_contains($errorLower, 'rate')) {
            return ['status' => 'rate_limited', 'message' => 'Rate limited — wait and retry',
                    'icon' => '🟡'];
        }
        if ($httpCode >= 500) {
            return ['status' => 'server_error', 'message' => 'Server error — temporary',
                    'icon' => '🟡'];
        }
        if (str_contains($errorLower, 'not_found') || str_contains($errorLower, 'model')) {
            // Model not available but key works — still connected
            return ['status' => 'ok', 'message' => 'Connected (default model may need update)',
                    'icon' => '🟢'];
        }

        return ['status' => 'error', 'message' => $errorMsg ?: "HTTP {$httpCode}", 'icon' => '❌'];
    }

    private function probeOpenAI(array $config): array
    {
        // OpenAI: list models endpoint is cheap and checks auth
        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $config['api_key']],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            // Key is valid. OpenAI doesn't expose balance via API.
            // Do a minimal completion to test quota.
            $ch2 = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch2, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4o-mini',
                    'max_tokens' => 5,
                    'messages' => [['role' => 'user', 'content' => 'Hi']],
                ]),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $config['api_key'],
                    'Content-Type: application/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]);
            $resp2 = curl_exec($ch2);
            $http2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);

            if ($http2 === 200) {
                return ['status' => 'ok', 'message' => 'Connected & working', 'icon' => '🟢'];
            }

            $data2 = json_decode($resp2, true);
            $errMsg = strtolower($data2['error']['message'] ?? '');
            if (str_contains($errMsg, 'quota') || str_contains($errMsg, 'billing') || $http2 === 429) {
                if (str_contains($errMsg, 'exceeded') || str_contains($errMsg, 'insufficient_quota')) {
                    return ['status' => 'no_credits', 'message' => 'Quota exceeded — add credits',
                            'icon' => '🔴', 'link' => 'https://platform.openai.com/account/billing'];
                }
                return ['status' => 'rate_limited', 'message' => 'Rate limited — try later',
                        'icon' => '🟡'];
            }
            // Models list works but completion fails — partial
            return ['status' => 'ok', 'message' => 'Connected (check model access)',
                    'icon' => '🟢'];
        }

        $data = json_decode($response, true);
        $errMsg = strtolower($data['error']['message'] ?? '');

        if ($httpCode === 401) {
            return ['status' => 'auth_error', 'message' => 'Invalid API key', 'icon' => '🔑'];
        }
        if (str_contains($errMsg, 'quota') || str_contains($errMsg, 'exceeded')) {
            return ['status' => 'no_credits', 'message' => 'Quota exceeded',
                    'icon' => '🔴', 'link' => 'https://platform.openai.com/account/billing'];
        }

        return ['status' => 'error', 'message' => $errMsg ?: "HTTP {$httpCode}", 'icon' => '❌'];
    }

    private function probeGoogle(array $config): array
    {
        // Google: list models to check key validity
        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models?key=' . $config['api_key'] . '&pageSize=1');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            // Key valid. Google Gemini API is free tier (with limits), so usually OK.
            return ['status' => 'ok', 'message' => 'Connected & working', 'icon' => '🟢'];
        }

        $data = json_decode($response, true);
        $errMsg = $data['error']['message'] ?? '';
        $errStatus = $data['error']['status'] ?? '';

        if ($httpCode === 400 && str_contains(strtolower($errMsg), 'api key')) {
            return ['status' => 'auth_error', 'message' => 'Invalid API key', 'icon' => '🔑'];
        }
        if ($httpCode === 403) {
            if (str_contains(strtolower($errMsg), 'enable') || str_contains(strtolower($errMsg), 'not been used')) {
                return ['status' => 'not_enabled', 'message' => 'API not enabled in Google Cloud Console',
                        'icon' => '⚙️', 'link' => 'https://console.cloud.google.com/apis'];
            }
            return ['status' => 'auth_error', 'message' => 'Access denied — check API key permissions',
                    'icon' => '🔑'];
        }
        if ($httpCode === 429) {
            return ['status' => 'rate_limited', 'message' => 'Quota exceeded',
                    'icon' => '🟡'];
        }

        return ['status' => 'error', 'message' => $errMsg ?: "HTTP {$httpCode}", 'icon' => '❌'];
    }

    /**
     * Classify an AI provider error into a user-friendly category.
     * Returns: ['type' => string, 'title' => string, 'message' => string, 'action' => string]
     */
    public static function classifyError(string $errorMsg, string $provider = '', int $httpCode = 0): array
    {
        $errorLower = strtolower($errorMsg);
        $providerName = ucfirst($provider ?: 'AI');

        // No credits / insufficient balance
        if (str_contains($errorLower, 'credit balance') || str_contains($errorLower, 'insufficient_quota')
            || str_contains($errorLower, 'billing') || str_contains($errorLower, 'payment required')
            || str_contains($errorLower, 'exceeded your current quota') || str_contains($errorLower, 'insufficient balance')
            || str_contains($errorLower, 'account has insufficient') || $httpCode === 402) {
            $links = [
                'anthropic' => 'https://console.anthropic.com/settings/billing',
                'openai'    => 'https://platform.openai.com/account/billing',
                'deepseek'  => 'https://platform.deepseek.com/top_up',
                'google'    => 'https://console.cloud.google.com/billing',
            ];
            $link = $links[$provider] ?? '';
            return [
                'type'    => 'no_credits',
                'title'   => "{$providerName}: No Credits",
                'message' => "Your {$providerName} account has run out of credits. Top up your balance or switch to a different AI provider.",
                'action'  => $link ? "Top up at: {$link}" : "Add credits to your {$providerName} account.",
                'link'    => $link,
            ];
        }

        // Rate limiting
        if (str_contains($errorLower, 'rate limit') || str_contains($errorLower, 'too many requests')
            || str_contains($errorLower, 'rate_limit') || $httpCode === 429) {
            return [
                'type'    => 'rate_limit',
                'title'   => "{$providerName}: Rate Limited",
                'message' => "Too many requests to {$providerName}. Wait a moment and try again, or switch to a different provider.",
                'action'  => 'Wait 30-60 seconds, then retry.',
            ];
        }

        // Authentication errors
        if (str_contains($errorLower, 'invalid.*api.key') || str_contains($errorLower, 'authentication')
            || str_contains($errorLower, 'unauthorized') || str_contains($errorLower, 'invalid x-api-key')
            || str_contains($errorLower, 'api key not valid') || $httpCode === 401 || $httpCode === 403) {
            return [
                'type'    => 'auth_error',
                'title'   => "{$providerName}: Authentication Failed",
                'message' => "The API key for {$providerName} is invalid or expired. Update it in Settings → AI Configuration.",
                'action'  => 'Go to Settings → AI Configuration to update your API key.',
            ];
        }

        // Not configured
        if (str_contains($errorLower, 'not configured') || str_contains($errorLower, 'api key not')) {
            return [
                'type'    => 'not_configured',
                'title'   => "{$providerName}: Not Configured",
                'message' => "{$providerName} is not set up. Add your API key in Settings → AI Configuration.",
                'action'  => 'Go to Settings → AI Configuration.',
            ];
        }

        // Model not found
        if (str_contains($errorLower, 'model not found') || str_contains($errorLower, 'does not exist')
            || str_contains($errorLower, 'invalid model') || str_contains($errorLower, 'not_found_error')) {
            return [
                'type'    => 'model_error',
                'title'   => "{$providerName}: Model Not Available",
                'message' => "The selected AI model is not available. It may have been deprecated or your plan doesn't include it.",
                'action'  => 'Try a different model or check your plan.',
            ];
        }

        // Content policy / safety
        if (str_contains($errorLower, 'content policy') || str_contains($errorLower, 'safety')
            || str_contains($errorLower, 'blocked') || str_contains($errorLower, 'content_filter')) {
            return [
                'type'    => 'content_blocked',
                'title'   => "{$providerName}: Content Blocked",
                'message' => "The AI provider blocked this request due to content policy. Try rephrasing your prompt.",
                'action'  => 'Modify your prompt and try again.',
            ];
        }

        // Timeout
        if (str_contains($errorLower, 'timeout') || str_contains($errorLower, 'timed out')
            || str_contains($errorLower, 'deadline exceeded') || $httpCode === 408 || $httpCode === 504) {
            return [
                'type'    => 'timeout',
                'title'   => "{$providerName}: Timeout",
                'message' => "The AI took too long to respond. The server may be overloaded.",
                'action'  => 'Try again, or switch to a faster model.',
            ];
        }

        // Server errors
        if ($httpCode >= 500 || str_contains($errorLower, 'server error') || str_contains($errorLower, 'overloaded')
            || str_contains($errorLower, 'internal error') || str_contains($errorLower, 'service unavailable')) {
            return [
                'type'    => 'server_error',
                'title'   => "{$providerName}: Server Error",
                'message' => "The {$providerName} servers are experiencing issues. This is usually temporary.",
                'action'  => 'Wait a few minutes and try again.',
            ];
        }

        // cURL / network errors
        if (str_contains($errorLower, 'curl') || str_contains($errorLower, 'connection')
            || str_contains($errorLower, 'resolve host') || str_contains($errorLower, 'network')) {
            return [
                'type'    => 'network_error',
                'title'   => 'Network Error',
                'message' => "Could not connect to {$providerName}. Check your internet connection.",
                'action'  => 'Verify internet connectivity and try again.',
            ];
        }

        // Generic fallback
        return [
            'type'    => 'unknown',
            'title'   => "{$providerName}: Error",
            'message' => $errorMsg,
            'action'  => 'Try again or switch to a different AI provider.',
        ];
    }

    private function callHuggingFace(string $prompt, array $options): array
    {
        if (!function_exists('ai_hf_generate_text')) {
            require_once CMS_ROOT . '/core/ai_hf.php';
        }

        $result = ai_hf_generate_text($prompt, [
            'params' => [
                'max_new_tokens' => $options['max_tokens'] ?? 2000,
                'temperature' => $options['temperature'] ?? 0.7
            ],
            'no_cache' => $options['no_cache'] ?? false
        ]);

        return [
            'ok' => $result['ok'] ?? false,
            'text' => $result['text'] ?? null,
            'cached' => $result['cached'] ?? false,
            'error' => $result['error'] ?? null,
            'tokens_used' => 0 // HF doesn't return token count
        ];
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $prompt, string $systemPrompt, array $options): array
    {
        $config = $this->config['openai'] ?? [];
        if (empty($config['api_key'])) {
            return ['ok' => false, 'error' => 'OpenAI API key not configured'];
        }

        // Model can be overridden via options
        $model = $options['model'] ?? $config['model'] ?? $config['default_model'] ?? 'gpt-4o-mini';

        // o1 models use different parameters
        // o1 and gpt-5 models use max_completion_tokens instead of max_tokens
        $isReasoningModel = (strpos($model, "o1") === 0) || (strpos($model, "gpt-5") === 0);
        $messages = [];

        // o1 models don't support system role - prepend to user message
        if ($isReasoningModel && !empty($systemPrompt)) {
            $messages[] = ['role' => 'user', 'content' => $systemPrompt . "\n\n" . $prompt];
        } else {
            if (!empty($systemPrompt)) {
                $messages[] = ['role' => 'system', 'content' => $systemPrompt];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];
        }

        $requestBody = [
            'model' => $model,
            'messages' => $messages
        ];

        // o1 models use max_completion_tokens, others use max_tokens
        if ($isReasoningModel) {
            $requestBody['max_completion_tokens'] = $options['max_tokens'] ?? 16000;
        } else {
            $requestBody['max_tokens'] = $options['max_tokens'] ?? 2000;
            $requestBody['temperature'] = $options['temperature'] ?? 0.7;
        }

        if (!empty($options['json_mode'])) {
            $requestBody['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $config['api_key'],
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);

        // Debug: log Anthropic response for troubleshooting
        @file_put_contents('/tmp/ai_anthropic_debug.log', date('H:i:s') . " HTTP={$httpCode} model={$model} resp_len=" . strlen($response) . "\n" . substr($response, 0, 1000) . "\n\n", FILE_APPEND);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            return ['ok' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
        }

        $text = $data['choices'][0]['message']['content'] ?? null;
        $tokensUsed = ($data['usage']['total_tokens'] ?? 0);

        return [
            'ok' => !empty($text),
            'text' => $text,
            'cached' => false,
            'error' => empty($text) ? 'No response from model' : null,
            'tokens_used' => $tokensUsed
        ];
    }

    /**
     * Call Anthropic (Claude) API
     */
    private function callAnthropic(string $prompt, string $systemPrompt, array $options): array
    {
        $config = $this->config['anthropic'] ?? [];
        if (empty($config['api_key'])) {
            return ['ok' => false, 'error' => 'Anthropic API key not configured'];
        }

        // Model can be overridden via options
        // Default to Claude Opus 4.5 (best for theme generation)
        $model = $options['model'] ?? $config['model'] ?? $config['default_model'] ?? 'claude-opus-4-5-20251101';

        $requestBody = [
            'model' => $model,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        if (!empty($systemPrompt)) {
            $requestBody['system'] = $systemPrompt;
        }

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $config['api_key'],
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_CONNECTTIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            return ['ok' => false, 'error' => $errorMsg];
        }

        $text = $data['content'][0]['text'] ?? null;
        $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);
        $stopReason = $data['stop_reason'] ?? null;

        return [
            'ok' => !empty($text),
            'text' => $text,
            'cached' => false,
            'error' => empty($text) ? 'No response from model' : null,
            'tokens_used' => $tokensUsed,
            'stop_reason' => $stopReason,
            'output_tokens' => $data['usage']['output_tokens'] ?? 0
        ];
    }

    /**
     * Call DeepSeek API (direct or via OpenRouter)
     */
    private function callDeepSeek(string $prompt, string $systemPrompt, array $options): array
    {
        $config = $this->config['deepseek'] ?? [];
        if (empty($config['api_key'])) {
            return ['ok' => false, 'error' => 'DeepSeek API key not configured'];
        }

        // Use base_url from config, default to DeepSeek direct API
        $baseUrl = $config['base_url'] ?? 'https://api.deepseek.com/v1';
        $apiUrl = rtrim($baseUrl, '/') . '/chat/completions';

        // Model can be overridden via options
        $model = $options['model'] ?? $config['model'] ?? $config['default_model'] ?? 'deepseek-chat';

        // Map friendly names to DeepSeek API model names
        $modelMap = [
            'deepseek-v3' => 'deepseek-chat',
            'deepseek-r1' => 'deepseek-reasoner',
            'deepseek-coder-v3' => 'deepseek-coder',
            'deepseek-code-v3' => 'deepseek-coder',
        ];
        $model = $modelMap[$model] ?? $model;
        $isReasoner = ($model === 'deepseek-reasoner');
        $messages = [];

        if ($isReasoner) {
            // DeepSeek R1: NO system role, merge into user message
            $userContent = $prompt;
            if (!empty($systemPrompt)) {
                $userContent = $systemPrompt . "\n\n" . $prompt;
            }
            $messages[] = ['role' => 'user', 'content' => $userContent];
        } else {
            if (!empty($systemPrompt)) {
                $messages[] = ['role' => 'system', 'content' => $systemPrompt];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];
        }

        $requestBody = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 8000,
        ];

        // DeepSeek R1: no temperature, no json_mode
        if (!$isReasoner) {
            $requestBody['temperature'] = $options['temperature'] ?? 0.7;
            if (!empty($options['json_mode'])) {
                $requestBody['response_format'] = ['type' => 'json_object'];
            }
        }

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $config['api_key'],
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $options['timeout'] ?? 180,
            CURLOPT_CONNECTTIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            return ['ok' => false, 'error' => $errorMsg];
        }

        $text = $data['choices'][0]['message']['content'] ?? null;
        $tokensUsed = ($data['usage']['total_tokens'] ?? 0);

        // DeepSeek R1: strip any <think> tags that leak into content
        if ($isReasoner && $text) {
            $text = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $text);
            $text = trim($text);
        }

        return [
            'ok' => !empty($text),
            'text' => $text,
            'cached' => false,
            'error' => empty($text) ? 'No response from model' : null,
            'tokens_used' => $tokensUsed
        ];
    }

    /**
     * Call Google Gemini API
     */
    private function callGoogle(string $prompt, string $systemPrompt, array $options): array
    {
        $config = $this->config['google'] ?? [];
        if (empty($config['api_key'])) {
            return ['ok' => false, 'error' => 'Google API key not configured'];
        }

        // Model can be overridden via options
        $model = $options['model'] ?? $config['model'] ?? $config['default_model'] ?? 'gemini-2.0-flash';

        // Build contents array
        $contents = [];

        // Add system instruction if provided
        $systemInstruction = null;
        if (!empty($systemPrompt)) {
            $systemInstruction = ['parts' => [['text' => $systemPrompt]]];
        }

        // Add user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]]
        ];

        $requestBody = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
                'temperature' => $options['temperature'] ?? 0.7,
            ]
        ];

        if ($systemInstruction) {
            $requestBody['systemInstruction'] = $systemInstruction;
        }

        // JSON mode for Gemini
        if (!empty($options['json_mode'])) {
            $requestBody['generationConfig']['responseMimeType'] = 'application/json';
        }

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $config['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            return ['ok' => false, 'error' => $errorMsg];
        }

        // Extract text from Gemini response
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        $tokensUsed = ($data['usageMetadata']['totalTokenCount'] ?? 0);

        return [
            'ok' => !empty($text),
            'text' => $text,
            'cached' => false,
            'error' => empty($text) ? 'No response from model' : null,
            'tokens_used' => $tokensUsed
        ];
    }

    // ========================================
    // Response Parsing Methods
    // ========================================

    /**
     * Parse JSON from AI response text
     */
    private function parseJsonResponse(string $text): ?array
    {
        // Try direct parse first
        $json = @json_decode($text, true);
        if (is_array($json)) {
            return $json;
        }

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)\n?```/', $text, $matches)) {
            $json = @json_decode(trim($matches[1]), true);
            if (is_array($json)) {
                return $json;
            }
        }

        // Try to find JSON object or array in text
        if (preg_match('/(\{[\s\S]*\}|\[[\s\S]*\])/', $text, $matches)) {
            $json = @json_decode($matches[1], true);
            if (is_array($json)) {
                return $json;
            }
        }

        return null;
    }

    /**
     * Validate AI response structure
     */
    public function validateResponse(array $response): bool
    {
        if (!$response['ok']) {
            return false;
        }

        if (empty($response['text']) && empty($response['json'])) {
            return false;
        }

        return true;
    }

    // ========================================
    // Error and Stats Methods
    // ========================================

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Clear last error
     */
    public function clearError(): void
    {
        $this->lastError = null;
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(): array
    {
        return $this->usageStats;
    }

    /**
     * Reset usage statistics
     */
    public function resetStats(): void
    {
        $this->usageStats = [
            'requests' => 0,
            'tokens_used' => 0,
            'cache_hits' => 0,
            'errors' => 0,
            'total_time_ms' => 0
        ];
    }

    /**
     * Get configuration info (without sensitive data)
     */
    public function getConfigInfo(): array
    {
        $info = [
            'configured' => $this->configured,
            'current_provider' => $this->provider,
            'available_providers' => $this->getAvailableProviders(),
            'defaults' => $this->config['defaults'] ?? []
        ];

        // Add provider-specific info (without API keys)
        foreach ($this->getAvailableProviders() as $provider) {
            $config = $this->config[$provider] ?? [];
            $info['providers'][$provider] = [
                'enabled' => true,
                'model' => $config['model'] ?? $config['default_model'] ?? 'default',
                'has_api_key' => !empty($config['api_key'])
            ];
        }

        return $info;
    }

    // ========================================
    // Utility Methods
    // ========================================

    /**
     * Test AI connection
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);

        $result = $this->query('Reply with only the word "OK" and nothing else.', [
            'max_tokens' => 10,
            'temperature' => 0,
            'no_cache' => true
        ]);

        $endTime = microtime(true);
        $latency = (int)(($endTime - $startTime) * 1000);

        return [
            'ok' => $result['ok'] && stripos($result['text'] ?? '', 'OK') !== false,
            'provider' => $this->provider,
            'latency_ms' => $latency,
            'response' => $result['text'] ?? null,
            'error' => $result['error'] ?? null
        ];
    }

    /**
     * Estimate tokens for text (rough approximation)
     */
    public function estimateTokens(string $text): int
    {
        // Rough approximation: ~4 characters per token for English
        return (int)ceil(strlen($text) / 4);
    }

    /**
     * Check if response would exceed token limit
     */
    public function willExceedTokenLimit(string $prompt, int $maxResponseTokens = 2000, int $limit = 8000): bool
    {
        $promptTokens = $this->estimateTokens($prompt);
        return ($promptTokens + $maxResponseTokens) > $limit;
    }
}
