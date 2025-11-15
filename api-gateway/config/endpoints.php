<?php
declare(strict_types=1);

/**
 * API Endpoint Configuration
 * Defines available endpoints, their handlers, middleware, and rate limits
 */

return [
    'v1' => [
        'auth' => [
            'login' => [
                'method' => 'POST',
                'path' => '/auth/login',
                'handler' => 'AuthController@login',
                'middleware' => ['RequestLogger'],
                'rate_limit' => [
                    'max_requests' => 5,
                    'interval' => 60 // seconds
                ]
            ],
            'logout' => [
                'method' => 'POST',
                'path' => '/auth/logout',
                'handler' => 'AuthController@logout',
                'middleware' => ['RequestLogger', 'AuthCheck'],
                'rate_limit' => [
                    'max_requests' => 10,
                    'interval' => 60
                ]
            ]
        ],
        'content' => [
            'list' => [
                'method' => 'GET',
                'path' => '/content',
                'handler' => 'ContentController@index',
                'middleware' => ['RequestLogger', 'AuthCheck'],
                'rate_limit' => [
                    'max_requests' => 30,
                    'interval' => 60
                ]
            ],
            'create' => [
                'method' => 'POST',
                'path' => '/content',
                'handler' => 'ContentController@store',
                'middleware' => ['RequestLogger', 'AuthCheck', 'ContentValidation'],
                'rate_limit' => [
                    'max_requests' => 10,
                    'interval' => 60
                ]
            ]
        ]
    ],
    'v2' => [
        'content' => [
            'list' => [
                'method' => 'GET',
                'path' => '/v2/content',
                'handler' => 'ContentV2Controller@index',
                'middleware' => ['RequestLogger', 'AuthCheck'],
                'rate_limit' => [
                    'max_requests' => 60,
                    'interval' => 60
                ]
            ]
        ]
    ]
];
