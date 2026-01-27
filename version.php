<?php
require_once __DIR__ . '/core/bootstrap.php';
// version.php — simple CMS version manifest (framework-free)
if (!defined('CMS_NAME')) { define('CMS_NAME', 'Jessie AI-CMS'); }
if (!defined('CMS_VERSION')) { define('CMS_VERSION', '1.0.0'); }
if (!defined('CMS_RELEASE_DATE')) { define('CMS_RELEASE_DATE', '2025-11-05'); }
// Build time = this file mtime (stable across requests)
$__b = @filemtime(__FILE__); if (!defined('CMS_BUILD')) { define('CMS_BUILD', $__b ? gmdate('c', $__b) : gmdate('c')); }
// Env flag (derived from DEV_MODE when present)
if (!defined('CMS_ENV')) { define('CMS_ENV', (defined('DEV_MODE') && DEV_MODE === true) ? 'dev' : 'prod'); }
// Human-readable helper
if (!function_exists('cms_version_string')) {
  function cms_version_string(): string {
    $suf = (CMS_ENV === 'dev') ? '-dev' : '';
    return CMS_VERSION . $suf . ' (build ' . CMS_BUILD . ')';
  }
}
// Expose version header ONLY for admin entry point
if (!headers_sent() && defined('CMS_ENTRY_POINT') && CMS_ENTRY_POINT === true) {
  header('X-CMS-Version: ' . CMS_VERSION . '; env=' . CMS_ENV);
}
