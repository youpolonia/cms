<?php

class ModerationService {
    private static $instance;
    private $db;
    private $config;
    
    private function __construct($db) {
        $this->db = $db;
        $this->config = require_once __DIR__ . '/../../config/content_moderation.php';
    }
    
    public static function getInstance($db) {
        if (!self::$instance) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }
    
    public function scanContent($contentId) {
        // Get content from database
        $content = $this->getContent($contentId);
        if (!$content) {
            return false;
        }
        
        // Run automated scanning
        $flags = $this->runAutomatedScan($content);
        
        // Process flags and update moderation status
        return $this->processFlags($contentId, $flags);
    }
    
    private function getContent($contentId) {
        $stmt = $this->db->prepare("SELECT * FROM contents WHERE id = ?");
        $stmt->execute([$contentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function runAutomatedScan($content) {
        $flags = [];
        
        // Check against priority rules
        foreach ($this->config['priority_rules'] as $rule) {
            foreach ($this->config['fields'] as $field) {
                if (isset($content[$field])) {
                    if (preg_match($rule['pattern'], $content[$field])) {
                        $flags[] = [
                            'type' => $rule['flag'],
                            'severity' => $this->getSeverity($rule['flag']),
                            'confidence' => $rule['weight'] * 0.2 // Convert to 0-1 scale
                        ];
                    }
                }
            }
        }
        
        return $flags;
    }
    
    private function getSeverity($flag) {
        foreach ($this->config['flag_severity'] as $severity => $flags) {
            if (in_array($flag, $flags)) {
                return $severity;
            }
        }
        return 'low';
    }
    
    private function processFlags($contentId, $flags) {
        if (empty($flags)) {
            $this->markAsAutoApproved($contentId);
            return true;
        }
        
        // Save flags to database
        $this->saveFlags($contentId, $flags);
        
        // Add to moderation queue if needed
        return $this->addToModerationQueue($contentId, $flags);
    }
    
    private function markAsAutoApproved($contentId) {
        $stmt = $this->db->prepare(
            "UPDATE contents SET moderation_status = 'auto_approved' WHERE id = ?"
        );
        return $stmt->execute([$contentId]);
    }
    
    private function saveFlags($contentId, $flags) {
        $stmt = $this->db->prepare(
            "INSERT INTO content_flags 
            (content_id, flag_type, severity, confidence_score, status) 
            VALUES (?, ?, ?, ?, 'pending')"
        );
        
        foreach ($flags as $flag) {
            $stmt->execute([
                $contentId,
                $flag['type'],
                $flag['severity'],
                $flag['confidence']
            ]);
        }
    }
    
    private function addToModerationQueue($contentId, $flags) {
        // Get highest priority flag
        $highestSeverity = $this->getHighestSeverity($flags);
        $priority = $this->config['automated_actions'][$highestSeverity]['priority'];
        
        $stmt = $this->db->prepare(
            "INSERT INTO moderation_queue 
            (content_id, priority, status) 
            VALUES (?, ?, 'pending')"
        );
        
        return $stmt->execute([$contentId, $priority]);
    }
    
    private function getHighestSeverity($flags) {
        $severities = ['high', 'medium', 'low'];
        foreach ($severities as $severity) {
            foreach ($flags as $flag) {
                if ($flag['severity'] === $severity) {
                    return $severity;
                }
            }
        }
        return 'low';
    }
}
