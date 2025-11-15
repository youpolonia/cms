<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class StatusTransitionController
{
    /**
     * Handle status transition request
     */
    public function transition(array $request): array
    {
        // Validate required fields
        $required = ['entity_type', 'entity_id', 'from_status', 'to_status'];
        foreach ($required as $field) {
            if (empty($request[$field])) {
                return $this->errorResponse("Missing required field: $field", 400);
            }
        }

        // Log transition to database
        $transitionId = $this->logTransition(
            $request['entity_type'],
            (int)$request['entity_id'],
            $request['from_status'],
            $request['to_status'],
            $request['reason'] ?? null
        );

        if (!$transitionId) {
            return $this->errorResponse('Failed to log status transition', 500);
        }

        return [
            'status' => 'success',
            'data' => [
                'transition_id' => $transitionId,
                'entity_type' => $request['entity_type'],
                'entity_id' => $request['entity_id'],
                'transition_time' => date('c')
            ]
        ];
    }

    private function logTransition(
        string $entityType,
        int $entityId,
        string $fromStatus,
        string $toStatus,
        ?string $reason
    ): ?int {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO status_transitions 
                (entity_type, entity_id, from_status, to_status, reason)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$entityType, $entityId, $fromStatus, $toStatus, $reason]);
            return (int)$pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Status transition error: " . $e->getMessage());
            return null;
        }
    }

    private function errorResponse(string $message, int $code): array
    {
        return [
            'status' => 'error',
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
    }
}
