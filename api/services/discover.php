<?php
require_once __DIR__.'/../../includes/service/serviceintegrationhandler.php';

header('Content-Type: application/json');

try {
    $services = serviceintegrationhandler::discoverServices();
    echo json_encode([
        'status' => 'success',
        'services' => $services
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
