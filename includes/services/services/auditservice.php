<?php

class AuditService {
    private PDO $pdo;
    private int $tenantId;
    private ?int $userId;

    public function __construct(PDO $pdo, int $tenantId, ?int $userId = null) {
        $this->pdo = $pdo;
        $this->tenantId = $tenantId;
        $this->userId = $userId;
    }

    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldState = null,
        ?array $newState = null
    ): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO audit_log (
                tenant_id,
                user_id,
                action,
                entity_type,
                entity_id,
                old_state,
                new_state
            ) VALUES (
                :tenant_id,
                :user_id,
                :action,
                :entity_type,
                :entity_id,
                :old_state,
                :new_state
            )
        ");

        $stmt->execute([
            ':tenant_id' => $this->tenantId,
            ':user_id' => $this->userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':old_state' => $oldState ? json_encode($oldState) : null,
            ':new_state' => $newState ? json_encode($newState) : null
        ]);
    }
}
