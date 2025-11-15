<?php

require_once __DIR__ . '/../../includes/services/SchedulingService.php';
require_once __DIR__ . '/../controllers/schedulingprocessorcontroller.php';

use Api\v1\Controllers\SchedulingProcessorController;

// Simple token-based authentication
$validToken = getenv('SCHEDULING_PROCESSOR_TOKEN');
$providedToken = $_GET['token'] ?? '';

if ($validToken && $providedToken !== $validToken) {
    http_response_code(401);
    die(json_encode(['error' => 'Invalid token']));
}

$controller = new SchedulingProcessorController();
$controller->processDueEvents();
