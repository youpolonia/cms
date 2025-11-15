<?php

namespace Includes\Controllers\Api;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Models\PersonalizationModel;
use Includes\Models\UserBehaviorModel;
use Includes\Session\SessionManager;

/**
 * RecommendationController handles API endpoints for content recommendations
 */
class RecommendationController {
    /**
     * Get recommendations for the current user
     *
     * @param Request $request
     * @return Response
     */
    public function getRecommendations(Request $request): Response {
        $session = SessionManager::getInstance();
        $userId = $session->get('user_id') ?? 0;
        
        $limit = (int)($request->getQueryParams()['limit'] ?? 5);
        $algorithm = $request->getQueryParams()['algorithm'] ?? PersonalizationModel::ALGORITHM_HYBRID;
        
        // Validate algorithm
        $validAlgorithms = [
            PersonalizationModel::ALGORITHM_COLLABORATIVE,
            PersonalizationModel::ALGORITHM_CONTENT_BASED,
            PersonalizationModel::ALGORITHM_HYBRID
        ];
        
        if (!in_array($algorithm, $validAlgorithms)) {
            $algorithm = PersonalizationModel::ALGORITHM_HYBRID;
        }
        
        // Get recommendations
        $recommendations = PersonalizationModel::getRecommendations($userId, $limit, $algorithm);
        
        // Track recommendation impression
        $this->trackRecommendationImpression($userId, $recommendations);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'data' => $recommendations
        ]));
    }
    
    /**
     * Record feedback on a recommendation
     *
     * @param Request $request
     * @return Response
     */
    public function recordFeedback(Request $request): Response {
        $session = SessionManager::getInstance();
        $userId = $session->get('user_id') ?? 0;
        
        $body = json_decode($request->getBody(), true);
        
        if (!isset($body['content_id']) || !isset($body['feedback_type'])) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing required parameters'
            ]));
        }
        
        $contentId = (int)$body['content_id'];
        $feedbackType = $body['feedback_type'];
        
        // Record feedback
        $success = PersonalizationModel::recordRecommendationFeedback($userId, $contentId, $feedbackType);
        
        // If it's a click, also log as behavior event
        if ($feedbackType === 'click' && $success) {
            UserBehaviorModel::logEvent([
                'user_id' => $userId,
                'session_id' => $session->getId(),
                'event_type' => 'content_click',
                'content_id' => $contentId,
                'metadata' => [
                    'source' => 'recommendation',
                    'timestamp' => time()
                ]
            ]);
        }
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => $success
        ]));
    }
    
    /**
     * Get personalized recommendations based on real-time context
     *
     * @param Request $request
     * @return Response
     */
    public function getContextualRecommendations(Request $request): Response {
        $session = SessionManager::getInstance();
        $userId = $session->get('user_id') ?? 0;
        
        $body = json_decode($request->getBody(), true);
        
        $currentContentId = $body['current_content_id'] ?? null;
        $context = $body['context'] ?? [];
        $limit = (int)($body['limit'] ?? 3);
        
        // Get recommendations based on current content and context
        $recommendations = $this->getRecommendationsForContext($userId, $currentContentId, $context, $limit);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'data' => $recommendations
        ]));
    }
    
    /**
     * Get recommendations for A/B testing
     *
     * @param Request $request
     * @return Response
     */
    public function getABTestRecommendations(Request $request): Response {
        $session = SessionManager::getInstance();
        $userId = $session->get('user_id') ?? 0;
        
        $testId = $request->getQueryParams()['test_id'] ?? null;
        $limit = (int)($request->getQueryParams()['limit'] ?? 5);
        
        if (!$testId) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Missing test_id parameter'
            ]));
        }
        
        // Determine which variant to show (A or B)
        $variant = $this->getABTestVariant($userId, $testId);
        
        // Get recommendations based on variant
        $algorithm = $variant === 'A' 
            ? PersonalizationModel::ALGORITHM_COLLABORATIVE 
            : PersonalizationModel::ALGORITHM_CONTENT_BASED;
        
        $recommendations = PersonalizationModel::getRecommendations($userId, $limit, $algorithm);
        
        // Track impression with test metadata
        $this->trackRecommendationImpression($userId, $recommendations, [
            'test_id' => $testId,
            'variant' => $variant
        ]);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'data' => $recommendations,
            'test' => [
                'id' => $testId,
                'variant' => $variant
            ]
        ]));
    }
    
    /**
     * Get recommendations based on context
     *
     * @param int $userId User ID
     * @param int|null $currentContentId Current content being viewed
     * @param array $context Additional context data
     * @param int $limit Maximum number of recommendations
     * @return array Recommendations
     */
    private function getRecommendationsForContext(int $userId, ?int $currentContentId, array $context, int $limit): array {
        // This would be a more sophisticated algorithm in a real implementation
        // For now, we'll just use the hybrid approach
        return PersonalizationModel::getRecommendations($userId, $limit);
    }
    
    /**
     * Determine which variant to show for an A/B test
     *
     * @param int $userId User ID
     * @param string $testId Test identifier
     * @return string 'A' or 'B'
     */
    private function getABTestVariant(int $userId, string $testId): string {
        // Consistently assign users to variants based on user ID and test ID
        $hash = crc32($userId . $testId);
        return ($hash % 2 === 0) ? 'A' : 'B';
    }
    
    /**
     * Track recommendation impressions for analytics
     *
     * @param int $userId User ID
     * @param array $recommendations Recommendations shown
     * @param array $metadata Additional metadata
     * @return void
     */
    private function trackRecommendationImpression(int $userId, array $recommendations, array $metadata = []): void {
        $session = SessionManager::getInstance();
        
        // Extract content IDs
        $contentIds = array_map(function($item) {
            return $item['id'];
        }, $recommendations);
        
        // Log impression event
        UserBehaviorModel::logEvent([
            'user_id' => $userId,
            'session_id' => $session->getId(),
            'event_type' => 'impression',
            'content_id' => null,
            'metadata' => array_merge([
                'content_ids' => $contentIds,
                'count' => count($contentIds),
                'timestamp' => time()
            ], $metadata)
        ]);
    }
}
