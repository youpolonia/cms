<?php

return [
    'path' => __DIR__ . '/../plugins',
    'sandbox' => [
        'enabled' => true,
        'time_limit' => 2, // seconds
        'memory_limit' => '64M',
        'functions_blacklist' => [
            'exec', 'shell_exec', 'system', 'passthru', 'popen',
            'proc_open', 'pcntl_exec', 'eval', 'create_function'
        ],
        'classes_whitelist' => [
            'Includes\\*',
            'Config\\*',
            'Models\\*'
        ]
    ],
    'dependencies' => [
        'required_fields' => ['name', 'version', 'dependencies'],
        'repository_url' => 'https://plugins.example.com/api/v1'
    ],
    'ui' => [
        'extension_points' => [
            'admin.dashboard.top',
            'admin.dashboard.bottom',
            'content.edit.sidebar',
            'content.edit.toolbar'
        ],
        'cache_ttl' => 3600 // 1 hour
    ],
    'tenants' => [
        'default' => [
            'enabled_plugins' => [],
            'settings' => []
        ]
    ]
];
