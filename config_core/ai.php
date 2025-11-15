<?php

return [
    'default_provider' => 'openai',
    
    'providers' => [
        'openai' => [
            'driver' => 'openai',
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
                        'cost_per_output_token' => 0.00003,
                        'templates' => ['blog_post', 'product_description']
                    ],
                    'gpt-3.5-turbo' => [
                        'max_tokens' => 16385,
                        'cost_per_input_token' => 0.0000015,
                        'cost_per_output_token' => 0.000002,
                        'templates' => ['social_media', 'email']
                    ]
                ],
                'tenant_overrides' => [] // Will be populated dynamically
            ],

            'rate_limits' => [
                'per_minute' => 60,
                'per_hour' => 1000,
                'per_day' => 10000,
                'tenant_overrides' => [] // Will be populated dynamically
            ],

            'quality_thresholds' => [
                'plagiarism' => 0.85,
                'tone_consistency' => 0.7,
                'tenant_overrides' => [] // Will be populated dynamically
            ],

            'monitoring' => [
                'enabled' => true,
                'storage_days' => 30,
                'alert_thresholds' => [
                    'error_rate' => 0.1,
                    'response_time' => 5000,
                    'cost_per_request' => 0.1
                ]
            ],
            
            'fine_tuning' => [
                'enabled' => true,
                'base_models' => ['gpt-3.5-turbo'],
                'max_training_examples' => 1000
            ]
        ],
        
        'huggingface' => [
            'driver' => 'huggingface',
            'key' => getenv('HUGGINGFACE_API_KEY') ?: null,
            'timeout' => 30,
            'retry_times' => 3,
            'retry_sleep' => 100,
            
            'models' => [
                'default' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
                'available' => [
                    'mistralai/Mixtral-8x7B-Instruct-v0.1' => [
                        'max_tokens' => 32768,
                        'cost_per_input_token' => 0.000007,
                        'cost_per_output_token' => 0.000014,
                        'templates' => ['technical_documentation']
                    ],
                    'meta-llama/Llama-2-70b-chat-hf' => [
                        'max_tokens' => 4096,
                        'cost_per_input_token' => 0.000009,
                        'cost_per_output_token' => 0.000018,
                        'templates' => ['creative_writing']
                    ]
                ],
                'tenant_overrides' => [] // Will be populated dynamically
            ],

            'rate_limits' => [
                'per_minute' => 30,
                'per_hour' => 500,
                'per_day' => 5000,
                'tenant_overrides' => [] // Will be populated dynamically
            ],

            'quality_thresholds' => [
                'plagiarism' => 0.8,
                'tone_consistency' => 0.65,
                'tenant_overrides' => [] // Will be populated dynamically
            ],
            
            'fine_tuning' => [
                'enabled' => true,
                'base_models' => ['mistralai/Mixtral-8x7B-Instruct-v0.1'],
                'max_training_examples' => 500
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
    ],
    
    'templates' => [
        'system_templates' => [
            'blog_post' => [
                'content' => "Write a blog post about {{{topic}}} with the following key points:\n{{{points}}}",
                'requires_ai' => true,
                'ai_parameters' => [
                    'temperature' => 0.7,
                    'max_tokens' => 1000
                ]
            ],
            'product_description' => [
                'content' => "Create a product description for {{{product_name}}} with these features:\n{{{features}}}",
                'requires_ai' => true
            ],
            'social_media' => [
                'content' => "Create a social media post about {{{topic}}} with tone: {{{tone}}}",
                'requires_ai' => true,
                'ai_parameters' => [
                    'temperature' => 0.9,
                    'max_tokens' => 280
                ]
            ],
            'email' => [
                'content' => "Write a {{{type}}} email with subject: {{{subject}}} and body about: {{{content}}}",
                'requires_ai' => true
            ]
        ],
        'tenant_overrides' => [
            // Example:
            // 1 => [
            //     'blog_post' => [
            //         'content' => "Custom blog template for tenant 1 about {{{topic}}}",
            //         'requires_ai' => true
            //     ]
            // ]
        ]
    ],

    'usage_tracking' => [
        'enabled' => true,
        'monthly_limits' => [
            'default' => 1000000, // 1M tokens
            'tiers' => [
                1 => 5000000,    // 5M tokens
                2 => 10000000    // 10M tokens
            ]
        ],
        'tenant_overrides' => [
            // Example:
            // 1 => [
            //     'monthly_limit' => 20000000 // 20M tokens
            // ]
        ]
    ]
];
