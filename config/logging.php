<?php

use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    'channels' => [
        'queries' => [
            'driver' => 'single',
            'path' => storage_path('logs/queries.log'),
            'level' => 'debug',
            'permission' => 0664,
            'tap' => [\App\Logging\QueryLogFormatter::class],
        ],
    ],
];
