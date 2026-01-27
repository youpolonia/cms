<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Models\PersonalizationModel;
use CMS\Includes\Models\UserBehaviorModel;
use CMS\Includes\Models\UserPreferencesModel;

class PersonalizationEngine {
    private PersonalizationModel $personalizationModel;
    private UserBehaviorModel $behaviorModel;
    private UserPreferencesModel $preferencesModel;
    private array $config;

    public function __construct(
        PersonalizationModel $personalizationModel,
        UserBehaviorModel $behaviorModel,
        UserPreferencesModel $preferencesModel,
        array $config = []
    ) {
        $this->personalizationModel = $personalizationModel;
        $this->behaviorModel = $behaviorModel;
        $this->preferencesModel = $preferencesModel;
        $this->config = $config;
    }

    /**
     * Get personalized content for a user
     */
    public function getPersonalizedContent(int $userId, string $context, int $limit = 10): array {
        $rules = $this->getApplicableRules($userId, $context);
        $content = [];

        foreach ($rules as $rule) {
            $ruleContent = $this->applyRule($userId, $rule);
            $content = array_merge($content, $ruleContent);
        }

        // Sort by combined score and apply limit
        usort($content, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($content, 0, $limit);
    }

    private function getApplicableRules(int $userId, string $context): array {
        $globalRules = $this->personalizationModel->getGlobalRules($context);
        $userRules = $this->personalizationModel->getUserRules($userId, $context);
        
        return array_merge($globalRules, $userRules);
    }

    private function applyRule(int $userId, array $rule): array {
        $content = [];
        
        switch ($rule['type']) {
            case 'content_based':
                $content = $this->applyContentBasedRule($userId, $rule);
                break;
            case 'behavioral':
                $content = $this->applyBehavioralRule($userId, $rule);
                break;
            case 'preference':
                $content = $this->applyPreferenceRule($userId, $rule);
                break;
            case 'ai':
                $content = $this->applyAIRule($userId, $rule);
                break;
        }

        return $content;
    }

    private function applyContentBasedRule(int $userId, array $rule): array {
        $similarContent = $this->personalizationModel->getSimilarContent(
            $rule['content_id'],
            $rule['similarity_threshold'] ?? 0.7
        );

        return array_map(function($item) use ($rule) {
            return [
                'content_id' => $item['id'],
                'score' => $item['similarity'] * ($rule['weight'] ?? 1.0),
                'source' => 'content_based',
                'rule_id' => $rule['id']
            ];
        }, $similarContent);
    }

    private function applyBehavioralRule(int $userId, array $rule): array {
        $behaviorData = $this->behaviorModel->getUserBehavior($userId, $rule['behavior_type']);
        $content = [];

        foreach ($behaviorData as $item) {
            $content[] = [
                'content_id' => $item['content_id'],
                'score' => $item['count'] * ($rule['weight'] ?? 1.0),
                'source' => 'behavioral',
                'rule_id' => $rule['id']
            ];
        }

        return $content;
    }

    private function applyPreferenceRule(int $userId, array $rule): array {
        $preferences = $this->preferencesModel->getUserPreferences($userId);
        $content = [];

        if (isset($preferences[$rule['preference_key']])) {
            $prefValue = $preferences[$rule['preference_key']];
            $matchingContent = $this->personalizationModel->getContentByPreference(
                $rule['preference_key'],
                $prefValue
            );

            foreach ($matchingContent as $item) {
                $content[] = [
                    'content_id' => $item['id'],
                    'score' => $rule['weight'] ?? 1.0,
                    'source' => 'preference',
                    'rule_id' => $rule['id']
                ];
            }
        }

        return $content;
    }

    private function applyAIRule(int $userId, array $rule): array {
        $userProfile = $this->personalizationModel->getUserProfile($userId);
        $context = $rule['context'] ?? 'default';

        // Call AI service to get recommendations
        $aiResponse = $this->callAIService(
            $userProfile,
            $context,
            $rule['ai_model'] ?? 'default'
        );

        return array_map(function($item) use ($rule) {
            return [
                'content_id' => $item['content_id'],
                'score' => $item['score'] * ($rule['weight'] ?? 1.0),
                'source' => 'ai',
                'rule_id' => $rule['id']
            ];
        }, $aiResponse['recommendations'] ?? []);
    }

    private function callAIService(array $userProfile, string $context, string $model): array {
        // Implementation would call actual AI service via REST API
        // This is a mock implementation for shared hosting compatibility
        return [
            'recommendations' => [],
            'model' => $model,
            'context' => $context
        ];
    }

    /**
     * Create a new personalization rule
     */
    public function createRule(array $ruleData): int {
        return $this->personalizationModel->createRule($ruleData);
    }

    /**
     * Update an existing rule
     */
    public function updateRule(int $ruleId, array $ruleData): bool {
        return $this->personalizationModel->updateRule($ruleId, $ruleData);
    }

    /**
     * Evaluate all rules for a user and context
     */
    public function evaluateRules(int $userId, string $context): array {
        $rules = $this->getApplicableRules($userId, $context);
        $results = [];

        foreach ($rules as $rule) {
            $results[] = [
                'rule_id' => $rule['id'],
                'type' => $rule['type'],
                'applied' => count($this->applyRule($userId, $rule)) > 0,
                'weight' => $rule['weight']
            ];
        }

        return $results;
    }
}
