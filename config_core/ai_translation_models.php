<?php
/**
 * AI Translation Models Configuration
 * Defines available translation models and their capabilities
 */
return [
    'gemini_pro' => [
        'api_endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
        'api_key_env' => 'GEMINI_API_KEY',
        'languages' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'nl', 'ru', 'ja', 'zh'],
        'context_window' => 32768,
        'rate_limit' => 60,
        'supports_detection' => true
    ],
    'mistral_large' => [
        'api_endpoint' => 'https://api.mistral.ai/v1/chat/completions',
        'api_key_env' => 'MISTRAL_API_KEY',
        'languages' => ['en', 'es', 'fr', 'de', 'it', 'pt'],
        'context_window' => 8192,
        'rate_limit' => 30,
        'fast_mode' => true,
        'supports_detection' => false
    ],
    'fallback' => [
        'api_endpoint' => 'https://translation-api.example.com/v1/translate',
        'api_key_env' => 'FALLBACK_API_KEY',
        'languages' => ['en', 'es', 'fr'],
        'context_window' => 4096,
        'rate_limit' => 10,
        'supports_detection' => true
    ]
];
