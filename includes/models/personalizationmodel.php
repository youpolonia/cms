<?php

namespace Includes\Models;

use Includes\Database\DatabaseConnection;

/**
 * PersonalizationModel handles content recommendations and personalization
 */
class PersonalizationModel {
    /**
     * Recommendation algorithms
     */
    const ALGORITHM_COLLABORATIVE = 'collaborative';
    const ALGORITHM_CONTENT_BASED = 'content_based';
    const ALGORITHM_HYBRID = 'hybrid';
    
    /**
     * Get personalized content recommendations for a user
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of recommendations to return
     * @param string $algorithm Algorithm to use for recommendations
     * @return array Array of recommended content items
     */
    public static function getRecommendations(int $userId, int $limit = 5, string $algorithm = self::ALGORITHM_HYBRID): array {
        switch ($algorithm) {
            case self::ALGORITHM_COLLABORATIVE:
                return self::getCollaborativeRecommendations($userId, $limit);
            case self::ALGORITHM_CONTENT_BASED:
                return self::getContentBasedRecommendations($userId, $limit);
            case self::ALGORITHM_HYBRID:
            default:
                return self::getHybridRecommendations($userId, $limit);
        }
    }
    
    /**
     * Get recommendations based on collaborative filtering
     * (users with similar behavior patterns)
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of recommendations
     * @return array Recommended content items
     */
    private static function getCollaborativeRecommendations(int $userId, int $limit): array {
        // Find similar users based on behavior patterns
        $query = "
            WITH user_vectors AS (
                SELECT 
                    user_id,
                    content_id,
                    COUNT(*) as interaction_count
                FROM 
                    user_behavior_events
                WHERE 
                    event_type IN ('page_view', 'content_click')
                GROUP BY 
                    user_id, content_id
            ),
            similar_users AS (
                SELECT 
                    u2.user_id as similar_user_id,
                    COUNT(*) as common_interactions
                FROM 
                    user_vectors u1
                JOIN 
                    user_vectors u2 ON u1.content_id = u2.content_id AND u1.user_id != u2.user_id
                WHERE 
                    u1.user_id = ?
                GROUP BY 
                    u2.user_id
                ORDER BY 
                    common_interactions DESC
                LIMIT 10
            )
            SELECT 
                c.id,
                c.title,
                c.type,
                c.created_at,
                COUNT(ube.event_id) as popularity
            FROM 
                contents c
            JOIN 
                user_behavior_events ube ON c.id = ube.content_id
            JOIN 
                similar_users su ON ube.user_id = su.similar_user_id
            WHERE 
                c.id NOT IN (
                    SELECT content_id FROM user_behavior_events 
                    WHERE user_id = ? AND event_type IN ('page_view', 'content_click')
                )
            GROUP BY 
                c.id
            ORDER BY 
                popularity DESC
            LIMIT ?
        ";
        
        return DatabaseConnection::fetchAll($query, [$userId, $userId, $limit]);
    }
    
    /**
     * Get recommendations based on content metadata and user preferences
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of recommendations
     * @return array Recommended content items
     */
    private static function getContentBasedRecommendations(int $userId, int $limit): array {
        // Get user's content preferences based on past interactions
        $query = "
            WITH user_preferences AS (
                SELECT 
                    c.type,
                    JSON_EXTRACT(c.metadata, '$.tags') as tags,
                    JSON_EXTRACT(c.metadata, '$.category') as category,
                    COUNT(*) as interaction_count
                FROM 
                    user_behavior_events ube
                JOIN 
                    contents c ON ube.content_id = c.id
                WHERE 
                    ube.user_id = ?
                    AND ube.event_type IN ('page_view', 'content_click')
                GROUP BY 
                    c.type, tags, category
                ORDER BY 
                    interaction_count DESC
                LIMIT 5
            )
            SELECT 
                c.id,
                c.title,
                c.type,
                c.created_at,
                (
                    CASE 
                        WHEN c.type IN (SELECT type FROM user_preferences) THEN 3
                        ELSE 0
                    END +
                    CASE 
                        WHEN JSON_EXTRACT(c.metadata, '$.category') IN (SELECT category FROM user_preferences) THEN 2
                        ELSE 0
                    END +
                    CASE 
                        WHEN JSON_CONTAINS(JSON_EXTRACT(c.metadata, '$.tags'), (SELECT JSON_ARRAYAGG(tags) FROM user_preferences)) THEN 1
                        ELSE 0
                    END
                ) as relevance_score
            FROM 
                contents c
            WHERE 
                c.id NOT IN (
                    SELECT content_id FROM user_behavior_events 
                    WHERE user_id = ? AND event_type IN ('page_view', 'content_click')
                )
            ORDER BY 
                relevance_score DESC, c.created_at DESC
            LIMIT ?
        ";
        
        return DatabaseConnection::fetchAll($query, [$userId, $userId, $limit]);
    }
    
    /**
     * Get recommendations using a hybrid approach combining collaborative and content-based filtering
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of recommendations
     * @return array Recommended content items
     */
    private static function getHybridRecommendations(int $userId, int $limit): array {
        // If user has no history, return popular content
        if (!self::hasUserHistory($userId)) {
            return self::getPopularContent($limit);
        }
        
        // Get recommendations from both algorithms
        $collaborative = self::getCollaborativeRecommendations($userId, $limit);
        $contentBased = self::getContentBasedRecommendations($userId, $limit);
        
        // Merge and deduplicate recommendations
        $merged = array_merge($collaborative, $contentBased);
        $unique = [];
        
        foreach ($merged as $item) {
            if (!isset($unique[$item['id']])) {
                $unique[$item['id']] = $item;
            }
        }
        
        // Sort by relevance and limit results
        usort($unique, function($a, $b) {
            return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
        });
        
        return array_slice(array_values($unique), 0, $limit);
    }
    
    /**
     * Check if a user has interaction history
     *
     * @param int $userId User ID
     * @return bool True if user has history, false otherwise
     */
    private static function hasUserHistory(int $userId): bool {
        $query = "
            SELECT COUNT(*) as count
            FROM user_behavior_events
            WHERE user_id = ?
        ";
        
        $result = DatabaseConnection::fetchOne($query, [$userId]);
        return $result['count'] > 0;
    }
    
    /**
     * Get popular content for new users or fallback
     *
     * @param int $limit Maximum number of items to return
     * @return array Popular content items
     */
    private static function getPopularContent(int $limit): array {
        $query = "
            SELECT 
                c.id,
                c.title,
                c.type,
                c.created_at,
                COUNT(ube.event_id) as popularity
            FROM 
                contents c
            JOIN 
                user_behavior_events ube ON c.id = ube.content_id
            WHERE 
                ube.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY 
                c.id
            ORDER BY 
                popularity DESC
            LIMIT ?
        ";
        
        return DatabaseConnection::fetchAll($query, [$limit]);
    }
    
    /**
     * Record user feedback on recommendations
     *
     * @param int $userId User ID
     * @param int $contentId Content ID
     * @param string $feedbackType Type of feedback (click, dismiss, etc.)
     * @return bool Success status
     */
    public static function recordRecommendationFeedback(int $userId, int $contentId, string $feedbackType): bool {
        $query = "
            INSERT INTO recommendation_feedback
            (user_id, content_id, feedback_type, created_at)
            VALUES (?, ?, ?, NOW())
        ";
        
        return DatabaseConnection::execute($query, [$userId, $contentId, $feedbackType]);
    }
}
