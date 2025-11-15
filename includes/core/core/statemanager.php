<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';

class StateManager {
    private $pdo;
    private $response;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->response = new Response();
    }

    public function getState(int $contentId): array {
        try {
            $stmt = $this->pdo->prepare("SELECT state FROM content WHERE id = ?");
            $stmt->execute([$contentId]);
            $state = $stmt->fetchColumn();
            
            return $this->response->success([
                'content_id' => $contentId,
                'state' => $state
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function setState(int $contentId, string $state): array {
        $validStates = ['draft', 'review', 'published'];
        
        if (!in_array($state, $validStates)) {
            return $this->response->error('Invalid state value');
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE content SET state = ? WHERE id = ?");
            $stmt->execute([$state, $contentId]);
            
            return $this->response->success([
                'content_id' => $contentId,
                'new_state' => $state
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function transitionState(int $contentId, string $newState): array {
        $validTransitions = [
            'draft' => ['review'],
            'review' => ['draft', 'published'],
            'published' => ['draft']
        ];

        try {
            // Get current state
            $currentState = $this->getState($contentId)['data']['state'] ?? null;
            
            if (!$currentState) {
                return $this->response->error('Content not found');
            }

            // Validate transition
            if (!in_array($newState, $validTransitions[$currentState] ?? [])) {
                return $this->response->error('Invalid state transition');
            }

            // Update state
            return $this->setState($contentId, $newState);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
