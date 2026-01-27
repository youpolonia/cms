<?php

return [
    'driver' => getenv('SESSION_DRIVER') ?: 'database',
    'lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 120),
    'expire_on_close' => getenv('SESSION_EXPIRE_ON_CLOSE') !== false ? (bool)getenv('SESSION_EXPIRE_ON_CLOSE') : false,
    'encrypt' => getenv('SESSION_ENCRYPT') !== false ? (bool)getenv('SESSION_ENCRYPT') : false,
    'files' => \CMS_ROOT . '/storage/framework/sessions',
    'connection' => getenv('SESSION_CONNECTION') ?: null,
    'table' => getenv('SESSION_TABLE') ?: 'sessions',
    'store' => getenv('SESSION_STORE') ?: null,
    'lottery' => [2, 100],
    'cookie' => getenv('SESSION_COOKIE') ?: (strtolower(str_replace(' ', '_', getenv('APP_NAME') ?: 'laravel')) . '_session'),
    'path' => getenv('SESSION_PATH') ?: '/',
    'domain' => getenv('SESSION_DOMAIN') ?: null,
    'secure' => getenv('SESSION_SECURE_COOKIE') !== false ? (bool)getenv('SESSION_SECURE_COOKIE') : false,
    'http_only' => getenv('SESSION_HTTP_ONLY') !== false ? (bool)getenv('SESSION_HTTP_ONLY') : true,
    'same_site' => 'lax',
    'partitioned' => getenv('SESSION_PARTITIONED_COOKIE') !== false ? (bool)getenv('SESSION_PARTITIONED_COOKIE') : false
];
