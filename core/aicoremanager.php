<?php
/**
 * Base class for AI model integration
 * Follows framework-free PHP requirements
 */
class AICoreManager {
    protected static $models = [];
    protected static $endpoints = [];

    /**
     * Register a new AI model
     * @param string $modelId - Unique model identifier
     * @param array $config - Configuration array
     */
    public static function registerModel(string $modelId, array $config): void {
        self::$models[$modelId] = $config;
        if (isset($config['api_endpoint'])) {
            self::$endpoints[$modelId] = $config['api_endpoint'];
        }
    }

    /**
     * Get model configuration
     */
    public static function getModelConfig(string $modelId): ?array {
        return self::$models[$modelId] ?? null;
    }

    /**
     * Make API request to model endpoint
     */
    public static function makeRequest(
        string $modelId, 
        array $payload,
        array $headers = []
    ): array {
        if (!isset(self::$endpoints[$modelId])) {
            throw new Exception("Model $modelId not registered");
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::$endpoints[$modelId],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json'
            ], $headers)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
}
