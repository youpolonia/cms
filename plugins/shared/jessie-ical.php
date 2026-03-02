<?php
/**
 * Jessie CMS — iCal/ICS Generator
 * Generates .ics files for events and bookings (RFC 5545)
 * 
 * Usage:
 *   require_once CMS_ROOT . '/plugins/shared/jessie-ical.php';
 *   JessieICal::download($event);  // force download
 *   JessieICal::output($events);   // output multiple events
 *   $ics = JessieICal::generate($event);  // get string
 */
class JessieICal
{
    /**
     * Generate ICS content for one or more events
     * @param array $events  Single event or array of events
     * Each event: [title, description, location, start (datetime), end (datetime), url, organizer_email, organizer_name]
     */
    public static function generate(array $events): string
    {
        // Normalize: single event → array of events
        if (isset($events['title'])) {
            $events = [$events];
        }

        $siteName = 'Jessie CMS';
        try { $siteName = db()->query("SELECT value FROM settings WHERE `key`='site_name'")->fetchColumn() ?: $siteName; } catch (\Throwable $e) {}

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Jessie CMS//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . self::escape($siteName),
        ];

        foreach ($events as $event) {
            $uid = md5(($event['id'] ?? '') . ($event['title'] ?? '') . ($event['start'] ?? '')) . '@jessiecms';
            $dtStart = self::formatDate($event['start'] ?? 'now');
            $dtEnd = self::formatDate($event['end'] ?? $event['start'] ?? 'now');
            $created = self::formatDate($event['created_at'] ?? 'now');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTART:' . $dtStart;
            $lines[] = 'DTEND:' . $dtEnd;
            $lines[] = 'DTSTAMP:' . self::formatDate('now');
            $lines[] = 'CREATED:' . $created;
            $lines[] = 'SUMMARY:' . self::escape($event['title'] ?? 'Event');

            if (!empty($event['description'])) {
                $lines[] = 'DESCRIPTION:' . self::escape(strip_tags($event['description']));
            }
            if (!empty($event['location'])) {
                $lines[] = 'LOCATION:' . self::escape($event['location']);
            }
            if (!empty($event['url'])) {
                $lines[] = 'URL:' . $event['url'];
            }
            if (!empty($event['organizer_email'])) {
                $name = $event['organizer_name'] ?? '';
                $lines[] = 'ORGANIZER' . ($name ? ';CN=' . self::escape($name) : '') . ':mailto:' . $event['organizer_email'];
            }

            // Reminder 1 hour before
            $lines[] = 'BEGIN:VALARM';
            $lines[] = 'TRIGGER:-PT1H';
            $lines[] = 'ACTION:DISPLAY';
            $lines[] = 'DESCRIPTION:Reminder: ' . self::escape($event['title'] ?? 'Event');
            $lines[] = 'END:VALARM';

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    /**
     * Force-download ICS file
     */
    public static function download(array $event, string $filename = ''): void
    {
        if (!$filename) {
            $filename = self::slugify($event['title'] ?? 'event') . '.ics';
        }
        $ics = self::generate($event);
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($ics));
        echo $ics;
        exit;
    }

    /**
     * Output ICS (for subscribable calendar URL)
     */
    public static function output(array $events): void
    {
        $ics = self::generate($events);
        header('Content-Type: text/calendar; charset=utf-8');
        echo $ics;
        exit;
    }

    /**
     * Generate Google Calendar add URL
     */
    public static function googleCalUrl(array $event): string
    {
        $start = date('Ymd\THis\Z', strtotime($event['start'] ?? 'now'));
        $end = date('Ymd\THis\Z', strtotime($event['end'] ?? $event['start'] ?? 'now'));
        return 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode($event['title'] ?? '')
            . '&dates=' . $start . '/' . $end
            . '&details=' . urlencode(strip_tags($event['description'] ?? ''))
            . '&location=' . urlencode($event['location'] ?? '');
    }

    private static function formatDate(string $datetime): string
    {
        return gmdate('Ymd\THis\Z', strtotime($datetime));
    }

    private static function escape(string $text): string
    {
        return str_replace(["\n", "\r", ",", ";", "\\"], ["\\n", "", "\\,", "\\;", "\\\\"], $text);
    }

    private static function slugify(string $text): string
    {
        return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $text), '-'));
    }
}
