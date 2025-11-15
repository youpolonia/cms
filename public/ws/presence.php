<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__.'/../../includes/services/WebSocketPresenceHandler.php';
require_once __DIR__.'/../../includes/services/PresenceTracker.php';

use CMS\Services\PresenceTracker;
use CMS\Services\WebSocketPresenceHandler;

$tracker = new PresenceTracker();
$handler = new WebSocketPresenceHandler($tracker);

$server = new \Ratchet\App('localhost', 8080, '0.0.0.0');
$server->route('/presence', $handler, ['*']);
$server->run();
