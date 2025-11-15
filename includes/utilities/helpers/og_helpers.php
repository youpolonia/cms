<?php
/**
 * Open Graph helper functions
 */

/**
 * Get sanitized Open Graph title
 * @param string|null $title Optional custom title
 * @return string Sanitized title
 */
function get_og_title(?string $title = null): string {
    global $page_title;
    $title = $title ?? $page_title ?? '';
    return htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

/**
 * Get Open Graph type
 * @param string|null $type Optional custom type (default: 'website')
 * @return string Sanitized type
 */
function get_og_type(?string $type = null): string {
    $type = $type ?? 'website';
    return htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
}

/**
 * Get current URL for Open Graph
 * @return string Sanitized URL
 */
function get_og_url(): string {
    $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
           $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Get Open Graph image URL with validation
 * @param string|null $image_url Optional image URL
 * @param string $default Default image path
 * @return string|null Validated URL or null if invalid
 */
function get_og_image(?string $image_url = null, string $default = '/assets/images/og-default.jpg'): ?string {
    $url = $image_url ?? $default;
    $valid = filter_var($url, FILTER_VALIDATE_URL);
    return $valid ? htmlspecialchars($url, ENT_QUOTES, 'UTF-8') : null;
}

/**
 * Get sanitized Open Graph description
 * @param string|null $description Optional description
 * @return string Sanitized description
 */
function get_og_description(?string $description = null): string {
    global $meta_description;
    $desc = $description ?? $meta_description ?? '';
    return htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
}
