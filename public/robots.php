<?php
/**
 * Dynamic robots.txt endpoint
 * Outputs rules based on SEO settings
 */

define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';

// Set content type
header('Content-Type: text/plain; charset=utf-8');

// Cache control - allow caching for 1 hour
header('Cache-Control: public, max-age=3600');

try {
    // Try to use the new SeoService
    require_once CMS_ROOT . '/core/database.php';
    require_once CMS_ROOT . '/core/services/seoservice.php';

    $db = \core\Database::connection();
    $seoService = new SeoService($db);

    echo $seoService->generateRobotsTxt();

} catch (Exception $e) {
    // Log error
    error_log('robots.txt generation error: ' . $e->getMessage());

    // Fallback to config-based or minimal rules
    try {
        if (file_exists(CMS_ROOT . '/config/robots.php')) {
            require_once CMS_ROOT . '/config/robots.php';

            if (function_exists('getRobotsRules')) {
                $rules = getRobotsRules();

                foreach ($rules as $rule) {
                    if (isset($rule['User-agent'])) {
                        echo "User-agent: " . $rule['User-agent'] . "\n";
                    }

                    if (isset($rule['Disallow']) && is_array($rule['Disallow'])) {
                        foreach ($rule['Disallow'] as $path) {
                            echo "Disallow: " . $path . "\n";
                        }
                    }

                    if (isset($rule['Allow']) && is_array($rule['Allow'])) {
                        foreach ($rule['Allow'] as $path) {
                            echo "Allow: " . $path . "\n";
                        }
                    }

                    if (isset($rule['Crawl-delay'])) {
                        echo "Crawl-delay: " . $rule['Crawl-delay'] . "\n";
                    }

                    if (isset($rule['Sitemap'])) {
                        echo "Sitemap: " . $rule['Sitemap'] . "\n";
                    }

                    echo "\n";
                }
                return;
            }
        }
    } catch (Exception $e2) {
        // Fall through to minimal rules
    }

    // Ultimate fallback - minimal safe rules
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "\n";
    echo "# Protected directories\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /config/\n";
    echo "Disallow: /core/\n";
    echo "Disallow: /includes/\n";
    echo "Disallow: /logs/\n";
    echo "Disallow: /backups/\n";
    echo "\n";
    echo "# Sitemap\n";
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    echo "Sitemap: " . $scheme . "://" . $host . "/sitemap.php\n";
}
