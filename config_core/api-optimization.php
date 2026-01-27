<?php

return [
    'compression' => [
        'enabled' => getenv('API_COMPRESSION_ENABLED') !== false ? (bool)getenv('API_COMPRESSION_ENABLED') : true,
        'methods' => ['gzip', 'deflate', 'br'],
        'min_size' => 1024, // 1KB
        'content_types' => [
            'application/json',
            'text/html',
            'text/plain',
            'application/xml'
        ]
    ],

    'cache' => [
        'enabled' => getenv('API_CACHE_ENABLED') !== false ? (bool)getenv('API_CACHE_ENABLED') : true,
        'duration' => getenv('API_CACHE_DURATION') !== false ? (int)getenv('API_CACHE_DURATION') : 3600, // 1 hour
        'excluded_endpoints' => [
            'content.versions.confirm-restore',
            'content.branches.merge'
        ],
        'vary_headers' => ['Accept-Encoding', 'Authorization']
    ],

    'rate_limits' => [
        'default' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
            'burst_limit' => getenv('API_BURST_LIMIT') !== false ? (int)getenv('API_BURST_LIMIT') : 100
        ],
        'heavy_endpoints' => [
            'content.version-comparison.compare',
            'content.version-comparison.analytics',
            'content.versions.analytics',
            'max_attempts' => 20,
            'decay_minutes' => 1,
            'burst_limit' => getenv('API_HEAVY_BURST_LIMIT') !== false ? (int)getenv('API_HEAVY_BURST_LIMIT') : 30
        ],
        'adaptive' => [
            'enabled' => getenv('API_ADAPTIVE_RATE_LIMIT') !== false ? (bool)getenv('API_ADAPTIVE_RATE_LIMIT') : true,
            'metrics' => ['cpu', 'memory', 'response_time'],
            'thresholds' => [
                'cpu' => 70,
                'memory' => 80,
                'response_time' => 500 // ms
            ]
        ]
    ],

    'async_endpoints' => [
        'content.version-comparison.compare',
        'content.versions.prepare-restore',
        'content.branches.merge'
    ],

    'monitoring' => [
        'slow_threshold_ms' => 500,
        'error_threshold_pct' => 5,
        'adaptive_throttling' => [
            'enabled' => true,
            'sample_rate' => 0.1,
            'metrics' => [
                'response_sizes',
                'latency_distribution',
                'error_rates'
            ]
        ]
    ]
];
