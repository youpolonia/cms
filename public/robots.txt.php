<?php

define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/seo.php';

header('Content-Type: text/plain; charset=UTF-8');

$seo = seo_get_settings();
$robotsIndex = $seo['robots_index'] ?? 'index';
$canonical = trim($seo['canonical_base_url'] ?? '');

if (empty($canonical)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $canonical = $scheme . '://' . $host;
}

echo "User-agent: *\n";

if ($robotsIndex === 'noindex') {
    echo "Disallow: /\n";
} else {
    echo "Allow: /\n";
}

echo "Sitemap: " . $canonical . "/sitemap.php\n";
