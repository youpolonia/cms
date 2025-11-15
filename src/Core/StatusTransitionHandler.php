<?php

namespace Core;

class StatusTransitionHandler
{
    public static function validateState(int $oldStatusId, int $newStatusId): bool
    {
        // Load valid status transitions from config file
        $validTransitions = require __DIR__ . '/../../config/status_rules.php';
        
        // Check if the transition is valid
        return in_array($newStatusId, $validTransitions[$oldStatusId] ?? []);
    }

    public static function applyTransition(int $contentId, int $oldStatusId, int $newStatusId): bool
    {
        // Validate the transition
        if (!self::validateState($oldStatusId, $newStatusId)) {
            return false;
        }
        
        // Get current tenant from session
        $tenantId = $_SESSION['tenant_id'] ?? null;
        if (!$tenantId) {
            return false;
        }
    
        $sql = "UPDATE contents SET status_id = :newStatusId
                WHERE id = :contentId AND tenant_id = :tenantId";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':newStatusId', $newStatusId);
        $stmt->bindParam(':contentId', $contentId);
        $stmt->bindParam(':tenantId', $tenantId);
        if (!$stmt->execute()) {
            return false;
        }
    
        // Log the transition
        self::logTransition($contentId, $oldStatusId, $newStatusId);
    
        return true;
    }

    private static function logTransition(int $contentId, int $oldStatusId, int $newStatusId): void
    {
        // Get current tenant from session
        $tenantId = $_SESSION['tenant_id'] ?? null;
        
        Database::insert('status_transitions', [
            'content_id' => $contentId,
            'tenant_id' => $tenantId,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'transitioned_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
