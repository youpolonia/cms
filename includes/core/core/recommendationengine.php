<?php
require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../models/RecommendationModel.php';

class RecommendationEngine {
    private $db;
    private $model;

    public function __construct() {
        $this->db = \core\Database::connection();
        $this->model = new RecommendationModel($this->db);
    }

    public function getCollaborativeRecommendations($userId, $limit = 10) {
        return $this->model->getCollaborativeRecommendations($userId, $limit);
    }

    public function getContentBasedRecommendations($userId, $limit = 10) {
        return $this->model->getContentBasedRecommendations($userId, $limit);
    }

    public function getHybridRecommendations($userId, $limit = 10) {
        $collaborative = $this->getCollaborativeRecommendations($userId, $limit);
        $contentBased = $this->getContentBasedRecommendations($userId, $limit);
        
        // Simple hybrid approach - combine and dedupe
        $combined = array_merge($collaborative, $contentBased);
        $uniqueIds = [];
        $result = [];
        
        foreach ($combined as $item) {
            if (!in_array($item['content_id'], $uniqueIds)) {
                $uniqueIds[] = $item['content_id'];
                $result[] = $item;
                if (count($result) >= $limit) break;
            }
        }
        
        return $result;
    }

    public function calculateUserSimilarities() {
        return $this->model->calculateUserSimilarities();
    }

    public function updateContentRecommendations() {
        return $this->model->updateContentRecommendations();
    }
}
