<?php

return [
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
        'users' => 'Active AI users'
    ]
];
