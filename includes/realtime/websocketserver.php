<?php
namespace CMS\Realtime;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once __DIR__ . '/../../core/bootstrap.php';

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $sessions;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sessions = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
            case 'operation':
                $this->broadcastOperation($from, $data);
                break;
            case 'presence':
                $this->updatePresence($from, $data);
                break;
        }
    }

    protected function handleAuth(ConnectionInterface $conn, array $data) {
        // Validate session token and document access
        if ($this->validateSession($data['token'], $data['document_id'])) {
            $this->sessions[$conn->resourceId] = [
                'document_id' => $data['document_id'],
                'user_id' => $data['user_id']
            ];
            $conn->send(json_encode(['type' => 'auth_success']));
        } else {
            $conn->send(json_encode(['type' => 'auth_failure']));
            $conn->close();
        }
    }

    protected function broadcastOperation(ConnectionInterface $from, array $data) {
        if (!isset($this->sessions[$from->resourceId])) return;

        $data['sender'] = $this->sessions[$from->resourceId]['user_id'];
        $data['document_id'] = $this->sessions[$from->resourceId]['document_id'];

        foreach ($this->clients as $client) {
            if ($client !== $from && 
                isset($this->sessions[$client->resourceId]) &&
                $this->sessions[$client->resourceId]['document_id'] === $data['document_id']) {
                $client->send(json_encode($data));
            }
        }
    }

    protected function updatePresence(ConnectionInterface $conn, array $data) {
        // Presence tracking implementation
    }

    protected function validateSession(string $token, string $documentId): bool {
        $validator = new SessionValidator(
            $this->getDatabaseConnection(),
            (defined('WS_ENCRYPTION_KEY') ? WS_ENCRYPTION_KEY : '')
        );
        return $validator->validateSession($token, $documentId);
    }

    protected function getDatabaseConnection(): \PDO {
        return \core\Database::connection();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->sessions[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
