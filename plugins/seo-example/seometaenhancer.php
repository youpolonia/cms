<?php
/**
 * SEO Meta Enhancer Example Plugin
 * Demonstrates SEO Toolkit hook usage
 */
class SEOMetaEnhancer {
    public function __construct() {
        // Register hooks
        add_hook('before_seo_analysis', [$this, 'cleanContent']);
        add_hook('after_seo_analysis', [$this, 'addCustomMetrics']); 
        add_hook('seo_meta_generation', [$this, 'enhanceMetaTags']);
    }

    /**
     * Clean content before analysis
     */
    public function cleanContent($content) {
        // Remove HTML comments
        return preg_replace('/<!--.*?-->/s', '', $content);
    }

    /**
     * Add custom metrics to analysis
     */
    public function addCustomMetrics($analysis) {
        $analysis['heading_structure'] = $this->analyzeHeadings($analysis);
        return $analysis;
    }

    /**
     * Enhance generated meta tags
     */
    public function enhanceMetaTags($meta) {
        // Add Twitter card meta
        $meta['twitter:card'] = 'summary';
        $meta['twitter:title'] = $meta['title'];
        $meta['twitter:description'] = $meta['description'];
        
        return $meta;
    }

    /**
     * Analyze heading structure (example helper)
     */
    private function analyzeHeadings($analysis) {
        // Implementation would parse content for headings
        return [
            'h1_count' => 1,
            'h2_count' => 3,
            'hierarchy_valid' => true
        ];
    }
}

// Initialize plugin
new SEOMetaEnhancer();
