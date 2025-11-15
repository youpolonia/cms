<?php
declare(strict_types=1);

namespace CMS\ContentLifecycle;

class AuditTrailService {
    public function logStatusChange(
        int $contentId,
        string $oldStatus,
        string $newStatus,
        int $userId
    ): void {
        $logEntry = sprintf(
            '[%s] Content #%d status changed from %s to %s by user #%d',
            date('Y-m-d H:i:s'),
            $contentId,
            $oldStatus,
            $newStatus,
            $userId
        );
        
        // Would integrate with existing logging system
        error_log($logEntry);
    }
}
