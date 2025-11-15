<?php
declare(strict_types=1);

namespace Includes\Personalization;

class ContentTargeting {
    private array $config;
    private RuleEvaluator $ruleEvaluator;

    public function __construct(array $config) {
        $this->config = $config;
        $this->ruleEvaluator = new RuleEvaluator($config['rules'] ?? []);
    }

    public function selectContent(array $userContext, array $contentOptions): array {
        $scoredContent = [];
        
        foreach ($contentOptions as $contentId => $content) {
            $evaluation = $this->ruleEvaluator->evaluate(
                $userContext,
                $content['context'] ?? []
            );

            $scoredContent[$contentId] = [
                'score' => $evaluation['score'],
                'content' => $content,
                'matched_rules' => $evaluation['matched_rules']
            ];
        }

        // Sort by score descending
        uasort($scoredContent, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Apply threshold filtering
        $threshold = $this->config['score_threshold'] ?? 0;
        $filtered = array_filter($scoredContent, fn($item) => $item['score'] >= $threshold);

        // Apply diversity rules if configured
        if (!empty($this->config['diversity_rules'])) {
            $filtered = $this->applyDiversityRules($filtered, $userContext);
        }

        return [
            'selected' => array_slice($filtered, 0, $this->config['max_results'] ?? 3, true),
            'all_options' => $scoredContent
        ];
    }

    private function applyDiversityRules(array $content, array $userContext): array {
        $result = [];
        $usedCategories = [];

        foreach ($content as $contentId => $item) {
            $category = $item['content']['category'] ?? null;
            
            if ($category && in_array($category, $usedCategories)) {
                continue;
            }

            $result[$contentId] = $item;
            $usedCategories[] = $category;
        }

        return $result;
    }

    public function trackEngagement(string $contentId, array $userContext): void {
        // TODO: Implement engagement tracking
    }
}
