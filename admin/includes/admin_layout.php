<?php
/**
 * Admin Layout - Legacy Pages with Dark Topbar
 * DO NOT add closing ?> tag
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

$versionFile = CMS_ROOT . '/version.php';
if (is_file($versionFile)) {
    require_once $versionFile;
}

if (!function_exists('admin_render_page_start')) {
    function admin_render_page_start(string $title = 'Admin'): void
    {
        $pageTitle = $title;
        header('Content-Type: text/html; charset=UTF-8');
        // Include full header with topbar
        require_once CMS_ROOT . '/admin/includes/header.php';
    }
}

if (!function_exists('admin_render_page_end')) {
    function admin_render_page_end(): void
    {
        // Include footer
        require_once CMS_ROOT . '/admin/includes/footer.php';
    }
}
