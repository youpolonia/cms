<?php
require_once __DIR__ . '/../../config.php';

/**
 * Recommendation Analytics Model
 * Handles tracking and reporting of recommendation performance metrics
 */
class RecommendationAnalytics {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    /**
     * Record an impression for a recommendation
     */
    public function recordImpression($recommendationId, $contentId, $userId = null, $sessionId = null, $ip = null, $userAgent = null) {
        $stmt = $this->db->prepare("
            INSERT INTO recommendation_analytics 
            (recommendation_id, content_id, user_id, session_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE impression_count = impression_count + 1
        ");
        return $stmt->execute([$recommendationId, $contentId, $userId, $sessionId, $ip, $userAgent]);
    }

    /**
     * Record a click for a recommendation
     */
    public function recordClick($recommendationId) {
        $stmt = $this->db->prepare("
            UPDATE recommendation_analytics 
            SET click_count = click_count + 1
            WHERE recommendation_id = ?
        ");
        return $stmt->execute([$recommendationId]);
    }

    /**
     * Record a dismissal for a recommendation
     */
    public function recordDismissal($recommendationId) {
        $stmt = $this->db->prepare("
            UPDATE recommendation_analytics 
            SET dismissal_count = dismissal_count + 1
            WHERE recommendation_id = ?
        ");
        return $stmt->execute([$recommendationId]);
    }

    /**
     * Record feedback rating for a recommendation
     */
    public function recordFeedback($recommendationId, $rating) {
        $stmt = $this->db->prepare("
            UPDATE recommendation_analytics 
            SET feedback_rating = ?
            WHERE recommendation_id = ?
        ");
        return $stmt->execute([$rating, $recommendationId]);
    }

    /**
     * Get daily aggregated metrics
     */
    public function getDailyMetrics($days = 7) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(created_at) AS date,
                SUM(impression_count) AS impressions,
                SUM(click_count) AS clicks,
                SUM(dismissal_count) AS dismissals,
                AVG(feedback_rating) AS avg_rating,
                COUNT(DISTINCT recommendation_id) AS unique_recommendations,
                COUNT(DISTINCT user_id) AS unique_users
            FROM recommendation_analytics
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Detect anomalies in recommendation performance
     */
    public function detectAnomalies($threshold = 0.3) {
        // Get baseline metrics from past 7 days
        $baseline = $this->getDailyMetrics(7);
        if (count($baseline) < 2) return []; // Not enough data
        
        // Calculate average click-through rate
        $totalImpressions = array_sum(array_column($baseline, 'impressions'));
        $totalClicks = array_sum(array_column($baseline, 'clicks'));
        $avgCTR = $totalClicks / max(1, $totalImpressions);
        
        // Get today's metrics
        $stmt = $this->db->prepare("
            SELECT 
                SUM(impression_count) AS impressions,
                SUM(click_count) AS clicks
            FROM recommendation_analytics
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $today = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check for anomalies
        $anomalies = [];
        if ($today['impressions'] > 0) {
            $todayCTR = $today['clicks'] / $today['impressions'];
            $ctrDiff = abs($todayCTR - $avgCTR);
            
            if ($ctrDiff > $threshold * $avgCTR) {
                $anomalies[] = [
                    'metric' => 'click_through_rate',
                    'expected' => round($avgCTR * 100, 2) . '%',
                    'actual' => round($todayCTR * 100, 2) . '%',
                    'deviation' => round(($ctrDiff / $avgCTR) * 100) . '%'
                ];
            }
        }
        
        return $anomalies;
    }

    /**
     * Generate performance report
     */
    public function generateReport($days = 7) {
        $metrics = $this->getDailyMetrics($days);
        $anomalies = $this->detectAnomalies();
        
        return [
            'time_period' => "$days days",
            'total_impressions' => array_sum(array_column($metrics, 'impressions')),
            'total_clicks' => array_sum(array_column($metrics, 'clicks')),
            'total_dismissals' => array_sum(array_column($metrics, 'dismissals')),
            'average_rating' => round(array_sum(array_column($metrics, 'avg_rating')) / count($metrics), 2),
            'daily_metrics' => $metrics,
            'anomalies' => $anomalies
        ];
    }
}
