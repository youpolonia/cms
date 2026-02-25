<?php
/**
 * Jessie AI Copywriter — plugin bootstrap
 * AI-powered product descriptions & ad copy for multiple platforms
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }

// Autoload includes
require_once __DIR__ . '/includes/class-copywriter-core.php';
require_once __DIR__ . '/includes/class-copywriter-brand.php';
require_once __DIR__ . '/includes/class-copywriter-platform.php';
require_once __DIR__ . '/includes/class-copywriter-bulk.php';
