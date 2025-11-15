<?php

return [
    'lifetime' => 1440, // 24 minutes
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => true,
    'http_only' => true,
    'same_site' => 'Strict',
    'cookie_partitioned' => false,
    'encrypt' => true,
    'cookie_name' => 'secure_session'
];
