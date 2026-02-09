<?php
/**
 * Cache Clear Utility - DEV MODE ONLY
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}
require_once CMS_ROOT . '/config.php';


// Clear OPcache
$opcacheResult = 'Not available';
if (function_exists('opcache_reset')) {
    $opcacheResult = opcache_reset() ? 'Cleared' : 'Failed';
}

// Redirect back with message
$referer = $_SERVER['HTTP_REFERER'] ?? '/admin';
header('Location: ' . $referer . (strpos($referer, '?') !== false ? '&' : '?') . 'cache_cleared=1');
exit;
