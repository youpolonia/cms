<?php
define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/seo.php';

$settings = seo_get_settings();

$base = rtrim($settings['canonical_base_url'] ?? '', '/');

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";

if (empty($base)) {
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    echo "\n";
    echo '</urlset>';
    echo "\n";
} else {
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    echo "\n";

    // Core URLs
    $urls = [];
    $urls[] = $base . '/';
    $urls[] = $base . '/articles';
    $urls[] = $base . '/pages';

    // Dynamically add published articles and pages
    try {
        $pdo = \core\Database::connection();

        // Fetch published blog posts
        $stmt = $pdo->prepare('SELECT slug FROM content WHERE type = ? AND status = ? ORDER BY created_at DESC');
        $stmt->execute(['blog', 'published']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['slug'])) {
                $urls[] = $base . '/blog/' . $row['slug'];
            }
        }

        // Fetch published pages
        $stmt = $pdo->prepare('SELECT slug FROM content WHERE type = ? AND status = ? ORDER BY created_at DESC');
        $stmt->execute(['page', 'published']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['slug'])) {
                $urls[] = $base . '/' . $row['slug'];
            }
        }

    } catch (\Throwable $e) {
        // On any error, gracefully degrade to core URLs only
        // Do not leak error messages
    }

    // Render all URLs
    foreach ($urls as $loc) {
        echo '  <url>';
        echo "\n";
        echo '    <loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>';
        echo "\n";
        echo '  </url>';
        echo "\n";
    }

    echo '</urlset>';
    echo "\n";
}
