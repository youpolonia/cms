<?php
// Scheduled tasks configuration
return [
    'token_cleanup' => [
        'class' => 'Includes\Auth\TokenBlacklist',
        'method' => 'scheduleCleanup',
        'interval' => 3600 // Run hourly
    ]
];
