<?php
/**
 * Content Federation Engine
 */
class ContentFederator {
    /**
     * Share content with target tenants
     * @param PDO $pdo Database connection
     * @param string $contentType posts|pages|blocks
     * @param int $contentId Source content ID
     * @param array $targetTenants List of tenant IDs
     * @param array $options Sharing options
     * @return array Results per tenant
     */
    public static function shareContent(PDO $pdo, $contentType, $contentId, $targetTenants, $options = []) {
        $results = [];
        $sourceTenant = TenantManager::getCurrentTenant();

        try {
            $pdo->beginTransaction();

            // Get source content
            $sourceContent = self::getSourceContent($pdo, $contentType, $contentId, $sourceTenant);
            
            if (!$sourceContent) {
                throw new Exception("Source content not found");
            }

            // Prepare version metadata
            $versionHash = self::generateVersionHash($sourceContent);
            $auditLog = [
                'action' => 'content_federation',
                'source_tenant' => $sourceTenant,
                'content_type' => $contentType,
                'content_id' => $contentId,
                'version_hash' => $versionHash,
                'timestamp' => time()
            ];

            foreach ($targetTenants as $tenantId) {
                if (!TenantManager::validateTenant($pdo, $tenantId)) {
                    $results[$tenantId] = [
                        'status' => 'error',
                        'message' => 'Invalid target tenant'
                    ];
                    continue;
                }

                // Check for existing content to handle updates
                $existingContent = self::getExistingContent($pdo, $contentType, $contentId, $tenantId);
                
                if ($existingContent) {
                    // Conflict resolution
                    $result = self::handleConflict(
                        $pdo,
                        $contentType,
                        $contentId,
                        $tenantId,
                        $sourceContent,
                        $existingContent,
                        $options
                    );
                } else {
                    // Insert new content
                    $result = self::insertContent(
                        $pdo,
                        $contentType,
                        $contentId,
                        $tenantId,
                        $sourceContent,
                        $versionHash
                    );
                }

                // Log audit entry
                $auditLog['target_tenant'] = $tenantId;
                $auditLog['status'] = $result['status'];
                self::logAudit($pdo, $auditLog);

                $results[$tenantId] = $result;
            }

            $pdo->commit();
            return $results;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Federation error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private static function getSourceContent(PDO $pdo, $contentType, $contentId, $tenantId) {
        $table = self::getContentTable($contentType);
        $stmt = $pdo->prepare("
            SELECT * FROM {$table}
            WHERE id = :id AND tenant_id = :tenant_id
        ");
        $stmt->execute([':id' => $contentId, ':tenant_id' => $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function getExistingContent(PDO $pdo, $contentType, $contentId, $tenantId) {
        $table = self::getContentTable($contentType);
        $stmt = $pdo->prepare("
            SELECT * FROM {$table}
            WHERE federated_source_id = :id AND tenant_id = :tenant_id
        ");
        $stmt->execute([':id' => $contentId, ':tenant_id' => $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function handleConflict($pdo, $contentType, $contentId, $tenantId, $sourceContent, $existingContent, $options) {
        $resolutionStrategy = $options['conflict_resolution'] ?? 'timestamp';
        
        switch ($resolutionStrategy) {
            case 'manual':
                return [
                    'status' => 'conflict',
                    'message' => 'Manual resolution required',
                    'source_version' => self::generateVersionHash($sourceContent),
                    'target_version' => self::generateVersionHash($existingContent)
                ];
                
            case 'source':
                return self::updateContent(
                    $pdo,
                    $contentType,
                    $contentId,
                    $tenantId,
                    $sourceContent,
                    self::generateVersionHash($sourceContent)
                );
                
            case 'timestamp':
            default:
                if (strtotime($sourceContent['updated_at']) > strtotime($existingContent['updated_at'])) {
                    return self::updateContent(
                        $pdo,
                        $contentType,
                        $contentId,
                        $tenantId,
                        $sourceContent,
                        self::generateVersionHash($sourceContent)
                    );
                }
                return [
                    'status' => 'skipped',
                    'message' => 'Target version is newer'
                ];
        }
    }

    private static function insertContent($pdo, $contentType, $contentId, $tenantId, $content, $versionHash) {
        $table = self::getContentTable($contentType);
        $columns = array_keys($content);
        $columns[] = 'federated_source_id';
        $columns[] = 'version_hash';
        
        $placeholders = array_map(function($col) { 
            return ":{$col}"; 
        }, $columns);
        
        $values = $content;
        $values['federated_source_id'] = $contentId;
        $values['version_hash'] = $versionHash;
        $values['tenant_id'] = $tenantId;
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return [
            'status' => 'created',
            'new_id' => $pdo->lastInsertId(),
            'version_hash' => $versionHash
        ];
    }

    private static function updateContent($pdo, $contentType, $contentId, $tenantId, $content, $versionHash) {
        $table = self::getContentTable($contentType);
        $updates = [];
        $values = ['tenant_id' => $tenantId, 'federated_source_id' => $contentId];
        
        foreach ($content as $col => $val) {
            if ($col !== 'id' && $col !== 'tenant_id') {
                $updates[] = "{$col} = :{$col}";
                $values[$col] = $val;
            }
        }
        
        $values['version_hash'] = $versionHash;
        $updates[] = "version_hash = :version_hash";
        
        $sql = "UPDATE {$table} 
                SET " . implode(', ', $updates) . "
                WHERE federated_source_id = :federated_source_id 
                AND tenant_id = :tenant_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return [
            'status' => 'updated',
            'version_hash' => $versionHash
        ];
    }

    private static function generateVersionHash($content) {
        return md5(json_encode($content));
    }

    private static function getContentTable($contentType) {
        $tables = [
            'posts' => 'posts',
            'pages' => 'content_pages',
            'blocks' => 'content_blocks'
        ];
        return $tables[$contentType] ?? 'content_pages';
    }

    private static function logAudit(PDO $pdo, $data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { 
            return ":{$col}"; 
        }, $columns);
        
        $sql = "INSERT INTO audit_log (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
    }
}
