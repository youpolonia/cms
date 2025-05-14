<?php

return [
    'default_provider' => 'openai',
    
    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
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
                    'response_time' => 5000,
                    'cost_per_request' => 0.1
                ]
            ]
        ],
        
        'huggingface' => [
            'driver' => 'huggingface',
            'key' => env('HUGGINGFACE_API_KEY'),
            'timeout' => 30,
            'retry_times' => 3,
            'retry_sleep' => 100,
            
            'models' => [
                'default' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
                'available' => [
                    'mistralai/Mixtral-8x7B-Instruct-v0.1' => [
                        'max_tokens' => 32768,
                        'cost_per_input_token' => 0.000007,
                        'cost_per_output_token' => 0.000014
                    ],
                    'meta-llama/Llama-2-70b-chat-hf' => [
                        'max_tokens' => 4096,
                        'cost_per_input_token' => 0.000009,
                        'cost_per_output_token' => 0.000018
                    ]
                ]
            ],

            'rate_limits' => [
                'per_minute' => 30,
                'per_hour' => 500,
                'per_day' => 5000
            ],

            'quality_thresholds' => [
                'plagiarism' => 0.8,
                'tone_consistency' => 0.65
            ]
        ]
    ],
    
    // Global settings
    'quality_thresholds' => [
        'plagiarism' => 0.8,
        'tone_consistency' => 0.7
    ],
    
    'monitoring' => [
        'enabled' => true,
        'storage_days' => 30
    ]
];