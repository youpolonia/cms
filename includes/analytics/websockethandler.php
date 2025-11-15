<?php
namespace Includes\Analytics;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class WebSocketHandler implements MessageComponentInterface {
    protected $clients;
    protected $channels = [];

    public function __construct() {
        $this->clients = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $conn->send(json_encode([
            'type' => 'connection', 
            'status' => 'established'
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'subscribe':
                    $this->subscribe($from, $data['channel']);
                    break;
                case 'unsubscribe':
                    $this->unsubscribe($from, $data['channel']);
                    break;
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        foreach ($this->channels as $channel => $subscribers) {
            if (isset($subscribers[$conn->resourceId])) {
                unset($this->channels[$channel][$conn->resourceId]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("WebSocket error: {$e->getMessage()}");
        $conn->close();
    }

    protected function subscribe(ConnectionInterface $conn, $channel) {
        if (!isset($this->channels[$channel])) {
            $this->channels[$channel] = [];
        }
        $this->channels[$channel][$conn->resourceId] = $conn;
    }

    protected function unsubscribe(ConnectionInterface $conn, $channel) {
        if (isset($this->channels[$channel][$conn->resourceId])) {
            unset($this->channels[$channel][$conn->resourceId]);
        }
    }

    public function broadcast($channel, $data) {
        if (isset($this->channels[$channel])) {
            foreach ($this->channels[$channel] as $client) {
                $client->send(json_encode($data));
            }
        }
    }
}
