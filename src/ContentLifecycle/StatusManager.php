<?php
declare(strict_types=1);

namespace CMS\ContentLifecycle;

use CMS\Database\Connection;
use CMS\Exceptions\StatusTransitionException;

class StatusManager {
    private Connection $connection;
    private StatusTransitionValidator $validator;
    private AuditTrailService $auditTrail;

    public function __construct(
        Connection $connection,
        StatusTransitionValidator $validator,
        AuditTrailService $auditTrail
    ) {
        $this->connection = $connection;
        $this->validator = $validator;
        $this->auditTrail = $auditTrail;
    }

    public function transitionStatus(
        int $contentId,
        string $currentStatus,
        string $newStatus
    ): bool {
        if (!$this->validator->isValidTransition($currentStatus, $newStatus)) {
            throw new StatusTransitionException(
                "Invalid status transition from $currentStatus to $newStatus"
            );
        }

        try {
            $this->connection->beginTransaction();
            
            $stmt = $this->connection->prepare(
                "UPDATE content SET status = ? WHERE id = ?"
            );
            $stmt->execute([$newStatus, $contentId]);
            
            $this->auditTrail->recordTransition(
                $contentId,
                $currentStatus,
                $newStatus
            );
            
            $this->connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new StatusTransitionException(
                "Status transition failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
