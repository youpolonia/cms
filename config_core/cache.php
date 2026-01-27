<?php

return [
    'default' => $_ENV['CACHE_DRIVER'] ?? 'file',
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => __DIR__.'/../storage/cache',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => $_ENV['CACHE_PREFIX'] ?? 'cms_cache',
        ],
    ],
    'prefix' => $_ENV['CACHE_PREFIX'] ?? 'cms_cache',
];
