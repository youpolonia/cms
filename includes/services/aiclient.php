<?php

namespace Includes\Services;

use Includes\Config\ConfigLoader;

/**
 * Handles communication with AI APIs for notification processing
 */
class AIClient {
    private static $providers = [
        'openai' => [
            'analysis_endpoint' => 'https://api.openai.com/v1/chat/completions',
            'suggestions_endpoint' => 'https://api.openai.com/v1/completions',
            'headers' => [
                'Authorization: Bearer {api_key}',
                'Content-Type: application/json'
            ]
        ],
        'huggingface' => [
            'analysis_endpoint' => 'https://api-inference.huggingface.co/models/',
            'suggestions_endpoint' => 'https://api-inference.huggingface.co/pipeline/',
            'headers' => [
                'Authorization: Bearer {api_key}'
            ]
        ]
    ];

    /**
     * Send request to AI service
     */
    public static function sendRequest(string $endpointType, array $payload): array {
        $config = ConfigLoader::get('ai');
        $provider = $config['provider'] ?? 'openai';
        
        if (!isset(self::$providers[$provider])) {
            throw new \RuntimeException("Unsupported AI provider: $provider");
        }

        $endpoint = self::$providers[$provider][$endpointType . '_endpoint'];
        $headers = self::prepareHeaders($provider, $config['api_key']);

        return self::executeApiCall($endpoint, $headers, $payload);
    }

    /**
     * Prepare request headers for provider
     */
    private static function prepareHeaders(string $provider, string $apiKey): array {
        $headers = self::$providers[$provider]['headers'];
        return array_map(function($header) use ($apiKey) {
            return str_replace('{api_key}', $apiKey, $header);
        }, $headers);
    }

    /**
     * Execute API call with retry logic
     */
    private static function executeApiCall(string $endpoint, array $headers, array $payload): array {
        $maxRetries = 3;
        $retryDelay = 1; // seconds
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => implode("\r\n", $headers),
                        'content' => json_encode($payload),
                        'timeout' => 30
                    ]
                ]);

                $response = file_get_contents($endpoint, false, $context);
                return json_decode($response, true);
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                sleep($retryDelay * $attempt);
            }
        }

        throw new \RuntimeException("Failed to complete API request after $maxRetries attempts");
    }

    /**
     * Get available providers
     */
    public static function getAvailableProviders(): array {
        return array_keys(self::$providers);
    }
}
