<?php
declare(strict_types=1);

return [
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => 'PROD_REDIS_PASSWORD', // Replace with actual password
        'database' => 0,
        'timeout' => 2.5,
        'read_timeout' => 2.5,
        'persistent' => true,
        'prefix' => 'cms_prod:',
        'options' => [
            Redis::OPT_SERIALIZER => Redis::SERIALIZER_PHP,
            Redis::OPT_PREFIX => 'cms_prod:',
            Redis::OPT_SCAN => Redis::SCAN_RETRY,
            Redis::OPT_READ_TIMEOUT => 2.5
        ]
    ],
    'default_ttl' => 3600 // 1 hour
];
