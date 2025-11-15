<?php
namespace CMS\Services;

use CMS\Core\DatabaseConnection;
use CMS\Helpers\SessionHelper;

class PresenceTracker {
    private $db;
    private $sessionHelper;
    private $activeUsers = [];

    public function __construct(DatabaseConnection $db, SessionHelper $sessionHelper) {
        $this->db = $db;
        $this->sessionHelper = $sessionHelper;
    }

    public function userConnected(int $userId, string $documentId): void {
        $this->activeUsers[$documentId][$userId] = [
            'connected_at' => time(),
            'last_active' => time(),
            'status' => 'active'
        ];
        
        $this->updateDatabase($userId, $documentId, 'connect');
    }

    public function userDisconnected(int $userId, string $documentId): void {
        if (isset($this->activeUsers[$documentId][$userId])) {
            unset($this->activeUsers[$documentId][$userId]);
            $this->updateDatabase($userId, $documentId, 'disconnect');
        }
    }

    public function updateActivity(int $userId, string $documentId): void {
        if (isset($this->activeUsers[$documentId][$userId])) {
            $this->activeUsers[$documentId][$userId]['last_active'] = time();
            $this->updateDatabase($userId, $documentId, 'activity');
        }
    }

    public function getActiveUsers(string $documentId): array {
        return $this->activeUsers[$documentId] ?? [];
    }

    private function updateDatabase(int $userId, string $documentId, string $action): void {
        $this->db->query(
            "INSERT INTO presence_events (user_id, document_id, action, timestamp) 
            VALUES (?, ?, ?, ?)",
            [$userId, $documentId, $action, time()]
        );
    }

    public function cleanupInactiveUsers(int $timeout = 300): void {
        $now = time();
        foreach ($this->activeUsers as $docId => $users) {
            foreach ($users as $userId => $data) {
                if ($now - $data['last_active'] > $timeout) {
                    $this->userDisconnected($userId, $docId);
                }
            }
        }
    }
}
