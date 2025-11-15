<?php
require_once __DIR__ . '/core/bootstrap.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use CMS\Realtime\WebSocketPresenceHandler;

if (file_exists(__DIR__ . '/includes/container.php')) {
    $container = require_once __DIR__ . '/includes/container.php';
} else {
    error_log('Missing includes/container.php');
}
$presenceHandler = $container->get(WebSocketPresenceHandler::class);

$server = IoServer::factory(
    new HttpServer(
        new WsServer($presenceHandler)
    ),
    8080
);

$server->run();
