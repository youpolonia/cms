<?php
namespace Services\Calendar;

use Services\Encryption\EncryptionService;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class ICalService implements CalendarService {
    private $connectionData;
    private $encryptionService;
    private $calendarUrl;

    public function __construct(array $connectionData, EncryptionService $encryptionService) {
        $this->connectionData = $connectionData;
        $this->encryptionService = $encryptionService;
        $this->calendarUrl = $connectionData['calendar_url'] ?? null;
    }

    public function authenticate(string $authCode): bool {
        // No authentication needed for public iCal feeds
        return true;
    }

    public function refreshToken(): bool {
        // No token refresh needed for iCal
        return true;
    }

    public function getEvents(\DateTime $start, \DateTime $end): array {
        if (empty($this->calendarUrl)) {
            return [];
        }

        try {
            $icalString = file_get_contents($this->calendarUrl);
            $ical = new \ICal\ICal($icalString, [
                'defaultTimeZone' => 'UTC'
            ]);

            $events = $ical->eventsFromRange($start->format('Y-m-d'), $end->format('Y-m-d'));
            
            return array_map(function($event) {
                return [
                    'id' => $event->uid,
                    'title' => $event->summary,
                    'start' => $event->dtstart,
                    'end' => $event->dtend,
                    'description' => $event->description,
                    'location' => $event->location
                ];
            }, $events);
        } catch (\Exception $e) {
            error_log('ICal fetch failed: ' . $e->getMessage());
            return [];
        }
    }

    public function createEvent(array $eventData): bool {
        // iCal is read-only - cannot create events
        return false;
    }

    public function updateEvent(string $eventId, array $eventData): bool {
        // iCal is read-only - cannot update events
        return false;
    }

    public function deleteEvent(string $eventId): bool {
        // iCal is read-only - cannot delete events
        return false;
    }

    public function getConnectionStatus(): array {
        return [
            'connected' => !empty($this->calendarUrl),
            'provider' => 'ical',
            'expires_at' => null
        ];
    }

    public function disconnect(): bool {
        $this->calendarUrl = null;
        return true;
    }

    public function exportEvents(array $events): string {
        $vCalendar = new Calendar();
        
        foreach ($events as $eventData) {
            $event = new Event();
            $event->setSummary($eventData['title'])
                  ->setDescription($eventData['description'] ?? '')
                  ->setLocation($eventData['location'] ?? '')
                  ->setOccurrence(
                      new \DateTimeImmutable($eventData['start']),
                      new \DateTimeImmutable($eventData['end'])
                  );
            $vCalendar->addEvent($event);
        }

        $calendarFactory = new CalendarFactory();
        return $calendarFactory->createCalendar($vCalendar);
    }
}
