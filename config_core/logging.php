<?php

return [
    'default' => 'app',
    'channels' => [
        'app' => [
            'driver' => 'custom',
            'path' => __DIR__ . '/../storage/logs/app.log',
            'level' => 'debug',
        ],
        'queries' => [
            'driver' => 'custom',
            'path' => __DIR__ . '/../storage/logs/queries.log',
            'level' => 'debug',
        ],
        'error' => [
            'driver' => 'custom',
            'path' => __DIR__ . '/../storage/logs/error.log',
            'level' => 'error',
        ],
        'security' => [
            'driver' => 'custom',
            'path' => __DIR__ . '/../storage/logs/security.log',
            'level' => 'warning',
        ],
        'security_audit' => [
            'driver' => 'custom',
            'path' => __DIR__ . '/../storage/logs/security_audit.log',
            'level' => 'info',
        ],
    ],
];
