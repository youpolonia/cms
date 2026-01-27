<?php

return [
    'channels' => [
        'in_app' => [
            'driver' => 'database',
            'enabled' => true,
            'rate_limit' => null, // No rate limit
            'batch_size' => 100,
        ],
        'email' => [
            'driver' => 'mail',
            'enabled' => true,
            'rate_limit' => '100/hour',
            'batch_size' => 50,
            'default_from' => getenv('MAIL_FROM_ADDRESS') ?: 'notifications@example.com',
        ],
        'webhook' => [
            'driver' => 'http',
            'enabled' => true,
            'rate_limit' => '30/minute',
            'batch_size' => 10,
            'timeout' => 5, // seconds
        ],
        'sms' => [
            'driver' => 'twilio',
            'enabled' => false, // Disabled by default
            'rate_limit' => '1/second',
            'batch_size' => 1,
            'from' => getenv('TWILIO_FROM_NUMBER') ?: null,
        ],
    ],

    'default_preferences' => [
        'channels' => ['in_app', 'email'],
        'frequency' => 'immediate',
        'content_filters' => [],
    ],

    'batch_processing' => [
        'enabled' => true,
        'queue' => 'notifications',
        'retry_after' => 60, // seconds
        'tries' => 3,
    ],

    'delivery_tracking' => [
        'enabled' => true,
        'retention_days' => 30,
    ],
];
