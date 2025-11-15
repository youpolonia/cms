<?php
/**
 * CMS Path Constants
 *
 * Defines core filesystem paths for the CMS
 *
 * @package Core
 */

// Define CMS root directory (absolute path)
define('CMS_ROOT', dirname(__DIR__));

// Core directories
define('CORE_DIR', CMS_ROOT . '/core');
define('API_DIR', CMS_ROOT . '/public/api');
define('INCLUDES_DIR', CMS_ROOT . '/includes');
define('PUBLIC_DIR', CMS_ROOT . '/public');
define('CONFIG_DIR', CMS_ROOT . '/config');
define('PLUGINS_DIR', CMS_ROOT . '/plugins');
define('DATABASE_DIR', CMS_ROOT . '/database');
define('STORAGE_DIR', CMS_ROOT . '/storage');
define('LOGS_DIR', CMS_ROOT . '/logs');
define('TENANTS_DIR', CMS_ROOT . '/tenants');
define('THEMES_DIR', CMS_ROOT . '/themes');

// Ensure the constants are only defined once
if (!defined('CMS_CONSTANTS_LOADED')) {
    define('CMS_CONSTANTS_LOADED', true);
}
