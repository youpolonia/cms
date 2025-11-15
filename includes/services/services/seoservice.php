<?php
/**
 * SEO Service - Handles all SEO-related functionality
 * 
 * Features:
 * - Canonical URL generation
 * - Meta title/description helpers
 * - Slug validation with uniqueness check
 * - AI content generation support
 * - Static methods only (no instance needed)
 */
class SeoService {
    /**
     * Generate canonical URL
     * @param string $slug Content slug
     * @param string $contentType Content type (e.g., 'page', 'post')
     * @return string Full canonical URL
     */
    public static function generateCanonicalUrl(string $slug, string $contentType): string {
        $baseUrl = self::getBaseUrl();
        return "{$baseUrl}/{$contentType}/{$slug}";
    }

    /**
     * Generate meta title
     * @param string $title Content title
     * @param string|null $aiTitle AI-generated title (optional)
     * @param string $siteName Site name suffix
     * @return string Optimized title
     */
    public static function generateMetaTitle(string $title, ?string $aiTitle = null, string $siteName = 'Site Name'): string {
        $baseTitle = $aiTitle ?? $title;
        return "{$baseTitle} | {$siteName}";
    }

    /**
     * Generate meta description
     * @param string $content Content text
     * @param string|null $aiDescription AI-generated description (optional)
     * @param int $maxLength Max description length (default: 160)
     * @return string Optimized description
     */
    public static function generateMetaDescription(string $content, ?string $aiDescription = null, int $maxLength = 160): string {
        if ($aiDescription) {
            return $aiDescription;
        }
        
        $cleanContent = strip_tags($content);
        return substr($cleanContent, 0, $maxLength);
    }

    /**
     * Validate and generate unique slug
     * @param string $title Input title
     * @param string $contentType Content type
     * @param string|null $existingSlug Existing slug (for updates)
     * @return string Validated unique slug
     */
    public static function validateSlug(string $title, string $contentType, ?string $existingSlug = null): string {
        $slug = self::generateSlug($title);
        
        if ($existingSlug && $slug === $existingSlug) {
            return $existingSlug;
        }

        // TODO: Implement actual uniqueness check against database
        // For now, append timestamp if slug exists
        if (self::slugExists($slug, $contentType)) {
            return $slug . '-' . time();
        }

        return $slug;
    }

    /**
     * Generate basic slug from title
     * @param string $title Input title
     * @return string Generated slug
     */
    private static function generateSlug(string $title): string {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        return preg_replace('/-+/', '-', $slug);
    }

    /**
     * Check if slug exists (placeholder implementation)
     * @param string $slug Slug to check
     * @param string $contentType Content type
     * @return bool
     */
    private static function slugExists(string $slug, string $contentType): bool {
        // TODO: Replace with actual database check
        return false;
    }

    /**
     * Get AI-generated SEO content if available
     * @param string $content Original content
     * @return array|null Array with 'title' and 'description' or null
     */
    public static function getAiSeoContent(string $content): ?array {
        if (!self::isAiAvailable()) {
            return null;
        }
        
        try {
            return AIClient::generateSeoContent($content);
        } catch (Exception $e) {
            error_log("AI SEO generation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if AI service is available
     * @return bool
     */
    private static function isAiAvailable(): bool {
        return class_exists('AIClient') && method_exists('AIClient', 'generateSeoContent');
    }

    /**
     * Get base URL
     * @return string
     */
    private static function getBaseUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return "{$protocol}://{$_SERVER['HTTP_HOST']}";
    }
}
