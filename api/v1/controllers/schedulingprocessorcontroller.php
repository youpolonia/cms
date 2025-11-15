<?php

namespace Api\v1\Controllers;

use Includes\Services\SchedulingService;

class SchedulingProcessorController {
    public function processDueEvents() {
        header('Content-Type: application/json');
        
        try {
            $service = new SchedulingService();
            $processedCount = $service->processDueEvents();
            
            echo json_encode([
                'success' => true,
                'processed' => $processedCount
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
