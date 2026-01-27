<?php
declare(strict_types=1);

return [
    'rules' => [
        'default_score' => 50,
        'thresholds' => [
            'high_priority' => 80,
            'medium_priority' => 50,
            'low_priority' => 20
        ],
        'evaluation_order' => [
            'user_segment',
            'content_type',
            'engagement_history'
        ]
    ],
    'content_targeting' => [
        'fallback_content_id' => 'default',
        'scoring_weights' => [
            'recency' => 0.3,
            'relevance' => 0.5,
            'engagement' => 0.2
        ],
        'max_variants' => 3
    ],
    'audit_logging' => [
        'enabled' => true,
        'log_level' => 'changes_only'
    ]
];
