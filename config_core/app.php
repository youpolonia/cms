<?php
/**
 * Application Configuration
 */

return [
    // Debug settings
    'debug' => false, // Set to true only in development
    'debug_log' => 'storage/logs/debug.log',
    
    // Application settings
    'timezone' => 'UTC',
    'locale' => 'en_US',
    
    // Security settings
    'csp_enabled' => true,
    'xss_protection' => true,
    
    // Performance settings
    'cache_enabled' => true,
    'cache_ttl' => 3600 // 1 hour
];
