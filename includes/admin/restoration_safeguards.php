<?php
/**
 * Restoration Safeguards
 * 
 * Provides safety checks and warnings before executing content restorations
 * 
 * Usage:
 * $safeguards = new RestorationSafeguards();
 * $warnings = $safeguards->checkRestoration($versionId, $userId);
 * 
 * @package CMS
 * @subpackage Admin
 */

class RestorationSafeguards {
    private $db;
    private $logger;

    public function __construct() {
        require_once __DIR__ . '/../../core/database.php';
        require_once __DIR__ . '/../audit/restorationlogger.php';

        $this->db = \core\Database::connection();

        $this->logger = new RestorationLogger();
    }
    
    /**
     * Check all restoration safeguards
     * 
     * @param int $versionId Version being restored
     * @param int $userId User attempting restoration
     * @return array Array of warnings and required confirmations
     */
    public function checkRestoration($versionId, $userId) {
        $warnings = [];
        
        // Check for recent changes
        $recentChanges = $this->checkRecentChanges($versionId);
        if ($recentChanges) {
            $warnings['recent_changes'] = $recentChanges;
        }
        
        // Check for multiple version rollback
        $multiVersion = $this->checkMultipleVersionRollback($versionId);
        if ($multiVersion) {
            $warnings['multi_version'] = $multiVersion;
        }
        
        // Check content type protections
        $contentType = $this->checkContentTypeProtections($versionId);
        if ($contentType) {
            $warnings['content_type'] = $contentType;
        }
        
        // Check admin permissions
        $permissions = $this->checkAdminPermissions($userId);
        if ($permissions) {
            $warnings['permissions'] = $permissions;
        }
        
        return $warnings;
    }
    
    /**
     * Check for changes made in last 2 hours
     */
    private function checkRecentChanges($versionId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as recent_count
            FROM content_versions
            WHERE content_id = (
                SELECT content_id FROM content_versions WHERE id = ?
            )
            AND created_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ");
        $stmt->execute([$versionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['recent_count'] > 1) {
            return [
                'message' => 'Warning: ' . $result['recent_count'] . ' recent changes detected in last 2 hours',
                'severity' => 'warning',
                'requires_confirm' => true
            ];
        }
        return false;
    }
    
    /**
     * Check if rolling back multiple versions
     */
    private function checkMultipleVersionRollback($versionId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as versions_behind
            FROM content_versions
            WHERE content_id = (
                SELECT content_id FROM content_versions WHERE id = ?
            )
            AND id > ?
        ");
        $stmt->execute([$versionId, $versionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['versions_behind'] > 3) {
            return [
                'message' => 'Warning: Rolling back ' . $result['versions_behind'] . ' versions',
                'severity' => 'danger',
                'requires_confirm' => true,
                'confirm_text' => 'I understand I am rolling back multiple versions'
            ];
        }
        return false;
    }
    
    /**
     * Check content type specific protections
     */
    private function checkContentTypeProtections($versionId) {
        $stmt = $this->db->prepare("
            SELECT c.content_type
            FROM contents c
            JOIN content_versions cv ON cv.content_id = c.id
            WHERE cv.id = ?
        ");
        $stmt->execute([$versionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['content_type'] === 'system_page') {
            return [
                'message' => 'Warning: Restoring a system page - this may affect site functionality',
                'severity' => 'danger',
                'requires_confirm' => true,
                'confirm_text' => 'I understand the risks of restoring a system page'
            ];
        }
        return false;
    }
    
    /**
     * Check if user has sufficient permissions
     */
    private function checkAdminPermissions($userId) {
        $stmt = $this->db->prepare("
            SELECT r.permissions
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $permissions = json_decode($result['permissions'] ?? '[]', true);
        
        if (!in_array('restore_content', $permissions)) {
            return [
                'message' => 'Error: You do not have permission to restore content',
                'severity' => 'danger',
                'block_action' => true
            ];
        }
        
        return false;
    }
    
    /**
     * Generate HTML for displaying warnings
     */
    public function generateWarningHtml($warnings) {
        $html = '';
        foreach ($warnings as $type => $warning) {
            if ($warning['block_action']) {
                $html .= '
<div class="alert alert-danger">' .
 $warning['message'] . '</div>';
            }
 else {
                $html .= '
<div class="alert alert-' .
 $warning['severity'] . '">' . $warning['message'];
                if ($warning['requires_confirm']) {
                    $html .= '
<div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="confirm_' .
 $type . '" required>
                        <label class="form-check-label" for="confirm_' . $type . '">' . 
                        ($warning['confirm_text'] ?? 'I understand and wish to proceed') . 
                        '</label>
                    </div>';
                }
                $html .= '
</div>';
            }
        }
        return $html;
    }
    
}
