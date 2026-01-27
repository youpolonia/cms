<?php
/**
 * Application Constants
 * Defines path constants and other global constants
 */

define('THEMES_DIR', '/themes/');
define('ASSETS_DIR', '/assets/');
if (!defined('DEV_MODE')) define('DEV_MODE', false);

define('LOG_PATH', __DIR__.'/../logs/errors.log');
define('WORKFLOW_LOG_PATH', __DIR__.'/../logs/workflow.log');
