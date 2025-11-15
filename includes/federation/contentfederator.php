<?php

namespace CMS\Federation;

use CMS\Core\TenantRepository;
use PDO;

class ContentFederator
{
    private $db;
    private $tenantRepository;

    public function __construct(PDO $db, TenantRepository $tenantRepository)
    {
        $this->db = $db;
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * Share content between tenants
     * @param string $contentId
     * @param string $sourceTenantId
     * @param array $targetTenantIds
     * @param array $permissions
     * @return bool
     */
    public function shareContent(string $contentId, string $sourceTenantId, array $targetTenantIds, array $permissions): bool
    {
        try {
            $this->db->beginTransaction();

            // Validate tenants exist
            $sourceTenant = $this->tenantRepository->find($sourceTenantId);
            if (!$sourceTenant) {
                throw new \RuntimeException("Source tenant not found");
            }

            foreach ($targetTenantIds as $targetTenantId) {
                $targetTenant = $this->tenantRepository->find($targetTenantId);
                if (!$targetTenant) {
                    throw new \RuntimeException("Target tenant $targetTenantId not found");
                }

                // Insert federation record
                $stmt = $this->db->prepare("
                    INSERT INTO content_federation 
                    (content_id, source_tenant_id, target_tenant_id, permissions, created_at) 
                    VALUES (:content_id, :source_tenant_id, :target_tenant_id, :permissions, NOW())
                ");
                $stmt->bindParam(':content_id', $contentId);
                $stmt->bindParam(':source_tenant_id', $sourceTenantId);
                $stmt->bindParam(':target_tenant_id', $targetTenantId);
                $stmt->bindValue(':permissions', json_encode($permissions));
                $stmt->execute();

                $this->logAudit('content_federation', $sourceTenantId, $targetTenantId, $contentId);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Content federation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Synchronize content versions between tenants
     * @param string $contentId
     * @param string $versionHash
     * @param array $tenantIds
     * @return bool
     */
    public function syncVersions(string $contentId, string $versionHash, array $tenantIds): bool
    {
        try {
            $this->db->beginTransaction();

            foreach ($tenantIds as $tenantId) {
                $stmt = $this->db->prepare("
                    UPDATE content_federation 
                    SET version_hash = :version_hash, updated_at = NOW() 
                    WHERE content_id = :content_id AND target_tenant_id = :tenant_id
                ");
                $stmt->bindParam(':version_hash', $versionHash);
                $stmt->bindParam(':content_id', $contentId);
                $stmt->bindParam(':tenant_id', $tenantId);
                $stmt->execute();

                $this->logAudit('version_sync', $tenantId, null, $contentId);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Version sync failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve content conflicts between versions
     * @param string $contentId
     * @param string $chosenVersionHash
     * @param string $resolutionStrategy
     * @return bool
     */
    public function resolveConflicts(string $contentId, string $chosenVersionHash, string $resolutionStrategy): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE content_federation 
                SET version_hash = :version_hash, 
                    conflict_resolved = 1,
                    resolution_strategy = :resolution_strategy,
                    updated_at = NOW()
                WHERE content_id = :content_id
            ");
            $stmt->bindParam(':version_hash', $chosenVersionHash);
            $stmt->bindParam(':resolution_strategy', $resolutionStrategy);
            $stmt->bindParam(':content_id', $contentId);
            $stmt->execute();

            $this->logAudit('conflict_resolution', null, null, $contentId, $resolutionStrategy);
            return true;
        } catch (\Exception $e) {
            error_log('Conflict resolution failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log federation actions to audit table
     */
    private function logAudit(string $action, ?string $sourceTenant, ?string $targetTenant, string $contentId, ?string $metadata = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO federation_audit_log 
            (action, source_tenant, target_tenant, content_id, metadata, timestamp) 
            VALUES (:action, :source_tenant, :target_tenant, :content_id, :metadata, NOW())
        ");
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':source_tenant', $sourceTenant);
        $stmt->bindParam(':target_tenant', $targetTenant);
        $stmt->bindParam(':content_id', $contentId);
        $stmt->bindParam(':metadata', $metadata);
        $stmt->execute();
    }
}
