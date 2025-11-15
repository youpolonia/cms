<?php
return [
    'cookie_name' => 'CMSSESSID_ADMIN',
    'cookie_params' => [
        'lifetime' => 86400, // 24 hours
        'path' => '/admin',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    'gc_maxlifetime' => 86400,
    'session_name' => 'CMS_ADMIN_SESSION'
];
