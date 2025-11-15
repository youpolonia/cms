<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Handles version verification and reporting
 */
class VersionVerifier {
    private $db;
    
    public function __construct() {
        $this->db = \core\Database::connection();
    }
    
    /**
     * Generate version verification report
     */
    public function generateReport(): array {
        $deployedVersions = $this->getDeployedVersions();
        $sourceVersions = $this->getSourceVersions();
        
        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_content' => count($sourceVersions),
                'up_to_date' => 0,
                'outdated' => 0,
                'missing' => 0
            ],
            'details' => []
        ];
        
        foreach ($sourceVersions as $contentId => $sourceVersion) {
            $deployedVersion = $deployedVersions[$contentId] ?? null;
            
            if (!$deployedVersion) {
                $status = 'missing';
                $report['summary']['missing']++;
            } elseif ($deployedVersion['version_number'] < $sourceVersion['version_number']) {
                $status = 'outdated';
                $report['summary']['outdated']++;
            } else {
                $status = 'up_to_date';
                $report['summary']['up_to_date']++;
            }
            
            $report['details'][] = [
                'content_id' => $contentId,
                'source_version' => $sourceVersion['version_number'],
                'deployed_version' => $deployedVersion['version_number'] ?? null,
                'status' => $status,
                'last_updated' => $deployedVersion['created_at'] ?? null
            ];
        }
        
        return $report;
    }
    
    /**
     * Store verification report in database
     */
    public function storeReport(array $report): bool {
        $summary = json_encode($report['summary']);
        $details = json_encode($report['details']);
        
        $affectedRows = $this->db->query("
            INSERT INTO version_verifications
            (report_data, generated_at, summary)
            VALUES (?, ?, ?)
        ", [
            $details,
            $report['generated_at'],
            $summary
        ]);
        
        return $affectedRows > 0;
    }
    
    private function getDeployedVersions(): array {
        $stmt = $this->db->query("
            SELECT content_id, MAX(version_number) as version_number,
                   MAX(created_at) as created_at
            FROM versions
            WHERE is_autosave = FALSE
            GROUP BY content_id
        ");
        
        $indexed = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $indexed[$row['content_id']] = $row;
        }
        return $indexed;
    }
    
    private function getSourceVersions(): array {
        $stmt = $this->db->query("
            SELECT content_id, version_number
            FROM version_content
            WHERE is_current = TRUE
        ");
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['content_id']] = $row;
        }
        return $result;
    }
}
