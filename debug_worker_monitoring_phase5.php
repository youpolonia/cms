<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
class DebugWorkerMonitoringPhase5 {
    public static function logMessage($message) {
        // No-op implementation for testing
    }
}
