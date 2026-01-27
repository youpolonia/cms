<?php
/**
 * Dynamic XML Sitemap Generator
 * Outputs XML sitemap for search engine crawlers
 */

define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/services/seoservice.php';

// Set XML content type
header('Content-Type: application/xml; charset=utf-8');

// Cache control - allow caching for 1 hour
header('Cache-Control: public, max-age=3600');

try {
    $db = \core\Database::connection();
    $seoService = new SeoService($db);

    // Output the sitemap
    echo $seoService->generateSitemap();

} catch (Exception $e) {
    // Log error but return empty sitemap
    error_log('Sitemap generation error: ' . $e->getMessage());

    // Fallback to basic sitemap
    require_once CMS_ROOT . '/core/seo.php';
    $seo = seo_get_settings();
    $canonical = trim($seo['canonical_base_url'] ?? '');

    if (empty($canonical)) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $canonical = $scheme . '://' . $host;
    }

    $baseUrl = htmlspecialchars($canonical, ENT_XML1, 'UTF-8');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo '  <url>' . "\n";
    echo '    <loc>' . $baseUrl . '/</loc>' . "\n";
    echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    echo '    <changefreq>daily</changefreq>' . "\n";
    echo '    <priority>1.0</priority>' . "\n";
    echo '  </url>' . "\n";
    echo '</urlset>' . "\n";
}
