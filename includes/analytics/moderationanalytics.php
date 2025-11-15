<?php

class ModerationAnalytics {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getModerationStats($timePeriod = '30d') {
        $dateRange = $this->getDateRange($timePeriod);
        
        return [
            'total_content' => $this->getTotalContentCount($dateRange),
            'auto_approved' => $this->getAutoApprovedCount($dateRange),
            'human_reviewed' => $this->getHumanReviewedCount($dateRange),
            'rejected' => $this->getRejectedCount($dateRange),
            'average_time' => $this->getAverageReviewTime($dateRange),
            'flag_distribution' => $this->getFlagDistribution($dateRange),
            'moderator_activity' => $this->getModeratorActivity($dateRange)
        ];
    }
    
    public function getFlagTrends($timePeriod = '30d') {
        $dateRange = $this->getDateRange($timePeriod);
        $stmt = $this->db->prepare(
            "SELECT 
                DATE(created_at) as day,
                flag_type,
                COUNT(*) as count
             FROM content_flags
             WHERE created_at BETWEEN ? AND ?
             GROUP BY day, flag_type
             ORDER BY day ASC"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getModeratorPerformance($timePeriod = '30d') {
        $dateRange = $this->getDateRange($timePeriod);
        $stmt = $this->db->prepare(
            "SELECT 
                u.username,
                COUNT(ma.id) as actions,
                SUM(CASE WHEN ma.action = 'approve' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN ma.action = 'reject' THEN 1 ELSE 0 END) as rejected,
                AVG(TIMESTAMPDIFF(MINUTE, mq.created_at, ma.created_at)) as avg_time
             FROM moderation_actions ma
             JOIN users u ON ma.moderator_id = u.id
             JOIN moderation_queue mq ON ma.content_id = mq.content_id
             WHERE ma.created_at BETWEEN ? AND ?
             GROUP BY ma.moderator_id
             ORDER BY actions DESC"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getTotalContentCount($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contents 
             WHERE created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchColumn();
    }
    
    private function getAutoApprovedCount($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contents 
             WHERE moderation_status = 'auto_approved'
             AND created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchColumn();
    }
    
    private function getHumanReviewedCount($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM moderation_actions
             WHERE created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchColumn();
    }
    
    private function getRejectedCount($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contents 
             WHERE moderation_status = 'rejected'
             AND created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchColumn();
    }
    
    private function getAverageReviewTime($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, mq.created_at, ma.created_at))
             FROM moderation_actions ma
             JOIN moderation_queue mq ON ma.content_id = mq.content_id
             WHERE ma.created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return round($stmt->fetchColumn(), 1);
    }
    
    private function getFlagDistribution($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT flag_type, COUNT(*) as count
             FROM content_flags
             WHERE created_at BETWEEN ? AND ?
             GROUP BY flag_type
             ORDER BY count DESC"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getModeratorActivity($dateRange) {
        $stmt = $this->db->prepare(
            "SELECT 
                u.username,
                COUNT(ma.id) as actions
             FROM moderation_actions ma
             JOIN users u ON ma.moderator_id = u.id
             WHERE ma.created_at BETWEEN ? AND ?
             GROUP BY ma.moderator_id
             ORDER BY actions DESC"
        );
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getDateRange($timePeriod) {
        $end = date('Y-m-d H:i:s');
        
        switch ($timePeriod) {
            case '7d':
                $start = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30d':
                $start = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90d':
                $start = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            default:
                $start = date('Y-m-d H:i:s', strtotime('-30 days'));
        }
        
        return ['start' => $start, 'end' => $end];
    }
}
