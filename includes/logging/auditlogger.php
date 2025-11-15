<?php
declare(strict_types=1);

namespace Logging;

class AuditLogger {
    private \PDO $connection;

    public function __construct(\PDO $connection) {
        $this->connection = $connection;
    }

    public function logAction(
        string $action,
        string $entityType,
        int $entityId,
        ?int $userId,
        ?string $oldState,
        ?string $newState,
        ?array $metadata = null
    ): void {
        $stmt = $this->connection->prepare(
            "INSERT INTO audit_logs 
            (action, entity_type, entity_id, user_id, old_state, new_state, metadata) 
            VALUES (:action, :entity_type, :entity_id, :user_id, :old_state, :new_state, :metadata)"
        );

        $stmt->execute([
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':user_id' => $userId,
            ':old_state' => $oldState,
            ':new_state' => $newState,
            ':metadata' => $metadata ? json_encode($metadata) : null
        ]);
    }

    public static function getInstance(\PDO $connection): self {
        static $instance = null;
        if ($instance === null) {
            $instance = new self($connection);
        }
        return $instance;
    }
}
