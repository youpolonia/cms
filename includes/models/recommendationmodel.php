<?php
require_once __DIR__ . '/../core/database.php';

class RecommendationModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getCollaborativeRecommendations($userId, $limit = 10) {
        // Get similar users first
        $similarUsers = $this->getSimilarUsers($userId, 5);
        $userIds = array_column($similarUsers, 'user_id');
        $userIds[] = $userId; // Include current user's interactions

        // Get content interactions from similar users
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "SELECT 
                ci.content_id, 
                COUNT(*) as interaction_count,
                AVG(ci.interaction_value) as avg_score
            FROM user_content_interactions ci
            WHERE ci.user_id IN ($placeholders)
            GROUP BY ci.content_id
            ORDER BY interaction_count DESC, avg_score DESC
            LIMIT ?";

        $params = array_merge($userIds, [$limit]);
        return $this->db->fetchAll($sql, $params);
    }

    public function getContentBasedRecommendations($userId, $limit = 10) {
        // Get user's past interactions to find preferred content features
        $sql = "SELECT 
                c.category, 
                c.tags,
                COUNT(*) as interaction_count
            FROM user_content_interactions ci
            JOIN contents c ON ci.content_id = c.id
            WHERE ci.user_id = ?
            GROUP BY c.category, c.tags
            ORDER BY interaction_count DESC
            LIMIT 3";

        $preferredFeatures = $this->db->fetchAll($sql, [$userId]);

        // Find similar content based on features
        $featureConditions = [];
        $params = [];
        foreach ($preferredFeatures as $feature) {
            $featureConditions[] = "(c.category = ? OR c.tags LIKE ?)";
            $params[] = $feature['category'];
            $params[] = '%'.$feature['tags'].'%';
        }

        $sql = "SELECT 
                c.id as content_id,
                COUNT(*) as match_score
            FROM contents c
            WHERE ".implode(' OR ', $featureConditions)."
            GROUP BY c.id
            ORDER BY match_score DESC
            LIMIT ?";

        $params[] = $limit;
        return $this->db->fetchAll($sql, $params);
    }

    public function calculateUserSimilarities() {
        // Calculate Jaccard similarity between users based on interactions
        $sql = "INSERT INTO user_similarity (user_id1, user_id2, similarity_score)
                SELECT 
                    a.user_id as user_id1,
                    b.user_id as user_id2,
                    COUNT(DISTINCT a.content_id) / 
                    (SELECT COUNT(DISTINCT content_id) FROM user_content_interactions 
                     WHERE user_id IN (a.user_id, b.user_id)) as similarity_score
                FROM user_content_interactions a
                JOIN user_content_interactions b ON a.content_id = b.content_id 
                    AND a.user_id < b.user_id
                GROUP BY a.user_id, b.user_id
                HAVING similarity_score > 0.2
                ON DUPLICATE KEY UPDATE similarity_score = VALUES(similarity_score)";

        return $this->db->query($sql);
    }

    public function updateContentRecommendations() {
        // Update content-to-content similarity based on co-interactions
        $sql = "INSERT INTO content_similarity (content_id1, content_id2, similarity_score)
                SELECT 
                    a.content_id as content_id1,
                    b.content_id as content_id2,
                    COUNT(DISTINCT a.user_id) / 
                    (SELECT COUNT(DISTINCT user_id) FROM user_content_interactions 
                     WHERE content_id IN (a.content_id, b.content_id)) as similarity_score
                FROM user_content_interactions a
                JOIN user_content_interactions b ON a.user_id = b.user_id 
                    AND a.content_id < b.content_id
                GROUP BY a.content_id, b.content_id
                HAVING similarity_score > 0.1
                ON DUPLICATE KEY UPDATE similarity_score = VALUES(similarity_score)";

        return $this->db->query($sql);
    }

    private function getSimilarUsers($userId, $limit = 5) {
        $sql = "SELECT user_id2 as user_id, similarity_score 
                FROM user_similarity 
                WHERE user_id1 = ?
                UNION
                SELECT user_id1 as user_id, similarity_score 
                FROM user_similarity 
                WHERE user_id2 = ?
                ORDER BY similarity_score DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $userId, $limit]);
    }
}
