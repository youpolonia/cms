<?php
/**
 * AI Providers Configuration
 * Framework-free PHP CMS
 */
return [
    'openai' => [
        'enabled' => false,
        'api_key' => '',
        'model' => 'gpt-4',
        'endpoint' => 'https://api.openai.com/v1/chat/completions',
        'rate_limit' => 60 // Requests per minute
    ],
    'gemini' => [
        'enabled' => false,
        'api_key' => '',
        'model' => 'gemini-pro',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
        'rate_limit' => 30 // Requests per minute
    ],
    'local' => [
        'enabled' => true,
        'model_path' => __DIR__ . '/../models/local-llm',
        'context_window' => 4096
    ]
];
