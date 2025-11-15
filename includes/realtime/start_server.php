<?php
require_once __DIR__ . '/websocketserver.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use CMS\Realtime\WebSocketServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
$server->run();
