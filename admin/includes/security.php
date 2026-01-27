<?php
// security.php – normalized bootstrap require_once for all admin pages.
// From /admin/includes/ to project root (/cms) is two levels up.
$BOOTSTRAP = dirname(__DIR__, 1) . '/config.php'; // /cms/config.php

if (is_file($BOOTSTRAP)) {
    require_once __DIR__ . '/../config.php';
} else {
    // Do NOT hard-fatal here; define flags so callers can render a friendly error.
    if (!defined('CMS_BOOTSTRAP_MISSING')) {
        define('CMS_BOOTSTRAP_MISSING', true);
        define('CMS_BOOTSTRAP_EXPECTED', $BOOTSTRAP);
    }
    // Return control to caller; avoid exit/throw to prevent HTTP 500.
    return;
}
