<?php

return [
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION')
    ],

    'copyleaks' => [
        'key' => env('COPYLEAKS_API_KEY'),
        'base_url' => 'https://api.copyleaks.com/v3'
    ],

    'tone_analyzer' => [
        'key' => env('TONE_ANALYZER_API_KEY'),
        'base_url' => 'https://api.toneanalyzer.com/v1'
    ],

    'ai_rate_limits' => [
        'per_minute' => env('AI_RATE_LIMIT_PER_MINUTE', 60),
        'per_hour' => env('AI_RATE_LIMIT_PER_HOUR', 1000),
        'per_day' => env('AI_RATE_LIMIT_PER_DAY', 10000)
    ],

    'n8n' => [
        'webhook_secret' => env('N8N_WEBHOOK_SECRET')
    ]
];
