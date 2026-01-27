<?php

return [
    // Default time-to-live in days
    'default_ttl' => 30,
    
    // Content type specific TTL overrides
    'content_types' => [
        'news' => 7,
        'blog' => 90,
        'page' => 365
    ],
    
    // Archive storage location
    'archive_path' => '/var/www/html/cms/storage/archive',
    
    // Retention period for archived content (days)
    'retention_period' => 365,
    
    // Logging configuration
    'logging' => [
        'enabled' => true,
        'path' => '/var/www/html/cms/storage/logs/archival.log'
    ]
];
