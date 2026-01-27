<?php
return [
    'rate_limits' => [
        'default' => [
            'api' => ['limit' => 100, 'window' => 60],
            'auth' => ['limit' => 30, 'window' => 60],
            'admin' => ['limit' => 200, 'window' => 60]
        ]
    ],
    'validation_rules' => [
        '/api/' => [
            'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'content_types' => ['application/json']
        ],
        '/admin/' => [
            'methods' => ['GET', 'POST'],
            'content_types' => ['application/json', 'multipart/form-data']
        ]
    ],
    'csp' => [
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline'",
        'style-src' => "'self' 'unsafe-inline'",
        'img-src' => "'self' data:",
        'connect-src' => "'self'"
    ]
];
