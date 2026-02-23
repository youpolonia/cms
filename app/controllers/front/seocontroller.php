<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class SeoController
{
    /**
     * Dynamic robots.txt
     * GET /robots.txt
     */
    public function robots(Request $request): void
    {
        $pdo = db();
        $siteUrl = '';

        // Try to get site_url from settings
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute(['site_url']);
        $siteUrl = $stmt->fetchColumn() ?: ('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $siteUrl = rtrim($siteUrl, '/');

        // Check for custom robots.txt content in settings
        $stmt->execute(['robots_txt']);
        $custom = $stmt->fetchColumn();

        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');

        if ($custom) {
            echo $custom;
            return;
        }

        // Default robots.txt
        echo "# Jessie CMS — robots.txt\n";
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "\n";
        echo "# Admin area\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /api/\n";
        echo "\n";
        echo "# Assets\n";
        echo "Allow: /public/assets/\n";
        echo "Allow: /themes/\n";
        echo "Allow: /uploads/\n";
        echo "\n";
        echo "# Sitemap\n";
        echo "Sitemap: {$siteUrl}/sitemap.xml\n";
    }

    /**
     * Serve favicon from active theme or uploads
     * GET /favicon.ico
     */
    public function favicon(Request $request): void
    {
        // 1. Check Theme Studio brand.favicon setting
        if (function_exists('theme_get')) {
            $favicon = theme_get('brand.favicon');
            if ($favicon && file_exists(CMS_ROOT . '/public' . $favicon)) {
                $this->serveFile(CMS_ROOT . '/public' . $favicon);
                return;
            }
            if ($favicon && file_exists(CMS_ROOT . $favicon)) {
                $this->serveFile(CMS_ROOT . $favicon);
                return;
            }
        }

        // 2. Check active theme directory for favicon
        $themeDir = theme_path();
        foreach (['favicon.ico', 'favicon.png', 'favicon.svg', 'assets/favicon.ico'] as $f) {
            $path = $themeDir . '/' . $f;
            if (file_exists($path)) {
                $this->serveFile($path);
                return;
            }
        }

        // 3. Check uploads
        if (file_exists(CMS_ROOT . '/uploads/favicon.ico')) {
            $this->serveFile(CMS_ROOT . '/uploads/favicon.ico');
            return;
        }

        // 4. Generate a minimal SVG favicon from theme primary color
        $primary = '#6366f1';
        if (function_exists('theme_get')) {
            $primary = theme_get('brand.primary_color') ?: theme_get('brand.color_primary') ?: $primary;
        }

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">'
           . '<rect width="32" height="32" rx="6" fill="' . htmlspecialchars($primary) . '"/>'
           . '<text x="16" y="22" font-size="18" font-weight="bold" fill="#fff" text-anchor="middle" font-family="system-ui">J</text>'
           . '</svg>';
    }

    private function serveFile(string $path): void
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $types = ['ico' => 'image/x-icon', 'png' => 'image/png', 'svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'gif' => 'image/gif'];
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=604800');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
