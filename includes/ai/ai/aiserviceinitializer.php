<?php
class AIServiceInitializer {
    public static function initialize(): void {
        // Load default configuration
        $config = self::loadConfig('config/ai.php');
        
        // Initialize primary provider
        AIClient::configure(
            $config['providers'][$config['default_provider']]['api_key'],
            $config['providers'][$config['default_provider']]['endpoint'],
            $config['default_provider']
        );
    }

    private static function loadConfig(string $path): array {
        if (!file_exists($path)) {
            throw new RuntimeException("AI config file not found: $path");
        }

        $config = require_once $path;
        
        // Apply environment overrides
        foreach ($config['providers'] as $provider => &$settings) {
            $env_key = strtoupper("AI_{$provider}_API_KEY");
            if ($api_key = getenv($env_key)) {
                $settings['api_key'] = $api_key;
            }
        }

        return $config;
    }

    public static function getConfiguredProviders(): array {
        $config = self::loadConfig('config/ai.php');
        return array_keys($config['providers']);
    }
}
