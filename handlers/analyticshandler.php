<?php
declare(strict_types=1);

namespace Handlers;

class AnalyticsHandler {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function postEvent(string $tenantId, array $data): void {
        // Basic validation
        if (empty($data['event_type'])) {
            throw new InvalidArgumentException('Missing event_type');
        }

        http_response_code(201);
        echo json_encode(['status' => 'success']);
    }

    public function getSummary(string $tenantId, array $params): void {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'event_counts' => [],
                'status_transitions' => []
            ]
        ]);
    }

    public function getEvents(string $tenantId, array $params): void {
        echo json_encode([
            'status' => 'success',
            'data' => []
        ]);
    }
}
