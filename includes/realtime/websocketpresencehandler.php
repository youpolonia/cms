<?php
namespace CMS\Realtime;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketPresenceHandler implements MessageComponentInterface {
    private $presenceHandler;
    private $connections = [];
    private $documentConnections = [];

    public function __construct(PresenceHandler $presenceHandler) {
        $this->presenceHandler = $presenceHandler;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->connections[$conn->resourceId] = $conn;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case 'join':
                $this->handleJoin($from, $data);
                break;
            case 'leave':
                $this->handleLeave($from, $data);
                break;
            case 'heartbeat':
                $this->handleHeartbeat($from, $data);
                break;
        }
    }

    private function handleJoin(ConnectionInterface $conn, array $data) {
        if (!isset($data['token']) || !isset($data['document_id'])) {
            return;
        }

        try {
            $result = $this->presenceHandler->handleJoin($data['token'], $data['document_id']);
            $this->documentConnections[$data['document_id']][$conn->resourceId] = $conn;
            
            $conn->send(json_encode([
                'type' => 'presence_update',
                'active_users' => $result['active_users'],
                'user_id' => $result['user_id']
            ]));

            $this->broadcastPresenceUpdate($data['document_id']);
        } catch (\Exception $e) {
            $conn->send(json_encode(['error' => $e->getMessage()]));
        }
    }

    private function handleLeave(ConnectionInterface $conn, array $data) {
        if (!isset($data['token']) || !isset($data['document_id'])) {
            return;
        }

        $this->presenceHandler->handleLeave($data['token'], $data['document_id']);
        unset($this->documentConnections[$data['document_id']][$conn->resourceId]);
        $this->broadcastPresenceUpdate($data['document_id']);
    }

    private function handleHeartbeat(ConnectionInterface $conn, array $data) {
        $conn->send(json_encode(['type' => 'heartbeat_ack']));
    }

    private function broadcastPresenceUpdate(string $documentId) {
        $activeUsers = $this->presenceHandler->getActiveUsers($documentId);
        $message = json_encode([
            'type' => 'presence_update',
            'active_users' => $activeUsers
        ]);

        if (isset($this->documentConnections[$documentId])) {
            foreach ($this->documentConnections[$documentId] as $conn) {
                $conn->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        unset($this->connections[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}
