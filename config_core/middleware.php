<?php
declare(strict_types=1);

return [
    // Execution order matters - first in array runs first
    'before' => [
        'Includes\Middleware\TenantMiddleware',
        'Includes\Middleware\SecurityHeadersMiddleware',
        'Includes\Middleware\MaintenanceModeMiddleware',
    ],
    'after' => [
        'Includes\Middleware\ResponseLoggerMiddleware',
        'Includes\Middleware\ErrorHandlerMiddleware',
    ]
];
