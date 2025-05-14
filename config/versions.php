<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Version Expiration Rules
    |--------------------------------------------------------------------------
    |
    | Defines rules for automatic version cleanup and expiration
    |
    */
    
    'expiration' => [
        'regular_versions' => [
            'keep_minimum' => 3,
            'expire_after_days' => 30,
        ],
        
        'autosaves' => [
            'expire_after_days' => 7,
            'keep_unlimited' => false,
        ],
        
        'published_versions' => [
            'never_expire' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Tracking
    |--------------------------------------------------------------------------
    */
    'lifecycle' => [
        'track_transitions' => true,
        'log_expirations' => true,
        'log_archivals' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Schedule
    |--------------------------------------------------------------------------
    */
    'cleanup_schedule' => [
        'enabled' => true,
        'time' => '02:00', // Daily at 2 AM
        'batch_size' => 100,
    ],
];