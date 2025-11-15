<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace Admin\Controllers;

use Core\EventBus;
use Core\Auth;

class DevToolsController {
    public function eventMonitor() {
        // Check developer permissions
        if (!Auth::hasRole('developer')) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }

        $eventBus = EventBus::getInstance();
        
        // Get initial data
        $listeners = $eventBus->getRegisteredListeners();
        $events = $eventBus->getDebugLog();
        $isLive = isset($_GET['live']) && $_GET['live'] === '1';

        // Render view
        require_once __DIR__ . '/../../views/dev-tools/event-monitor.php';
    }
}
