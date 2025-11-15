<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../config.php';

/**
 * Dynamic robots.txt endpoint
 * Outputs rules configured in config/robots.php
 */

try {
    // Require config file
    require_once __DIR__ . '/../config/robots.php';
    
    // Set content type
    header('Content-Type: text/plain');
    
    // Get rules from config
    $rules = getRobotsRules();
    
    // Output rules in proper robots.txt format
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
} catch (Throwable $e) {
    // Fallback to minimal safe rules if config fails
    header('Content-Type: text/plain');
    echo "User-agent: *\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /config/\n";
    echo "Disallow: /includes/\n";
}
