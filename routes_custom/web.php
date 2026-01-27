<?php
/**
 * Custom web routes (DEV only)
 * This file is loaded when DEV_MODE is true
 * Add custom routes here if needed
 */
if (!defined('DEV_MODE')) {
    require_once __DIR__ . '/../config.php';
}

// Return empty array - no custom routes defined
return [];
