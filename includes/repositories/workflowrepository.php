<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;
use App\Services\AuditService;

class WorkflowRepository {
    private PDO $pdo;
    private AuditService $auditService;

    public function __construct(PDO $pdo, AuditService $auditService) {
        $this->pdo = $pdo;
        $this->auditService = $auditService;
    }

    public function createWorkflowDefinition(
        string $name, 
        array $states, 
        array $transitions, 
        string $initialState
    ): int|false {
        $sql = "INSERT INTO workflow_definitions 
                (name, states, transitions, initial_state) 
                VALUES (:name, :states, :transitions, :initial_state)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':states' => json_encode($states),
                ':transitions' => json_encode($transitions),
                ':initial_state' => $initialState
            ]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->auditService->logError("Workflow creation failed", [
                'error' => $e->getMessage(),
                'name' => $name
            ]);
            return false;
        }
    }

    public function getWorkflowDefinition(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM workflow_definitions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $workflow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($workflow) {
            $workflow['states'] = json_decode($workflow['states'], true);
            $workflow['transitions'] = json_decode($workflow['transitions'], true);
        }
        return $workflow;
    }

    public function getAllWorkflowDefinitions(): array {
        $stmt = $this->pdo->query("SELECT * FROM workflow_definitions ORDER BY name ASC");
        $workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($wf) {
            $wf['states'] = json_decode($wf['states'], true);
            $wf['transitions'] = json_decode($wf['transitions'], true);
            return $wf;
        }, $workflows);
    }

    public function deleteWorkflowDefinition(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM workflow_definitions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function createWorkflowInstance(
        int $definitionId, 
        int $subjectId, 
        string $initialState
    ): int|false {
        $sql = "INSERT INTO workflow_instances 
                (definition_id, subject_id, current_state, created_at, updated_at) 
                VALUES (:def_id, :sub_id, :state, NOW(), NOW())";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':def_id' => $definitionId,
                ':sub_id' => $subjectId,
                ':state' => $initialState
            ]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->auditService->logError("Workflow instance creation failed", [
                'error' => $e->getMessage(),
                'definition_id' => $definitionId,
                'subject_id' => $subjectId
            ]);
            return false;
        }
    }

    public function getWorkflowInstance(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM workflow_instances WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getInstancesForSubject(int $subjectId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM workflow_instances 
             WHERE subject_id = :subject_id 
             ORDER BY created_at DESC"
        );
        $stmt->execute([':subject_id' => $subjectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateInstanceState(
        int $instanceId, 
        string $newState
    ): bool {
        $sql = "UPDATE workflow_instances 
                SET current_state = :state, updated_at = NOW() 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':state' => $newState,
            ':id' => $instanceId
        ]);
    }

    public function verifyTenantOwnership(string $instance_id, string $tenant_id): bool {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM workflow_instances
             WHERE id = :id AND tenant_id = :tenant_id
             LIMIT 1"
        );
        $stmt->execute([':id' => $instance_id, ':tenant_id' => $tenant_id]);
        return (bool)$stmt->fetchColumn();
    }
}
