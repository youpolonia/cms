<?php
// Include common functions and configuration
require_once __DIR__ . '/config.php';

// Include core interfaces
require_once __DIR__ . '/core/interfaces/cacheinterface.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', __DIR__);
