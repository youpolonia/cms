<?php

return [
    // Brute-force protection settings
    'brute_force' => [
        'max_attempts' => 5,       // Maximum login attempts
        'time_window' => 600,      // 10 minutes in seconds
        'ban_duration' => 1800     // 30 minutes ban after max attempts
    ],
    
    // Session timeout settings
    'session_timeout' => 900,      // 15 minutes in seconds
    
    // IP binding settings
    'ip_binding' => [
        'enabled' => false,        // Toggle for IP binding
        'strict_mode' => false     // If true, rejects session on any IP change
    ]
];
