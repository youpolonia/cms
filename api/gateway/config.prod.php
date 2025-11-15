<?php
declare(strict_types=1);

return [
    'security' => [
        'validation' => [
            'enabled' => true,
            'strict_content_type' => true
        ],
        'auth_middleware' => [
            'enabled' => true,
            'exclude' => [
                '/api/auth/login',
                '/api/auth/register',
                '/api/health'
            ]
        ],
        'jwt' => [
            'secret' => getenv('JWT_SECRET'),
            'algorithm' => 'HS256',
            'expires_in' => 3600 // 1 hour
        ]
    ],
    'routes' => [
        // Will be populated with route definitions
    ]
];
