<?php

return [
    'enabled' => getenv('QUERY_CACHE_ENABLED') !== false ? (bool)getenv('QUERY_CACHE_ENABLED') : true,
    'store' => getenv('QUERY_CACHE_STORE') ?: 'redis',
    'default_ttl' => getenv('QUERY_CACHE_TTL') !== false ? (int)getenv('QUERY_CACHE_TTL') : 3600, // 1 hour
    'prefix' => 'query_cache:',
    'use_tags' => getenv('QUERY_CACHE_TAGS') !== false ? (bool)getenv('QUERY_CACHE_TAGS') : true,
    
    'ttl_strategy' => [
        'select' => getenv('QUERY_CACHE_TTL_SELECT') !== false ? (int)getenv('QUERY_CACHE_TTL_SELECT') : 7200, // 2 hours
        'aggregate' => getenv('QUERY_CACHE_TTL_AGGREGATE') !== false ? (int)getenv('QUERY_CACHE_TTL_AGGREGATE') : 1800, // 30 min
        'join' => getenv('QUERY_CACHE_TTL_JOIN') !== false ? (int)getenv('QUERY_CACHE_TTL_JOIN') : 3600, // 1 hour
        'analytics' => [
            'event' => getenv('QUERY_CACHE_TTL_ANALYTICS_EVENT') !== false ? (int)getenv('QUERY_CACHE_TTL_ANALYTICS_EVENT') : 900, // 15 min
            'aggregation' => getenv('QUERY_CACHE_TTL_ANALYTICS_AGG') !== false ? (int)getenv('QUERY_CACHE_TTL_ANALYTICS_AGG') : 300, // 5 min
            'realtime' => getenv('QUERY_CACHE_TTL_ANALYTICS_RT') !== false ? (int)getenv('QUERY_CACHE_TTL_ANALYTICS_RT') : 60, // 1 min
        ],
    ],
    
    'exclude' => [
        'tables' => [
            'sessions',
            'jobs',
            'failed_jobs',
            'analytics_failures' // Don't cache error data
        ],
        'queries' => [
            'insert',
            'update',
            'delete',
            'truncate'
        ]
    ],
    
    'thresholds' => [
        'min_execution_time' => 0.1, // seconds
        'min_result_size' => 100 // rows
    ],
    
    'warmup' => [
        'enabled' => getenv('QUERY_CACHE_WARMUP') !== false ? (bool)getenv('QUERY_CACHE_WARMUP') : false,
        'strategies' => [
            'frequent_queries' => [
                'sample_period' => '24 hours',
                'threshold' => 5
            ],
            'slow_queries' => [
                'threshold_ms' => 500
            ]
        ]
    ],
    
    'monitoring' => [
        'enabled' => true,
        'sample_rate' => 0.1, // 10% of queries
        'metrics' => [
            'hit_rate',
            'miss_rate',
            'memory_usage',
            'evictions',
            'analytics' => [
                'event_queries',
                'aggregation_queries',
                'realtime_queries'
            ]
        ]
    ]
];
