<?php
/**
 * Centralized logger configuration
 * 
 * Configures default logger behavior and fallback mechanisms
 */

return [
    // Default logger type ('file' or 'database')
    'default_type' => 'file',
    
    // File logger configuration
    'file' => [
        'path' => __DIR__ . '/../logs/app.log',
        'max_size' => '10MB',
        'rotate' => true
    ],
    
    // Database logger configuration
    'database' => [
        'table' => 'system_logs',
        'connection' => 'default'
    ],
    
    // Fallback configuration
    'fallback' => [
        'enabled' => true,
        'emergency_path' => __DIR__ . '/../logs/emergency.log',
        'max_retries' => 3,
        'retry_delay_ms' => 100,
        'use_stderr' => true
    ],
    
    // Environment-specific overrides
    'environments' => [
        'production' => [
            'default_type' => 'database'
        ],
        'development' => [
            'default_type' => 'file'
        ]
    ]
];
