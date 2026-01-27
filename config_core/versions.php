<?php

return [
    'retention' => [
        'default_count' => 5,
        'default_days' => 30,
        'max_count' => 20,
        'max_days' => 365
    ],
    'cleanup_schedule' => [
        'enabled' => true,
        'frequency' => 'daily',
        'time' => '02:00'
    ]
];
