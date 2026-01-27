<?php
return [
    'host' => '0.0.0.0',
    'port' => 80,
    'update_interval' => 5, // seconds
    'allowed_origins' => [
        'yourdomain.com',
        'admin.yourdomain.com'
    ],
    'max_connections' => 100,
    'ssl' => [
        'local_cert' => '/path/to/cert.pem',
        'local_pk' => '/path/to/privkey.pem',
        'allow_self_signed' => false,
        'verify_peer' => false
    ]
];
