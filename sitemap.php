<?php
/**
 * Dynamic Sitemap Generator
 * Serves XML sitemap with all published pages and articles
 * URL: /sitemap.xml (routed via .htaccess)
 */
define('CMS_ROOT', __DIR__);
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';

// SEO settings
$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$settings = file_exists($seoConfigPath) ? (json_decode(@file_get_contents($seoConfigPath), true) ?? []) : [];
$base = rtrim($settings['canonical_base_url'] ?? '', '/');

if ($base === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $scheme . '://' . $host;
}

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$urls = [];

// Homepage
$urls[] = [
    'loc'        => $base . '/',
    'lastmod'    => date('Y-m-d'),
    'changefreq' => 'daily',
    'priority'   => '1.0',
];

try {
    $pdo = \core\Database::connection();

    // Published pages
    $stmt = $pdo->query("SELECT slug, updated_at FROM pages WHERE status = 'published' ORDER BY updated_at DESC");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $slug = trim($row['slug'] ?? '');
        if ($slug === '' || $slug === 'home') continue;
        $urls[] = [
            'loc'        => $base . '/' . $slug,
            'lastmod'    => substr($row['updated_at'] ?? date('Y-m-d'), 0, 10),
            'changefreq' => 'weekly',
            'priority'   => '0.8',
        ];
    }

    // Published articles
    $stmt = $pdo->query("SELECT slug, updated_at, published_at FROM articles WHERE status = 'published' ORDER BY published_at DESC");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $slug = trim($row['slug'] ?? '');
        if ($slug === '') continue;
        $date = $row['updated_at'] ?? $row['published_at'] ?? date('Y-m-d');
        $urls[] = [
            'loc'        => $base . '/article/' . $slug,
            'lastmod'    => substr($date, 0, 10),
            'changefreq' => 'monthly',
            'priority'   => '0.6',
        ];
    }
} catch (\Exception $e) {
    // Serve whatever we have (at least homepage)
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
    if (!empty($u['lastmod']))    echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
    if (!empty($u['changefreq'])) echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    if (!empty($u['priority']))   echo "    <priority>{$u['priority']}</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
