<?php

return [
    'recommendation_strategy' => [
        'variants' => [
            'A' => [
                'description' => 'Content-based recommendations only',
                'weight' => 0.33
            ],
            'B' => [
                'description' => 'Collaborative filtering only',
                'weight' => 0.33
            ],
            'C' => [
                'description' => 'Hybrid approach (default)',
                'weight' => 0.34
            ]
        ],
        'metrics' => [
            'click_through_rate',
            'time_spent',
            'conversion_rate'
        ],
        'duration_days' => 30,
        'is_active' => true
    ]
];
