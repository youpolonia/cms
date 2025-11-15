<?php
/**
 * SEO Toolkit Core Module
 * Provides basic SEO analysis and recommendations
 */
class SEOToolkit {
    private $pageContent;
    private $metaData;
    private $aiHandler;

    public function __construct($content, $meta = []) {
        $this->pageContent = $content;
        $this->metaData = $meta;
        $this->aiHandler = new SEOApiHandler();
    }

    public function analyzeContent() {
        // Hook: before_seo_analysis
        if (function_exists('apply_hooks')) {
            $this->pageContent = apply_hooks('before_seo_analysis', $this->pageContent);
        }

        $analysis = [
            'word_count' => str_word_count($this->pageContent),
            'keyword_density' => $this->calculateKeywordDensity(),
            'meta_tags' => $this->checkMetaTags()
        ];

        // Integrate with AI components
        if (class_exists('SEORecommendationEngine')) {
            $analysis['recommendations'] = SEORecommendationEngine::generate($this->pageContent);
        }

        // Hook: after_seo_analysis
        if (function_exists('apply_hooks')) {
            $analysis = apply_hooks('after_seo_analysis', $analysis);
        }

        return $analysis;
    }

    private function calculateKeywordDensity() {
        // Basic keyword analysis implementation
        $words = str_word_count(strtolower($this->pageContent), 1);
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        return array_slice($wordFreq, 0, 5);
    }

    private function checkMetaTags() {
        $required = ['title', 'description'];
        $missing = array_diff($required, array_keys($this->metaData));
        return [
            'has_title' => !empty($this->metaData['title']),
            'has_description' => !empty($this->metaData['description']),
            'missing' => $missing
        ];
    }
    /**
     * Generate SEO meta tags with plugin support
     * @return array Generated meta tags
     */
    public function generateMetaTags() {
        $meta = [
            'title' => $this->metaData['title'] ?? '',
            'description' => $this->metaData['description'] ?? '',
            'keywords' => $this->calculateKeywordDensity()
        ];

        // Hook: seo_meta_generation
        if (function_exists('apply_hooks')) {
            $meta = apply_hooks('seo_meta_generation', $meta);
        }

        return $meta;
    }
}
