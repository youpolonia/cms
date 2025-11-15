<?php

namespace Core;

class StatusTransitionHandler
{
    /**
     * Validates a state transition.
     *
     * @param int $oldStatusId The current status ID.
     * @param int $newStatusId The new status ID.
     * @param string|null $tenantId Optional tenant ID for tenant-aware validation
     * @return bool True if the transition is valid, false otherwise.
     */
    public static function validateState(int $oldStatusId, int $newStatusId, ?string $tenantId = null): bool
    {
        // Load valid status transitions from config file
        $validTransitions = require_once __DIR__ . '/../../config/status_rules.php';

        // Check tenant-specific rules first if tenantId provided
        if ($tenantId && isset($validTransitions[$tenantId][$oldStatusId])) {
            return in_array($newStatusId, $validTransitions[$tenantId][$oldStatusId]);
        }

        // Fall back to default rules
        return in_array($newStatusId, $validTransitions['_default'][$oldStatusId] ?? []);
    }

    /**
     * Applies a state transition.
     *
     * @param int $contentId The ID of the content.
     * @param int $oldStatusId The current status ID.
     * @param int $newStatusId The new status ID.
     * @param string|null $tenantId Optional tenant ID for tenant-aware transition
     * @return bool True if the transition is applied successfully, false otherwise.
     */
    public static function applyTransition(int $contentId, int $oldStatusId, int $newStatusId, ?string $tenantId = null): bool
    {
        // Validate the transition
        if (!self::validateState($oldStatusId, $newStatusId, $tenantId)) {
            return false;
        }

        // Update the content status in the database
        $query = "UPDATE content SET status_id = :newStatusId
                  WHERE id = :contentId
                  AND status_id = :oldStatusId" .
                  ($tenantId ? " AND tenant_id = :tenantId" : "");
        $stmt = DB::prepare($query);
        $stmt->bindParam(':newStatusId', $newStatusId);
        $stmt->bindParam(':contentId', $contentId);
        $stmt->bindParam(':oldStatusId', $oldStatusId);
        if ($tenantId) {
            $stmt->bindParam(':tenantId', $tenantId);
        }

        try {
            $stmt->execute();
            $updatedRows = $stmt->rowCount();

            if ($updatedRows > 0) {
                // Log the transition
                self::logTransition($contentId, $oldStatusId, $newStatusId);
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            // Handle the exception
            error_log("Transition error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logs a state transition.
     *
     * @param int $contentId The ID of the content.
     * @param int $oldStatusId The previous status ID.
     * @param int $newStatusId The new status ID.
     * @param string|null $tenantId Optional tenant ID for tenant-aware logging
     * @return void
     */
    private static function logTransition(int $contentId, int $oldStatusId, int $newStatusId, ?string $tenantId = null): void
    {
        // Insert a new record into the status_transitions table
        $query = "INSERT INTO status_transitions (content_id, old_status_id, new_status_id, tenant_id, transitioned_at)
                  VALUES (:contentId, :oldStatusId, :newStatusId, :tenantId, NOW())";
        $stmt = DB::prepare($query);
        $stmt->bindParam(':contentId', $contentId);
        $stmt->bindParam(':oldStatusId', $oldStatusId);
        $stmt->bindParam(':newStatusId', $newStatusId);
        $stmt->bindValue(':tenantId', $tenantId);

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            // Handle the exception
            error_log("Logging error: " . $e->getMessage());
        }
    }
}
