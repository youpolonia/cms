<?php
declare(strict_types=1);

/**
 * Content Recommendation Engine
 * Provides personalized content suggestions
 */
class RecommendationEngine {
    private static array $contentFeatures = [];
    private static array $userPreferences = [];

    /**
     * Add content item with features
     */
    public static function addContentItem(
        string $itemId,
        array $features
    ): void {
        self::$contentFeatures[$itemId] = $features;
    }

    /**
     * Set user preferences
     */
    public static function setUserPreferences(
        int $userId,
        array $preferences
    ): void {
        self::$userPreferences[$userId] = $preferences;
    }

    /**
     * Get recommendations for user
     */
    public static function getRecommendations(
        int $userId,
        int $limit = 10
    ): array {
        $userPrefs = self::$userPreferences[$userId] ?? [];
        if (empty($userPrefs)) {
            return [];
        }

        $scores = [];
        foreach (self::$contentFeatures as $itemId => $features) {
            $scores[$itemId] = self::calculateSimilarity($userPrefs, $features);
        }

        arsort($scores);
        return array_slice(array_keys($scores), 0, $limit);
    }

    /**
     * Calculate similarity between preferences and features
     */
    private static function calculateSimilarity(
        array $preferences,
        array $features
    ): float {
        $score = 0.0;
        foreach ($preferences as $key => $weight) {
            if (isset($features[$key])) {
                $score += $weight * $features[$key];
            }
        }
        return $score;
    }
}
