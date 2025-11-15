<?php

class SchedulingController {
    protected $schedulingService;

    public function __construct() {
        $this->schedulingService = new SchedulingService();
    }

    public function createScheduledEvent($request) {
        // Validate permissions
        if (!has_permission('schedule_content')) {
            return response(403, 'Permission denied');
        }

        // Validate input
        $required = ['content_id', 'version_id', 'scheduled_at'];
        if (!validate_request($request, $required)) {
            return response(400, 'Missing required fields');
        }

        try {
            $event = $this->schedulingService->createScheduledEvent(
                $request['content_id'],
                $request['version_id'],
                $request['scheduled_at']
            );
            return response(201, $event);
        } catch (Exception $e) {
            return response(500, $e->getMessage());
        }
    }

    public function getScheduledEvents($request) {
        if (!has_permission('view_scheduled_content')) {
            return response(403, 'Permission denied');
        }

        $filters = [
            'status' => $request['status'] ?? null,
            'start_date' => $request['start_date'] ?? null,
            'end_date' => $request['end_date'] ?? null
        ];

        try {
            $events = $this->schedulingService->getScheduledEvents($filters);
            return response(200, $events);
        } catch (Exception $e) {
            return response(500, $e->getMessage());
        }
    }

    public function updateScheduledEvent($request, $id) {
        if (!has_permission('schedule_content')) {
            return response(403, 'Permission denied');
        }

        try {
            $event = $this->schedulingService->updateScheduledEvent(
                $id,
                $request['scheduled_at'] ?? null,
                $request['status'] ?? null
            );
            return response(200, $event);
        } catch (Exception $e) {
            return response(500, $e->getMessage());
        }
    }

    public function cancelScheduledEvent($request, $id) {
        if (!has_permission('schedule_content')) {
            return response(403, 'Permission denied');
        }

        try {
            $this->schedulingService->cancelScheduledEvent($id);
            return response(204);
        } catch (Exception $e) {
            return response(500, $e->getMessage());
        }
    }
}
