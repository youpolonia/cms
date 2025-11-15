<?php

return [
    'key' => getenv('OPENAI_API_KEY') ?: null,
    'organization' => getenv('OPENAI_ORGANIZATION') ?: null,
    'timeout' => 30,
    'retry_times' => 3,
    'retry_sleep' => 100,
    
    'models' => [
        'default' => 'gpt-4-turbo',
        'available' => [
            'gpt-4-turbo' => [
                'max_tokens' => 128000,
                'cost_per_input_token' => 0.00001,
                'cost_per_output_token' => 0.00003
            ],
            'gpt-3.5-turbo' => [
                'max_tokens' => 16385,
                'cost_per_input_token' => 0.0000015,
                'cost_per_output_token' => 0.000002
            ]
        ]
    ],

    'rate_limits' => [
        'per_minute' => 60,
        'per_hour' => 1000,
        'per_day' => 10000
    ],

    'quality_thresholds' => [
        'plagiarism' => 0.85,
        'tone_consistency' => 0.7
    ],

    'monitoring' => [
        'enabled' => true,
        'storage_days' => 30,
        'alert_thresholds' => [
            'error_rate' => 0.1,
            'response_time' => 5000, // ms
            'cost_per_request' => 0.1 // USD
        ]
    ]
];
