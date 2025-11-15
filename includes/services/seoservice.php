<?php
/**
 * SEO Service - Handles content analysis, meta generation and optimization
 */
class SeoService {
    private $aiClient;
    private $contentScorer;
    
    public function __construct() {
        $this->aiClient = new AIClient();
        $this->contentScorer = new ContentScorer();
    }

    /**
     * Analyze content and return comprehensive SEO results
     */
    public function analyzeContent(string $content): SeoAnalysisResult {
        $result = new SeoAnalysisResult();
        
        // Basic metrics
        $result->wordCount = str_word_count($content);
        $result->readabilityScore = $this->calculateReadabilityScore($content);
        
        // AI-powered analysis
        $result->keywords = $this->generateKeywords($content);
        $result->optimizationTips = $this->getAiOptimizationSuggestions($content);
        
        return $result;
    }

    /**
     * Extract keywords from content
     */
    public function generateKeywords(string $content): array {
        return $this->aiClient->analyzeKeywords($content);
    }

    /**
     * Calculate Flesch-Kincaid readability score
     */
    public function calculateReadabilityScore(string $content): float {
        return $this->contentScorer->calculateReadability($content);
    }

    /**
     * Generate meta tags for content
     */
    public function generateMetaTags(array $contentData): array {
        $tags = [
            'title' => $contentData['title'] ?? '',
            'description' => $contentData['description'] ?? '',
            'og:title' => $contentData['title'] ?? '',
            'og:description' => $contentData['description'] ?? ''
        ];
        
        if (isset($contentData['image'])) {
            $tags['og:image'] = $contentData['image'];
        }
        
        return $tags;
    }

    /**
     * Get AI-powered optimization suggestions
     */
    public function getAiOptimizationSuggestions(string $content): array {
        return $this->aiClient->getSeoSuggestions($content);
    }
}

/**
 * SEO Analysis Result DTO
 */
class SeoAnalysisResult {
    public $wordCount;
    public $readabilityScore;
    public $keywords = [];
    public $optimizationTips = [];
}
