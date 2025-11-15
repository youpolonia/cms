<?php
/**
 * Core Initialization
 * 
 * Handles system-wide initialization and debug mode settings
 * 
 * @package CMS
 */

// Load debug configuration
require_once __DIR__ . '/../config/debug.php';

// Set error reporting based on DEV_MODE
if (defined('DEV_MODE') && DEV_MODE === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Record script start time for performance tracking
    $GLOBALS['SCRIPT_START_TIME'] = microtime(true);
}
