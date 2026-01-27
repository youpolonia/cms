<?php

return [
    'monitoring' => [
        'enabled' => true,
        'metrics' => [
            'response_times' => [
                'sample_rate' => 1.0,
                'thresholds' => [
                    'warning' => 500, // ms
                    'critical' => 2000 // ms
                ]
            ],
            'database' => [
                'query_threshold' => 100, // queries
                'slow_query_threshold' => 1000 // ms
            ],
            'memory' => [
                'warning' => '80%',
                'critical' => '90%'
            ]
        ],
        'integrations' => [
            'sentry' => getenv('PERFORMANCE_SENTRY_DSN') ?: null,
            'grafana' => [
                'url' => getenv('GRAFANA_URL') ?: null,
                'api_key' => getenv('GRAFANA_API_KEY') ?: null
            ]
        ]
    ],

    'workflow_thresholds' => [
        'time_per_stage' => [
            'warning' => 3600, // 1 hour in seconds
            'critical' => 86400 // 24 hours in seconds
        ],
        'total_duration' => [
            'warning' => 86400, // 1 day in seconds
            'critical' => 259200 // 3 days in seconds
        ],
        'stage_transitions' => [
            'max_per_hour' => 20,
            'max_per_day' => 100
        ]
    ],

    'scaling' => [
        'auto_scale' => getenv('AUTO_SCALE_ENABLED') !== false ? (bool)getenv('AUTO_SCALE_ENABLED') : false,
        'rules' => [
            'cpu' => [
                'scale_up' => 70,
                'scale_down' => 30
            ],
            'memory' => [
                'scale_up' => 75,
                'scale_down' => 25
            ],
            'queue' => [
                'scale_up' => 100,
                'scale_down' => 10
            ]
        ]
    ]
];
