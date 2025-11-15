<?php
require_once __DIR__ . '/../core/bootstrap.php';

require_once CMS_ROOT . '/bootstrap.php';

use App\Services\Analytics\WebSocketServer;
use App\Services\Analytics\WebSocketHandler;
use App\Services\Analytics\EventProcessor;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

try {
    $handler = new WebSocketHandler(new EventProcessor());
    $wsServer = new WsServer(new WebSocketServer($handler));
    $httpServer = new HttpServer($wsServer);
    
    $server = IoServer::factory($httpServer, 8080);
    
    echo "Analytics WebSocket server running on port 8080\n";
    $server->run();
} catch (\Exception $e) {
    error_log($e->getMessage());
    echo "Error starting WebSocket server: Internal error\n";
    exit(1);
}
