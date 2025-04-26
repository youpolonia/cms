<?php

return [
    'thresholds' => [
        50 => 'warning',
        100 => 'alert'
    ],

    'monthly_limit' => env('AI_MONTHLY_LIMIT', 100),
    
    'tracked_routes' => [
        'ai.*',
        'content.generate',
        'content.improve',
        'content.summarize',
        'ai.openai.*'
    ],

    'openai' => [
        'default_model' => 'text-davinci-003',
        'rate_limit' => [
            'per_minute' => 60,
            'per_hour' => 1000
        ],
        'cost_per_token' => [
            'text-davinci-003' => 0.00002,
            'gpt-4' => 0.00006
        ],
        'max_tokens' => 2000,
        'default_temperature' => 0.7
    ]
];