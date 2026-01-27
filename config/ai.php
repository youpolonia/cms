<?php
/**
 * AI Configuration Loader
 * Loads settings from ai_settings.json for backward compatibility
 */

$settingsFile = __DIR__ . '/ai_settings.json';

if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
} else {
    $settings = [];
}

// Return in old format for compatibility
return [
    'default_provider' => $settings['default_provider'] ?? 'openai',
    'providers' => [
        'openai' => [
            'api_key' => $settings['providers']['openai']['api_key'] ?? '',
            'enabled' => $settings['providers']['openai']['enabled'] ?? false,
            'model' => $settings['providers']['openai']['default_model'] ?? 'gpt-4o-mini',
        ],
        'anthropic' => [
            'api_key' => $settings['providers']['anthropic']['api_key'] ?? '',
            'enabled' => $settings['providers']['anthropic']['enabled'] ?? false,
            'model' => $settings['providers']['anthropic']['default_model'] ?? 'claude-3-5-sonnet-20241022',
        ],
        'google' => [
            'api_key' => $settings['providers']['google']['api_key'] ?? '',
            'enabled' => $settings['providers']['google']['enabled'] ?? false,
            'model' => $settings['providers']['google']['default_model'] ?? 'gemini-1.5-flash',
        ],
        'huggingface' => [
            'api_key' => $settings['providers']['huggingface']['api_key'] ?? '',
            'enabled' => $settings['providers']['huggingface']['enabled'] ?? false,
            'model' => $settings['providers']['huggingface']['default_model'] ?? 'mistralai/Mistral-7B-Instruct-v0.2',
        ],
        'ollama' => [
            'base_url' => $settings['providers']['ollama']['base_url'] ?? 'http://localhost:11434',
            'enabled' => $settings['providers']['ollama']['enabled'] ?? false,
            'model' => $settings['providers']['ollama']['default_model'] ?? 'llama2',
        ],
    ],
    'temperature' => $settings['generation_defaults']['temperature'] ?? 0.7,
    'max_tokens' => $settings['generation_defaults']['max_tokens'] ?? 2000,
];
