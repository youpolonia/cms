<?php
declare(strict_types=1);

if (!Auth::hasRole('developer')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

if (file_exists(__DIR__ . '/../../../core/eventbus.php')) {
    require_once __DIR__ . '/../../../core/eventbus.php';
} elseif (file_exists(__DIR__ . '/../../../core/EventBus.php')) {
    require_once __DIR__ . '/../../../core/eventbus.php';
} else {
    error_log('Missing core/eventbus.php');
}
require_once __DIR__ . '/../../../core/responsehandler.php';

$events = EventBus::getDebugLog();
$listeners = EventBus::getRegisteredListeners();

ResponseHandler::render('dev-tools/event-monitor', [
    'title' => 'Event Bus Monitor',
    'events' => $events,
    'listeners' => $listeners,
    'isLive' => ($_GET['live'] ?? '0') === '1'
]);
