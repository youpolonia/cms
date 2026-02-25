<?php
declare(strict_types=1);

class BookingCalendar
{
    /**
     * Get available time slots for a given date, service, and optional staff.
     */
    public static function getAvailableSlots(string $date, int $serviceId, ?int $staffId = null): array
    {
        $service = \BookingService::get($serviceId);
        if (!$service || $service['status'] !== 'active') return [];

        $duration = (int)$service['duration_minutes'];
        $buffer = (int)$service['buffer_minutes'];
        $maxPerSlot = (int)$service['max_bookings_per_slot'];

        // Get business hours for this day
        $dayOfWeek = strtolower(date('D', strtotime($date))); // mon, tue, etc
        $hours = self::getBusinessHours($dayOfWeek);
        if (empty($hours)) return []; // Closed

        $openTime = $hours[0];
        $closeTime = $hours[1];

        // Get slot interval
        $interval = (int)self::getSetting('slot_interval', '30');

        // Get existing appointments
        $existing = self::getBookedSlots($date, $serviceId, $staffId);

        // Check staff schedule if specific staff requested
        if ($staffId) {
            $staff = \BookingStaff::get($staffId);
            if ($staff && !empty($staff['schedule'][$dayOfWeek])) {
                $staffHours = $staff['schedule'][$dayOfWeek];
                if (is_array($staffHours) && count($staffHours) === 2) {
                    // Narrow to staff availability
                    if ($staffHours[0] > $openTime) $openTime = $staffHours[0];
                    if ($staffHours[1] < $closeTime) $closeTime = $staffHours[1];
                }
            } elseif ($staff && isset($staff['schedule'][$dayOfWeek]) && empty($staff['schedule'][$dayOfWeek])) {
                return []; // Staff not available this day
            }
        }

        // Min advance check
        $minAdvanceHours = (int)self::getSetting('min_advance_hours', '2');
        $now = new \DateTime('now');
        $dateObj = new \DateTime($date);

        // Generate slots
        $slots = [];
        $current = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $openTime);
        $end = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $closeTime);

        if (!$current || !$end) return [];

        while ($current < $end) {
            $slotStart = $current->format('H:i');
            $slotEnd = (clone $current)->modify("+{$duration} minutes")->format('H:i');

            // Check if slot end exceeds closing
            if ($slotEnd > $closeTime) break;

            // Check minimum advance time
            $slotDateTime = clone $current;
            $diff = $now->diff($slotDateTime);
            $hoursUntil = ($diff->invert ? -1 : 1) * ($diff->h + $diff->days * 24);

            $available = true;
            $bookedCount = 0;

            // Check conflicts with existing appointments
            foreach ($existing as $appt) {
                if (self::timesOverlap($slotStart, $slotEnd, $appt['start_time'], $appt['end_time'], $buffer)) {
                    $bookedCount++;
                }
            }

            if ($bookedCount >= $maxPerSlot) $available = false;
            if ($hoursUntil < $minAdvanceHours) $available = false;

            $slots[] = [
                'start'     => $slotStart,
                'end'       => $slotEnd,
                'available' => $available,
                'booked'    => $bookedCount,
                'max'       => $maxPerSlot,
            ];

            $current->modify("+{$interval} minutes");
        }

        return $slots;
    }

    /**
     * Check if a specific time slot is available.
     */
    public static function isSlotAvailable(string $date, string $startTime, int $serviceId, ?int $staffId = null): bool
    {
        $slots = self::getAvailableSlots($date, $serviceId, $staffId);
        foreach ($slots as $slot) {
            if ($slot['start'] === $startTime && $slot['available']) return true;
        }
        return false;
    }

    /**
     * Get calendar data for a month (for calendar view).
     */
    public static function getMonthData(int $year, int $month): array
    {
        $pdo = db();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT date, COUNT(*) AS count, status
            FROM booking_appointments
            WHERE date BETWEEN ? AND ? AND status NOT IN ('cancelled')
            GROUP BY date, status
        ");
        $stmt->execute([$startDate, $endDate]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $days = [];
        foreach ($rows as $r) {
            $d = $r['date'];
            if (!isset($days[$d])) $days[$d] = ['total' => 0, 'pending' => 0, 'confirmed' => 0];
            $days[$d]['total'] += (int)$r['count'];
            $days[$d][$r['status']] = (int)$r['count'];
        }

        return $days;
    }

    /**
     * Get week view data.
     */
    public static function getWeekData(string $startDate): array
    {
        $pdo = db();
        $endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));

        $stmt = $pdo->prepare("
            SELECT a.*, s.name AS service_name, s.color AS service_color, s.duration_minutes,
                   st.name AS staff_name
            FROM booking_appointments a
            LEFT JOIN booking_services s ON a.service_id = s.id
            LEFT JOIN booking_staff st ON a.staff_id = st.id
            WHERE a.date BETWEEN ? AND ? AND a.status NOT IN ('cancelled')
            ORDER BY a.date ASC, a.start_time ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $d = date('Y-m-d', strtotime($startDate . " +{$i} days"));
            $week[$d] = [];
        }
        foreach ($appointments as $a) {
            $week[$a['date']][] = $a;
        }

        return $week;
    }

    // ─── Helpers ───

    private static function getBookedSlots(string $date, int $serviceId, ?int $staffId): array
    {
        $sql = "SELECT start_time, end_time FROM booking_appointments WHERE date = ? AND status NOT IN ('cancelled')";
        $params = [$date];

        if ($staffId) {
            $sql .= " AND (staff_id = ? OR service_id = ?)";
            $params[] = $staffId;
            $params[] = $serviceId;
        } else {
            $sql .= " AND service_id = ?";
            $params[] = $serviceId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private static function timesOverlap(string $s1, string $e1, string $s2, string $e2, int $buffer = 0): bool
    {
        $toMin = fn($t) => (int)substr($t, 0, 2) * 60 + (int)substr($t, 3, 2);
        $start1 = $toMin($s1);
        $end1 = $toMin($e1) + $buffer;
        $start2 = $toMin($s2) - $buffer;
        $end2 = $toMin($e2);
        return $start1 < $end2 && $start2 < $end1;
    }

    private static function getBusinessHours(string $day): array
    {
        $hours = json_decode(self::getSetting('business_hours', '{}'), true);
        return $hours[$day] ?? [];
    }

    public static function getSetting(string $key, string $default = ''): string
    {
        static $cache = [];
        if (isset($cache[$key])) return $cache[$key];
        $stmt = db()->prepare("SELECT `value` FROM booking_settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        $cache[$key] = $val !== false ? $val : $default;
        return $cache[$key];
    }
}
