<?php
namespace Services\Calendar;

interface CalendarService {
    public function authenticate(string $authCode): bool;
    public function refreshToken(): bool;
    public function getEvents(\DateTime $start, \DateTime $end): array;
    public function createEvent(array $eventData): bool;
    public function updateEvent(string $eventId, array $eventData): bool;
    public function deleteEvent(string $eventId): bool;
    public function getConnectionStatus(): array;
    public function disconnect(): bool;
}
