<?php

return [
    'metrics' => [
        'query_time' => [
            'threshold' => 500, // milliseconds
            'warning_threshold' => 300,
            'track' => true
        ],
        'slow_queries' => [
            'threshold' => 500, // milliseconds (updated to match requirements)
            'warning_threshold' => 300,
            'track' => true
        ],
        'connection_usage' => [
            'threshold' => 0.9, // 90%
            'warning_threshold' => 0.7,
            'track' => true
        ],
        'cache_ratio' => [
            'threshold' => 0.8, // 80% hit ratio
            'warning_threshold' => 0.6,
            'track' => true
        ]
    ],
    'storage' => [
        'key_prefix' => 'db_monitoring:',
        'retention_period' => 604800, // 7 days in seconds
        'grafana' => [
            'enabled' => true,
            'endpoint' => getenv('GRAFANA_ENDPOINT') ?: null,
            'api_key' => getenv('GRAFANA_API_KEY') ?: null,
            'dashboard_uid' => 'db-performance'
        ]
    ],
    'collection_interval' => 300, // 5 minutes in seconds
    'alerting' => [
        'enabled' => true,
        'channels' => [
            'slack' => getenv('DB_ALERTS_SLACK_WEBHOOK') ?: null,
            'email' => getenv('DB_ALERTS_EMAIL') ?: null
        ]
    ]
];
