<?php
declare(strict_types=1);

/**
 * Internal Temp Directory Helper
 *
 * Provides workspace-confined temporary file storage.
 * All temp operations use /var/www/html/cms/tmp_safe/ instead of system temp.
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Get the internal temp directory path
 * Creates directory with 0700 permissions if it doesn't exist
 *
 * @return string Absolute path to tmp_safe directory
 */
function cms_tmp_dir(): string
{
    $dir = CMS_ROOT . '/tmp_safe';

    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    return $dir;
}

/**
 * Get a path within the internal temp directory
 *
 * @param string $suffix Optional subdirectory or file suffix
 * @return string Absolute path within tmp_safe
 */
function cms_tmp_path(string $suffix = ''): string
{
    $base = cms_tmp_dir();

    if ($suffix === '') {
        return $base;
    }

    $suffix = ltrim($suffix, '/');
    return $base . '/' . $suffix;
}
