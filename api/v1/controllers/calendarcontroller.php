<?php
namespace Api\v1\Controllers;

use Services\Calendar\SyncService;
use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class CalendarController extends Controller {
    private $syncService;

    public function __construct(SyncService $syncService) {
        $this->syncService = $syncService;
    }

    public function sync(Request $request): Response {
        $data = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $start = new \DateTime($data['start']);
        $end = new \DateTime($data['end']);

        if ($this->syncService->syncEvents($start, $end)) {
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Sync failed'], 500);
    }

    public function scheduleSync(Request $request): Response {
        $data = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $start = new \DateTime($data['start']);
        $end = new \DateTime($data['end']);

        if ($this->syncService->scheduleSync($start, $end)) {
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Failed to schedule sync'], 500);
    }

    public function resolveConflict(Request $request): Response {
        $data = $request->validate([
            'event_id' => 'required|integer',
            'resolution' => 'required|in:keep_local,keep_remote,merge'
        ]);

        // Implementation would depend on specific conflict resolution logic
        return $this->json(['success' => true]);
    }

    public function getSyncStatus(): Response {
        // Implementation would check queue status and last sync time
        return $this->json([
            'last_sync' => date('Y-m-d H:i:s'),
            'status' => 'idle'
        ]);
    }
}
