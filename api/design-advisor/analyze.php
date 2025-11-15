<?php
require_once __DIR__.'/../../modules/aiadvisor/advisorinterface.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['layout']) || empty($input['theme'])) {
        throw new Exception('Missing required parameters');
    }

    $result = AdvisorInterface::analyzeLayout($input['layout'], $input['theme']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
