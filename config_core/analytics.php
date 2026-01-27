<?php

return [
    'real_time' => [
        'enabled' => getenv('ANALYTICS_REAL_TIME') !== false ? (bool)getenv('ANALYTICS_REAL_TIME') : true,
        'sample_rates' => [
            'page_views' => 1.0,
            'api_calls' => 1.0,
            'content_ops' => 1.0,
            'ai_usage' => 1.0
        ],
        'storage' => [
            'primary' => getenv('ANALYTICS_STORAGE') ?: 'redis',
            'fallback' => 'database',
            'ttl' => 604800 // 7 days in seconds
        ],
        'processing' => [
            'batch_size' => 1000,
            'workers' => 4,
            'queue' => 'analytics'
        ],
        'mcp_integration' => [
            'enabled' => true,
            'services' => [
                'personalization' => [
                    'endpoint' => getenv('MCP_PERSONALIZATION_ENDPOINT') ?: null,
                    'timeout' => 5
                ],
                'search' => [
                    'endpoint' => getenv('MCP_SEARCH_ENDPOINT') ?: null,
                    'timeout' => 5
                ]
            ]
        ]
    ],

    'exports' => [
        'default_expiration_days' => 30,
        'warning_days_before' => 1,
        'extend_days' => 7,
        'max_extensions' => 3,
    ],
    'anonymization' => [
        'default_enabled' => false,
        'default_options' => [
            'remove_pii' => true,
            'hash_identifiers' => true,
            'remove_ip' => true,
            'remove_user_agent' => true
        ]
    ],
    'widgets' => [
        'default_refresh_interval' => 60, // seconds
        'types' => [
            'usage_stats' => [
                'metrics' => ['views', 'sessions', 'duration'],
                'chart_type' => 'line'
            ],
            'content_views' => [
                'metrics' => ['content_type', 'views', 'unique_views'],
                'chart_type' => 'bar'
            ],
            'ai_usage' => [
                'metrics' => ['requests', 'tokens', 'users'],
                'chart_type' => 'pie',
                'threshold' => 1000
            ],
            'content_operations' => [
                'metrics' => ['generated_words', 'comparisons', 'restorations'],
                'chart_type' => 'bar',
                'stacked' => true
            ],
            'engagement' => [
                'metrics' => ['engagement_score', 'comparison_time', 'generation_time'],
                'chart_type' => 'radar'
            ]
        ]
    ],
    'metrics' => [
        'views' => 'Total page views',
        'sessions' => 'User sessions',
        'duration' => 'Average session duration (minutes)',
        'content_type' => 'Content by type',
        'unique_views' => 'Unique visitors',
        'requests' => 'AI API requests',
        'tokens' => 'AI tokens consumed',
        'users' => 'Active AI users',
        'generated_words' => 'AI generated words',
        'generation_time' => 'Content generation time (seconds)',
        'comparisons' => 'Version comparisons',
        'comparison_time' => 'Version comparison duration (seconds)',
        'restorations' => 'Content restorations',
        'engagement_score' => 'User engagement score'
    ],
    'content_operations' => [
        'generation' => [
            'track_words' => true,
            'track_time' => true,
            'track_templates' => true
        ],
        'comparison' => [
            'track_views' => true,
            'track_duration' => true,
            'track_changes' => true
        ],
        'restoration' => [
            'track_count' => true,
            'track_reasons' => true
        ]
    ],
    'retention' => [
        'raw_events' => 30, // days
        'aggregates' => 365, // days
        'anonymized' => 730 // days
    ],
    'cron' => [
        'daily_processing' => [
            'time' => '03:00',
            'timezone' => 'UTC',
            'enabled' => true,
            'retry_attempts' => 3,
            'retry_delay' => 300, // 5 minutes
            'notification_email' => getenv('ANALYTICS_CRON_NOTIFICATION_EMAIL') ?: null,
            'webhook_url' => getenv('ANALYTICS_CRON_WEBHOOK_URL') ?: null
        ]
    ]
];
