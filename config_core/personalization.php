<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Personalization Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for content personalization system including caching,
    | default rules, and integration settings.
    |
    */

    'cache' => [
        'ttl' => getenv('PERSONALIZATION_CACHE_TTL') !== false ? (int)getenv('PERSONALIZATION_CACHE_TTL') : 3600, // 1 hour
        'prefix' => 'personalization',
    ],

    'rules' => [
        'default_priority' => 100,
        'max_conditions' => 10,
        'types' => [
            'user_segment' => [
                'class' => \App\Rules\UserSegmentRule::class,
                'max_conditions' => 5
            ],
            'behavior' => [
                'class' => \App\Rules\BehaviorRule::class,
                'max_conditions' => 3
            ],
            'ab_test' => [
                'class' => \App\Rules\ABTestRule::class,
                'max_conditions' => 2
            ]
        ]
    ],

    'tracking' => [
        'enabled' => getenv('PERSONALIZATION_TRACKING_ENABLED') !== false ? (bool)getenv('PERSONALIZATION_TRACKING_ENABLED') : true,
        'storage_driver' => getenv('PERSONALIZATION_TRACKING_DRIVER') ?: 'database',
        'events' => [
            'page_view',
            'content_interaction',
            'conversion',
            'search_query'
        ]
    ],

    'segmentation' => [
        'default_groups' => [
            'new_users',
            'returning_users',
            'high_value_users'
        ],
        'custom_group_limit' => getenv('PERSONALIZATION_SEGMENT_LIMIT') !== false ? (int)getenv('PERSONALIZATION_SEGMENT_LIMIT') : 20,
        'refresh_interval' => 'daily'
    ],

    'ab_testing' => [
        'enabled' => getenv('PERSONALIZATION_AB_TESTING_ENABLED') !== false ? (bool)getenv('PERSONALIZATION_AB_TESTING_ENABLED') : true,
        'default_variations' => 2,
        'min_participants' => 100,
        'confidence_threshold' => 0.95,
        'tracking_event' => 'ab_test_participation'
    ],

    'integration' => [
        'content_versions' => true,
        'caching_layer' => 'default',
        'analytics' => [
            'driver' => getenv('PERSONALIZATION_ANALYTICS_DRIVER') ?: 'mixpanel',
            'connection' => getenv('PERSONALIZATION_ANALYTICS_CONNECTION') ?: 'default'
        ],
        'mcp' => [
            'enabled' => getenv('MCP_PERSONALIZATION_ENABLED') !== false ? (bool)getenv('MCP_PERSONALIZATION_ENABLED') : false,
            'host' => getenv('MCP_PERSONALIZATION_HOST') ?: 'localhost',
            'port' => getenv('MCP_PERSONALIZATION_PORT') !== false ? (int)getenv('MCP_PERSONALIZATION_PORT') : 80,
            'timeout' => getenv('MCP_PERSONALIZATION_TIMEOUT') !== false ? (int)getenv('MCP_PERSONALIZATION_TIMEOUT') : 30,
            'models' => [
                'user_behavior' => [
                    'version' => 'v1.2',
                    'training_interval' => 'weekly'
                ],
                'content_performance' => [
                    'version' => 'v1.0',
                    'training_interval' => 'daily'
                ],
                'recommendations' => [
                    'version' => 'v1.0',
                    'training_interval' => 'daily'
                ]
            ]
        ]
    ],

    'predictive' => [
        'default_model' => 'user_behavior',
        'min_confidence' => 0.7,
        'cache_predictions' => true,
        'cache_ttl' => 3600,
        'fallback_strategy' => 'random'
    ],

    'dynamic_content' => [
        'max_variations' => 5,
        'default_priority' => 50,
        'refresh_interval' => 'hourly'
    ],

    'recommendations' => [
        'enabled' => getenv('RECOMMENDATIONS_ENABLED') !== false ? (bool)getenv('RECOMMENDATIONS_ENABLED') : true,
        'default_strategy' => getenv('RECOMMENDATIONS_DEFAULT_STRATEGY') ?: 'hybrid',
        'strategies' => [
            'collaborative' => [
                'min_interactions' => 5,
                'similarity_threshold' => 0.7,
                'cache_ttl' => 3600
            ],
            'content' => [
                'min_similarity' => 0.6,
                'max_categories' => 3,
                'cache_ttl' => 3600
            ],
            'hybrid' => [
                'content_weight' => 0.4,
                'collaborative_weight' => 0.6,
                'cache_ttl' => 3600
            ],
            'realtime' => [
                'session_threshold' => 3,
                'decay_rate' => 0.8,
                'cache_ttl' => 900
            ]
        ],
        'fallback' => [
            'strategy' => 'popular',
            'limit' => 10
        ],
        'admin' => [
            'max_strategies' => 5,
            'strategy_switch_interval' => 'daily'
        ]
    ]
];
