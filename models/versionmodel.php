<?php

require_once __DIR__.'/../db.php';
require_once __DIR__ . '/../includes/tenantvalidator.php';
require_once __DIR__ . '/../includes/approvallogger.php';

class VersionModel {
    private $db;
    private $tenantId;
    protected $approvalStates = [
        'draft',
        'pending_approval',
        'approved',
        'rejected',
        'changes_requested'
    ];

    public function __construct($tenantId) {
        TenantValidator::validate($tenantId);
        $this->tenantId = $tenantId;
        $this->db = \DB::connection();
    }

    public function create($data) {
        $contentId = $data['content_id'];
        $maxVersionStmt = $this->db->prepare("
            SELECT MAX(version_number) as max_version
            FROM content_versions cv
            JOIN contents c ON cv.content_id = c.id
            WHERE cv.content_id = :contentId AND c.tenant_id = :tenantId
        ");
        if (!$maxVersionStmt->execute([":contentId" => $contentId, ":tenantId" => $this->tenantId])) {
            return false;
        }
        $maxVersion = $maxVersionStmt->fetch()['max_version'] ?? 0;
        $newVersion = $maxVersion + 1;

        $insertStmt = $this->db->prepare("
            INSERT INTO content_versions
            (content_id, version_number, data, status, created_by, notes)
            VALUES (:contentId, :version, :data, :status, :createdBy, :notes)
        ");
        return $insertStmt->execute([
            ":contentId" => $contentId,
            ":version" => $newVersion,
            ":data" => $data['content'],
            ":status" => $data['status'] ?? 'draft',
            ":createdBy" => $_SESSION['user_id'] ?? 0,
            ":notes" => $data['notes'] ?? "Auto-saved version"
        ]);
    }

    public function getNextVersionNumber($contentId) {
        $stmt = $this->db->prepare("
            SELECT MAX(version_number) as max_version
            FROM content_versions cv
            JOIN contents c ON cv.content_id = c.id
            WHERE cv.content_id = :contentId AND c.tenant_id = :tenantId
        ");
        if (!$stmt->execute([":contentId" => $contentId, ":tenantId" => $this->tenantId])) {
            return 1;
        }
        $result = $stmt->fetch();
        return ($result['max_version'] ?? 0) + 1;
    }

    public function getVersions($contentId) {
        $stmt = $this->db->prepare("
            SELECT cv.*
            FROM content_versions cv
            JOIN contents c ON cv.content_id = c.id
            WHERE cv.content_id = :contentId AND c.tenant_id = :tenantId
            ORDER BY version_number DESC
        ");
        if (!$stmt->execute([":contentId" => $contentId, ":tenantId" => $this->tenantId])) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function getVersion($contentId, $versionId) {
        $stmt = $this->db->prepare("
            SELECT cv.*
            FROM content_versions cv
            JOIN contents c ON cv.content_id = c.id
            WHERE cv.content_id = :contentId AND cv.id = :versionId AND c.tenant_id = :tenantId
        ");
        if (!$stmt->execute([
            ":contentId" => $contentId,
            ":versionId" => $versionId,
            ":tenantId" => $this->tenantId
        ])) {
            return false;
        }
        return $stmt->fetch();
    }

    public function restoreVersion($contentId, $versionId, $restoredBy) {
        $version = $this->getVersion($contentId, $versionId);
        if (!$version) {
            return false;
        }

        $updateStmt = $this->db->prepare("
            UPDATE contents
            SET body = :data
            WHERE id = :contentId AND tenant_id = :tenantId
        ");
        if (!$updateStmt->execute([
            ":data" => $version['data'],
            ":contentId" => $contentId,
            ":tenantId" => $this->tenantId
        ])) {
            return false;
        }

        return $this->create([
            'content_id' => $contentId,
            'content' => $version['data'],
            'status' => $version['status'],
            'notes' => "Restored from version $versionId"
        ]);
    }

    public function requestApproval($versionId, $requestedBy) {
        $this->updateVersionStatus($versionId, 'pending_approval', $requestedBy);
        ApprovalLogger::log($this->tenantId, $versionId, 'approval_requested', $requestedBy);
    }

    public function approveVersion($versionId, $approvedBy) {
        $this->updateVersionStatus($versionId, 'approved', $approvedBy);
        ApprovalLogger::log($this->tenantId, $versionId, 'approved', $approvedBy);
    }

    public function rejectVersion($versionId, $rejectedBy, $reason = '') {
        $this->updateVersionStatus($versionId, 'rejected', $rejectedBy);
        ApprovalLogger::log($this->tenantId, $versionId, 'rejected', $rejectedBy, $reason);
    }

    public function requestChanges($versionId, $requestedBy, $notes = '') {
        $this->updateVersionStatus($versionId, 'changes_requested', $requestedBy);
        ApprovalLogger::log($this->tenantId, $versionId, 'changes_requested', $requestedBy, $notes);
    }

    public function isApproved($versionId) {
        $version = $this->getVersionById($versionId);
        return $version['status'] === 'approved';
    }

    public function isPendingApproval($versionId) {
        $version = $this->getVersionById($versionId);
        return $version['status'] === 'pending_approval';
    }

    private function updateVersionStatus($versionId, $status, $userId) {
        if (!in_array($status, $this->approvalStates)) {
            throw new Exception("Invalid status transition");
        }

        $query = "UPDATE content_versions SET status = :status, updated_by = :user_id, updated_at = NOW()
                 WHERE id = :versionId AND EXISTS (
                    SELECT 1 FROM contents c
                    WHERE c.id = content_versions.content_id
                    AND c.tenant_id = :tenantId
                 )";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':versionId' => $versionId,
            ':status' => $status,
            ':user_id' => $userId,
            ':tenantId' => $this->tenantId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Version not found for tenant");
        }
    }

    private function getVersionById($versionId) {
        $stmt = $this->db->prepare("
            SELECT cv.*
            FROM content_versions cv
            JOIN contents c ON cv.content_id = c.id
            WHERE cv.id = :versionId AND c.tenant_id = :tenantId
        ");
        if (!$stmt->execute([":versionId" => $versionId, ":tenantId" => $this->tenantId])) {
            throw new Exception("Version not found");
        }
        return $stmt->fetch();
    }
}
