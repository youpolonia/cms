<?php
return [
    'default_connection' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => (defined('DB_HOST') ? DB_HOST : 'localhost'),
            'port' => 3306,
            'database' => (defined('DB_NAME') ? DB_NAME : 'cms_database'),
            'username' => (defined('DB_USER') ? DB_USER : 'cms_user'),
            'password' => (defined('DB_PASS') ? DB_PASS : ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'unix_socket' => '/var/run/mysqld/mysqld.sock', // Common default socket path
        ]
    ],
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
