<?php

return [
    // Token expiration in minutes (0 = never expires)
    'expiration' => 0,
    
    // Token abilities
    'abilities' => [
        'content:read',
        'content:write',
        'media:upload',
        'analytics:view'
    ],
    
    // Middleware for token authentication
    'middleware' => [
        'verify_token' => \App\Http\Middleware\VerifyToken::class,
        'check_abilities' => \App\Http\Middleware\CheckTokenAbilities::class
    ]
];
