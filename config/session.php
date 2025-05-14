<?php
/**
 * Session Configuration - Enhanced Security
 */
return [
    // Session encryption
    'encrypt' => true,
    'encryption_key' => env('SESSION_ENCRYPTION_KEY', bin2hex(random_bytes(32))),

    // Cookie settings
    'cookie_name' => '__Secure-CMS-Session',
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_partitioned' => true,

    // Session regeneration
    'regenerate_interval' => 300, // 5 minutes
    'regenerate_on_auth' => true,

    // Session storage
    'storage' => 'database',
    'table' => 'sessions',
    'gc_maxlifetime' => 14400, // 4 hours
];
