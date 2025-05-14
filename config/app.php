<?php
/**
 * Application Configuration
 * 
 * Environment overrides can be set using:
 * CONFIG_APP_* environment variables (e.g. CONFIG_APP_ENV=production)
 */
return [
    // Basic Application Settings
    'name' => env('APP_NAME', 'AI CMS'),
    'env' => env('APP_ENV', 'development'),
    'debug' => env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://localhost'),
    
    // Time & Locale
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    
    // Encryption
    'key' => env('APP_KEY', ''),
    'cipher' => 'AES-256-CBC',
    
    // Logging
    'log' => env('APP_LOG', 'single'),
    'log_level' => env('APP_LOG_LEVEL', 'debug'),
    
    // Features
    'features' => [
        'ai_integration' => env('APP_FEATURES_AI', true),
        'page_builder' => env('APP_FEATURES_PAGE_BUILDER', true),
        'multisite' => env('APP_FEATURES_MULTISITE', false),
    ],
    
    // API Settings
    'api' => [
        'rate_limit' => env('APP_API_RATE_LIMIT', 60),
        'throttle' => env('APP_API_THROTTLE', 10),
    ],
];