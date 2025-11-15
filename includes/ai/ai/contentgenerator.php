<?php
/**
 * AI Content Generator Example
 * 
 * Provides ready-to-use content generation functions
 */

require_once __DIR__ . '/../core/AI.php';

class ContentGenerator {
    private $ai;
    
    public function __construct($apiKey) {
        $this->ai = new AIHandler($apiKey);
    }

    /**
     * Generate SEO-optimized article
     */
    public function generateArticle($topic, $keywords = '', $tone = 'professional') {
        $prompt = "Write a 500-word article about $topic";
        
        if (!empty($keywords)) {
            $prompt .= " that includes these keywords: $keywords";
        }
        
        $prompt .= ". Use a $tone tone.";
        
        return $this->ai->generateText($prompt);
    }

    /**
     * Generate product description
     */
    public function generateProductDescription($productName, $features, $tone = 'professional') {
        $prompt = "Write a compelling product description for $productName with these features: " 
            . implode(', ', $features) . ". Use a $tone tone.";
            
        return $this->ai->generateText($prompt);
    }
}
