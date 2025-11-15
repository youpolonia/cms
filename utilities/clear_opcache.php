<?php
/**
 * OPCache Clear Utility
 * 
 * @package CMS
 * @version 1.0.0
 */

if (!defined('CMS_ROOT')) {
    die('Direct access not allowed');
}

header('Content-Type: text/plain');

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPCache cleared successfully\n";
    } else {
        echo "OPCache reset failed\n";
    }
} else {
    echo "OPCache is not enabled\n";
}

// Additional status information
echo "PHP Version: " . phpversion() . "\n";
echo "OPCache Status: " . (function_exists('opcache_get_status') ? 'Enabled' : 'Disabled') . "\n";
