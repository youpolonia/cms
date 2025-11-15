<?php
namespace CMS\Realtime;

class PresenceHandler {
    private $tracker;
    private $sessionValidator;

    public function __construct(PresenceTracker $tracker, SessionValidator $sessionValidator) {
        $this->tracker = $tracker;
        $this->sessionValidator = $sessionValidator;
    }

    public function handleJoin(string $token, string $documentId): array {
        $userId = $this->sessionValidator->getUserIdFromToken($token);
        if (!$userId || !$this->sessionValidator->validateDocumentAccess($userId, $documentId)) {
            throw new \RuntimeException('Invalid session or access denied');
        }

        $this->tracker->userJoined($userId, $documentId);
        return [
            'active_users' => $this->tracker->getActiveUsers($documentId),
            'user_id' => $userId
        ];
    }

    public function handleLeave(string $token, string $documentId): void {
        $userId = $this->sessionValidator->getUserIdFromToken($token);
        if ($userId) {
            $this->tracker->userLeft($userId, $documentId);
        }
    }

    public function getActiveUsers(string $documentId): array {
        return $this->tracker->getActiveUsers($documentId);
    }

    public function cleanupInactive(): void {
        $this->tracker->cleanupInactive();
    }
}
