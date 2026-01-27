<?php
/**
 * SEO Service - Handles all SEO-related operations
 */
class SeoService {
    /**
     * Generate SEO-friendly slug from string
     * @param string $input
     * @return string
     */
    public static function generateSlug(string $input): string {
        $slug = strtolower(trim($input));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        return preg_replace('/-+/', '-', $slug);
    }

    /**
     * Generate meta tags for page
     * @param array $params [title, description, keywords, type]
     * @return string HTML meta tags
     */
    public static function generateMetaTags(array $params): string {
        // Validate input
        if (!isset($params['title']) || !is_string($params['title'])) {
            throw new InvalidArgumentException('Title must be a string');
        }

        // Set defaults based on content type
        $type = $params['type'] ?? 'page';
        $defaults = [
            'page' => [
                'title' => 'Default Page Title',
                'description' => 'Default page description',
                'keywords' => 'default,page'
            ],
            'blog' => [
                'title' => 'Blog Post',
                'description' => 'Blog post description',
                'keywords' => 'blog,post'
            ],
            'company' => [
                'title' => 'Company Profile',
                'description' => 'Company profile page',
                'keywords' => 'company,profile,business'
            ]
        ];

        // Merge with type-specific defaults
        $typeDefaults = $defaults[$type] ?? $defaults['page'];
        $params = array_merge($typeDefaults, $params);

        // Generate canonical URL if not provided
        $canonical = $params['canonical'] ?? self::getCanonicalUrl();

        return <<<HTML
        <title>{$params['title']}</title>
        <meta name="description" content="{$params['description']}">
        <meta name="keywords" content="{$params['keywords']}">
        <link rel="canonical" href="$canonical">
        HTML;
    }

    /**
     * Get canonical URL for current page
     * @return string
     */
    public static function getCanonicalUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Analyze content for SEO optimization
     * @param string $content
     * @return array [keywords, readability_score, keyword_density]
     */
    public static function analyzeContent(string $content): array {
        $keywords = [];
        $wordCount = str_word_count($content);
        $sentences = preg_split('/[.!?]+/', $content);
        
        // Basic keyword extraction
        preg_match_all('/\b\w{4,}\b/', strtolower($content), $matches);
        $keywords = array_count_values($matches[0]);
        arsort($keywords);
        
        // Basic readability score (Flesch-Kincaid approximation)
        $readability = 206.835 - (1.015 * ($wordCount / count($sentences))) - (84.6 * (array_sum(array_map('strlen', $sentences)) / $wordCount));
        
        return [
            'keywords' => array_slice($keywords, 0, 10),
            'readability_score' => round($readability, 2),
            'keyword_density' => round(count($matches[0]) / max(1, $wordCount) * 100, 2)
        ];
    }

    /**
     * Calculate SEO score (0-100)
     * @param string $content
     * @return int
     */
    public static function getSeoScore(string $content): int {
        $analysis = self::analyzeContent($content);
        $score = 0;
        
        // Score based on keyword density (ideal 1-3%)
        $density = $analysis['keyword_density'];
        $score += min(30, $density * 10); // Max 30 points
        
        // Score based on readability (ideal 60-80)
        $readability = $analysis['readability_score'];
        $score += min(40, abs($readability - 60)); // Max 40 points
        
        // Score based on content length (ideal 300+ words)
        $wordCount = str_word_count($content);
        $score += min(30, $wordCount / 10); // Max 30 points
        
        return min(100, $score);
    }
}
