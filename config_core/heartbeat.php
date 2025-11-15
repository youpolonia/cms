<?php
/**
 * Heartbeat alert configuration
 */

return [
    // Default failure threshold in seconds
    'default_threshold' => 300, // 5 minutes

    // Alert level configurations
    'alert_levels' => [
        1 => [
            'threshold_minutes' => 5,
            'notification_channels' => ['dashboard'],
            'escalation_after_minutes' => 15
        ],
        2 => [
            'threshold_minutes' => 15,
            'notification_channels' => ['dashboard', 'email'],
            'escalation_after_minutes' => 30
        ],
        3 => [
            'threshold_minutes' => 30,
            'notification_channels' => ['dashboard', 'email', 'sms'],
            'escalation_after_minutes' => 60
        ],
        4 => [
            'threshold_minutes' => 60,
            'notification_channels' => ['dashboard', 'email', 'sms'],
            'escalation_after_minutes' => 120
        ],
        5 => [
            'threshold_minutes' => 120,
            'notification_channels' => ['dashboard', 'email', 'sms'],
            'escalation_after_minutes' => null // Final level
        ]
    ],

    // Maximum alert level (must match highest level in alert_levels)
    'max_alert_level' => 5,

    // Maintenance mode settings
    'maintenance' => [
        'suppress_all' => false,
        'suppress_until' => null
    ]
];
