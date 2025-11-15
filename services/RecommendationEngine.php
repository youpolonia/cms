<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Models\RecommendationModel;
use CMS\Includes\Models\UserBehaviorModel;
use CMS\Includes\Models\PersonalizationModel;

class RecommendationEngine {
    private RecommendationModel $recommendationModel;
    private UserBehaviorModel $behaviorModel;
    private PersonalizationModel $personalizationModel;
    private array $config;

    public function __construct(
        RecommendationModel $recommendationModel,
        UserBehaviorModel $behaviorModel,
        PersonalizationModel $personalizationModel,
        array $config = []
    ) {
        $this->recommendationModel = $recommendationModel;
        $this->behaviorModel = $behaviorModel;
        $this->personalizationModel = $personalizationModel;
        $this->config = $config;
    }

    /**
     * Get personalized recommendations for a user
     */
    public function getRecommendations(int $userId, int $limit = 10): array {
        $strategies = $this->config['strategies'] ?? [
            'content_based' => 0.4,
            'collaborative' => 0.4,
            'popular' => 0.2
        ];

        $results = [];
        
        // Get recommendations from each strategy
        if (isset($strategies['content_based'])) {
            $contentBased = $this->getContentBasedRecommendations($userId, (int)($limit * $strategies['content_based']));
            $results = array_merge($results, $contentBased);
        }

        if (isset($strategies['collaborative'])) {
            $collaborative = $this->getCollaborativeRecommendations($userId, (int)($limit * $strategies['collaborative']));
            $results = array_merge($results, $collaborative);
        }

        if (isset($strategies['popular'])) {
            $popular = $this->getPopularRecommendations((int)($limit * $strategies['popular']));
            $results = array_merge($results, $popular);
        }

        // Apply personalization rules
        $results = $this->applyPersonalizationRules($userId, $results);

        // Remove duplicates and re-sort
        $results = $this->deduplicateAndSort($results);

        return array_slice($results, 0, $limit);
    }

    private function getContentBasedRecommendations(int $userId, int $limit): array {
        return $this->recommendationModel->getContentBasedRecommendations($userId, $limit);
    }

    private function getCollaborativeRecommendations(int $userId, int $limit): array {
        return $this->recommendationModel->getCollaborativeRecommendations($userId, $limit);
    }

    private function getPopularRecommendations(int $limit): array {
        return $this->behaviorModel->getMostPopularContent($limit);
    }

    private function applyPersonalizationRules(int $userId, array $items): array {
        $rules = $this->personalizationModel->getRulesForUser($userId);
        
        foreach ($rules as $rule) {
            switch ($rule['type']) {
                case 'boost':
                    $items = $this->applyBoostRule($items, $rule);
                    break;
                case 'filter':
                    $items = $this->applyFilterRule($items, $rule);
                    break;
            }
        }

        return $items;
    }

    private function applyBoostRule(array $items, array $rule): array {
        foreach ($items as &$item) {
            if ($this->matchesRule($item, $rule)) {
                $item['score'] *= (1 + ($rule['boost_value'] / 100));
            }
        }
        return $items;
    }

    private function applyFilterRule(array $items, array $rule): array {
        return array_filter($items, function($item) use ($rule) {
            return !$this->matchesRule($item, $rule);
        });
    }

    private function matchesRule(array $item, array $rule): bool {
        foreach ($rule['conditions'] as $field => $value) {
            if (!isset($item[$field]) || $item[$field] != $value) {
                return false;
            }
        }
        return true;
    }

    private function deduplicateAndSort(array $items): array {
        $unique = [];
        foreach ($items as $item) {
            $contentId = $item['content_id'];
            if (!isset($unique[$contentId])) {
                $unique[$contentId] = $item;
            } else {
                // Keep the highest score if duplicate
                if ($item['score'] > $unique[$contentId]['score']) {
                    $unique[$contentId] = $item;
                }
            }
        }

        // Sort by score descending
        usort($unique, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_values($unique);
    }

    /**
     * Update recommendation models (run periodically via cron)
     */
    public function updateModels(): void {
        $this->recommendationModel->calculateUserSimilarities();
        $this->recommendationModel->updateContentRecommendations();
    }
}
