<?php
namespace CMS\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use CMS\Services\PresenceTracker;

class WebSocketPresenceHandler implements MessageComponentInterface {
    private $presenceTracker;
    private $connections = [];

    public function __construct(PresenceTracker $presenceTracker) {
        $this->presenceTracker = $presenceTracker;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->connections[$conn->resourceId] = $conn;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if ($data['type'] === 'presence') {
            $userId = (int)$data['userId'];
            $documentId = $data['documentId'];
            
            switch ($data['action']) {
                case 'connect':
                    $this->presenceTracker->userConnected($userId, $documentId);
                    break;
                case 'disconnect':
                    $this->presenceTracker->userDisconnected($userId, $documentId);
                    break;
                case 'activity':
                    $this->presenceTracker->updateActivity($userId, $documentId);
                    break;
            }

            $this->broadcastPresenceUpdate($documentId);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        unset($this->connections[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("WebSocket error: {$e->getMessage()}");
        $conn->close();
    }

    public function broadcastPresenceUpdate(string $documentId) {
        $presenceData = [
            'type' => 'presence',
            'documentId' => $documentId,
            'users' => $this->presenceTracker->getActiveUsers($documentId)
        ];

        foreach ($this->connections as $conn) {
            $conn->send(json_encode($presenceData));
        }
    }
}
