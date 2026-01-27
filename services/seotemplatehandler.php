<?php
/**
 * SEO Template Handler - Integrates SEO features with TemplateProcessor
 */
class SeoTemplateHandler {
    private SeoService $seoService;
    
    public function __construct(SeoService $seoService) {
        $this->seoService = $seoService;
    }

    /**
     * Process content and inject SEO meta tags
     */
    public function processContent(string $content): string {
        $analysis = $this->seoService->analyzeContent($content);
        $metaTags = $this->seoService->generateMetaTags($analysis);
        
        return $this->injectMetaTags($content, $metaTags);
    }

    private function injectMetaTags(string $content, array $metaTags): string {
        $metaHtml = "\n<!-- SEO Meta Tags -->\n";
        $metaHtml .= "<meta name=\"title\" content=\"{$metaTags['title']}\">\n";
        $metaHtml .= "<meta name=\"description\" content=\"{$metaTags['description']}\">\n";
        $metaHtml .= "<meta name=\"keywords\" content=\"{$metaTags['keywords']}\">\n";

        // Inject before closing head tag if exists, otherwise at start of body
        if (strpos($content, '</head>') !== false) {
            return str_replace('</head>', $metaHtml.'</head>', $content);
        }
        return str_replace('<body>', '<body>'.$metaHtml, $content);
    }

    /**
     * Get SEO analysis for content editor
     */
    public function getAnalysisForEditor(string $content): array {
        $analysis = $this->seoService->analyzeContent($content);
        return [
            'score' => $this->calculateOverallScore($analysis),
            'suggestions' => $this->generateSuggestions($analysis)
        ];
    }

    private function calculateOverallScore(array $analysis): int {
        $readabilityWeight = 0.6;
        $keywordWeight = 0.4;
        
        $readabilityScore = $analysis['readability'];
        $keywordScore = min(100, count($analysis['keywords']) * 20);
        
        return (int)round(
            ($readabilityScore * $readabilityWeight) + 
            ($keywordScore * $keywordWeight)
        );
    }

    private function generateSuggestions(array $analysis): array {
        $suggestions = [];
        
        if ($analysis['readability'] < SeoService::MIN_READABILITY_SCORE) {
            $suggestions[] = 'Improve readability by shortening sentences and using simpler words';
        }
        
        if (count($analysis['keywords']) < 3) {
            $suggestions[] = 'Add more relevant keywords to improve SEO';
        }
        
        if ($analysis['word_count'] < 300) {
            $suggestions[] = 'Consider expanding content (minimum 300 words recommended)';
        }
        
        return $suggestions;
    }
}
