<?php
// Debug mode configuration
if (!defined('DEV_MODE')) define('DEV_MODE', false); // Set to true to enable debug features

// Query logging configuration
define('QUERY_LOG_ENABLED', true); // Requires DEV_MODE = true
define('QUERY_LOG_PATH', __DIR__ . '/../storage/logs/query.log');
define('QUERY_LOG_MAX_SIZE', 5 * 1024 * 1024); // 5MB
