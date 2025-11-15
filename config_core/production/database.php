<?php
declare(strict_types=1);

return [
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'cms_prod',
    'password' => 'PROD_DB_PASSWORD', // Replace with actual password
    'database' => 'cms_production',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => 'InnoDB',
    'timezone' => '+00:00',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];
