<?php

namespace AI\SEO;

use AI\AIManager;
use Database\DatabaseConnection;

class SEORecommendationEngine {
    private AIManager $aiManager;
    private DatabaseConnection $db;
    
    public function __construct(AIManager $aiManager, DatabaseConnection $db) {
        $this->aiManager = $aiManager;
        $this->db = $db;
    }

    /**
     * Generate comprehensive SEO recommendations
     */
    public function generateRecommendations(array $analysis, int $versionId): array {
        $recommendations = [
            'keywords' => $this->generateKeywordRecommendations($analysis['keywords']),
            'readability' => $this->generateReadabilityRecommendations($analysis['readability']),
            'meta_tags' => $this->generateMetaTagRecommendations($analysis['meta_suggestions'])
        ];

        $this->storeRecommendations($versionId, $recommendations);

        return $recommendations;
    }

    private function generateKeywordRecommendations(array $keywords): array {
        // Use OpenAI to suggest keyword optimizations
        return $this->aiManager->getProvider('openai')
            ->analyze('keyword_recommendations', $keywords);
    }

    private function generateReadabilityRecommendations(int $score): array {
        // Generate readability improvement suggestions
        return $this->aiManager->getProvider('openai')
            ->analyze('readability_recommendations', ['score' => $score]);
    }

    private function generateMetaTagRecommendations(array $suggestions): array {
        // Filter and format meta tag suggestions
        return array_map(function($suggestion) {
            return [
                'type' => $suggestion['type'],
                'value' => substr($suggestion['value'], 0, 255)
            ];
        }, $suggestions);
    }

    private function storeRecommendations(int $versionId, array $recommendations): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO seo_analysis 
             (version_id, analysis_date, recommendations) 
             VALUES (?, NOW(), ?)"
        );
        
        return $stmt->execute([
            $versionId,
            json_encode($recommendations)
        ]);
    }
}
