<?php
namespace Services\Calendar;

use Services\Calendar\ICalService;
use Models\ScheduledEvent;
use Core\Database\Database;
use Core\Queue\QueueService;
use Core\Logger\Logger;

class SyncService {
    private $calendarService;
    private $db;
    private $queue;
    private $logger;

    public function __construct(
        ICalService $calendarService,
        Database $db,
        QueueService $queue,
        Logger $logger
    ) {
        $this->calendarService = $calendarService;
        $this->db = $db;
        $this->queue = $queue;
        $this->logger = $logger;
    }

    public function syncEvents(\DateTime $start, \DateTime $end): bool {
        $calendarEvents = $this->calendarService->getEvents($start, $end);
        $localEvents = $this->getLocalEvents($start, $end);

        $changes = $this->calculateChanges($calendarEvents, $localEvents);
        return $this->applyChanges($changes);
    }

    private function getLocalEvents(\DateTime $start, \DateTime $end): array {
        return $this->db->query(
            "SELECT * FROM scheduled_events 
             WHERE start_time BETWEEN ? AND ?",
            [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]
        )->fetchAll();
    }

    private function calculateChanges(array $calendarEvents, array $localEvents): array {
        $changes = ['create' => [], 'update' => [], 'delete' => []];
        
        // Map local events by calendar ID for easier comparison
        $localMap = [];
        foreach ($localEvents as $event) {
            $localMap[$event['calendar_id']] = $event;
        }

        // Check for new/updated events
        foreach ($calendarEvents as $event) {
            if (!isset($localMap[$event['id']])) {
                $changes['create'][] = $event;
            } elseif ($this->eventChanged($localMap[$event['id']], $event)) {
                $changes['update'][] = $event;
            }
        }

        // Check for deleted events
        foreach ($localEvents as $event) {
            if (!isset($calendarEvents[$event['calendar_id']])) {
                $changes['delete'][] = $event['id'];
            }
        }

        return $changes;
    }

    private function eventChanged(array $localEvent, array $calendarEvent): bool {
        return $localEvent['title'] !== $calendarEvent['title'] ||
               $localEvent['start'] !== $calendarEvent['start'] ||
               $localEvent['end'] !== $calendarEvent['end'] ||
               $localEvent['description'] !== $calendarEvent['description'];
    }

    private function applyChanges(array $changes): bool {
        try {
            $this->db->beginTransaction();

            // Process creates in batches of 100
            $createChunks = array_chunk($changes['create'], 100);
            foreach ($createChunks as $batch) {
                $values = [];
                foreach ($batch as $event) {
                    $values[] = [
                        'calendar_id' => $event['id'],
                        'title' => $event['title'],
                        'start_time' => $event['start'],
                        'end_time' => $event['end'],
                        'description' => $event['description'],
                        'location' => $event['location'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
                $this->db->bulkInsert('scheduled_events', $values);
            }

            // Process updates in batches of 100
            $updateChunks = array_chunk($changes['update'], 100);
            foreach ($updateChunks as $batch) {
                foreach ($batch as $event) {
                    $this->db->update('scheduled_events',
                        [
                            'title' => $event['title'],
                            'start_time' => $event['start'],
                            'end_time' => $event['end'],
                            'description' => $event['description'],
                            'location' => $event['location'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ],
                        ['calendar_id' => $event['id']]
                    );
                }
            }

            // Process deletes in batches of 100
            if (!empty($changes['delete'])) {
                $deleteChunks = array_chunk($changes['delete'], 100);
                foreach ($deleteChunks as $batch) {
                    $this->db->query(
                        "DELETE FROM scheduled_events WHERE id IN (?)",
                        [$batch]
                    );
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Calendar sync failed: " . $e->getMessage());
            return false;
        }
    }

    public function scheduleSync(\DateTime $start, \DateTime $end): bool {
        return $this->queue->push('calendar-sync', [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s')
        ]);
    }
}
