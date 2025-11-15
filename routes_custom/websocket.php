<?php

require_once __DIR__.'/../bootstrap.php';

$server = new App\Services\Analytics\WebSocketServer();
$server->run(8080);
