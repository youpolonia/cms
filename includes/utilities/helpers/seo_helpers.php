<?php
/**
 * SEO Helper Functions
 */

/**
 * Get canonical URL for content entry
 * 
 * @param array $entry Content entry data
 * @return string Canonical URL
 */
function get_canonical_url(array $entry): string {
    // Use explicit canonical_url if set
    if (!empty($entry['canonical_url'])) {
        return sanitize_url($entry['canonical_url']);
    }

    // Generate URL from content type and slug
    $path = '/' . $entry['content_type'] . '/' . $entry['slug'];
    
    // Get base URL from config or server vars
    $baseUrl = get_base_url();
    
    return $baseUrl . $path;
}

/**
 * Get base URL from config or server vars
 * 
 * @return string Base URL with protocol and domain
 */
function get_base_url(): string {
    // Check if configured in settings
    if (defined('SITE_URL')) {
        return rtrim(SITE_URL, '/');
    }

    // Fallback to server vars
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    return $protocol . '://' . $host;
}

/**
 * Sanitize URL to prevent XSS
 * 
 * @param string $url
 * @return string Sanitized URL
 */
function sanitize_url(string $url): string {
    $url = trim($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // Ensure absolute URL if relative
    if (strpos($url, 'http') !== 0) {
        $url = get_base_url() . '/' . ltrim($url, '/');
    }
    
    return $url;
}
