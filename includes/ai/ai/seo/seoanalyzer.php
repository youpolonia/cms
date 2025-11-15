<?php

namespace AI\SEO;

use AI\AIManager;
use AI\AIProviderInterface;

class SEOAnalyzer {
    private AIManager $aiManager;
    
    public function __construct(AIManager $aiManager) {
        $this->aiManager = $aiManager;
    }

    /**
     * Analyze content for SEO optimization
     */
    public function analyzeContent(string $content): array {
        $analysis = [
            'keywords' => $this->extractKeywords($content),
            'readability' => $this->calculateReadability($content),
            'meta_suggestions' => $this->generateMetaSuggestions($content)
        ];

        return $analysis;
    }

    private function extractKeywords(string $content): array {
        // Use Hugging Face for keyword extraction
        return $this->aiManager->getProvider('huggingface')
            ->analyze('keywords', $content);
    }

    private function calculateReadability(string $content): int {
        // Use OpenAI for readability scoring
        return $this->aiManager->getProvider('openai')
            ->analyze('readability', $content);
    }

    private function generateMetaSuggestions(string $content): array {
        // Use OpenAI for meta suggestions
        return $this->aiManager->getProvider('openai')
            ->analyze('meta_suggestions', $content);
    }
}
