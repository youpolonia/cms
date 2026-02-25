<?php
declare(strict_types=1);

class BookingAppointment
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['date'])) { $where[] = 'a.date = ?'; $params[] = $filters['date']; }
        if (!empty($filters['date_from'])) { $where[] = 'a.date >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where[] = 'a.date <= ?'; $params[] = $filters['date_to']; }
        if (!empty($filters['status'])) { $where[] = 'a.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['service_id'])) { $where[] = 'a.service_id = ?'; $params[] = (int)$filters['service_id']; }
        if (!empty($filters['staff_id'])) { $where[] = 'a.staff_id = ?'; $params[] = (int)$filters['staff_id']; }
        if (!empty($filters['search'])) {
            $where[] = '(a.customer_name LIKE ? OR a.customer_email LIKE ? OR a.customer_phone LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }

        $whereStr = implode(' AND ', $where);
        $total = (int)$pdo->prepare("SELECT COUNT(*) FROM booking_appointments a WHERE {$whereStr}")->execute($params) ?
            $pdo->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;

        // Recount properly
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM booking_appointments a WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT a.*, s.name AS service_name, s.color AS service_color, s.duration_minutes,
                   st.name AS staff_name
            FROM booking_appointments a
            LEFT JOIN booking_services s ON a.service_id = s.id
            LEFT JOIN booking_staff st ON a.staff_id = st.id
            WHERE {$whereStr}
            ORDER BY a.date DESC, a.start_time ASC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'appointments' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total, 'page' => $page, 'totalPages' => $totalPages,
        ];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("
            SELECT a.*, s.name AS service_name, s.color AS service_color, s.duration_minutes, s.price AS service_price,
                   st.name AS staff_name, st.email AS staff_email
            FROM booking_appointments a
            LEFT JOIN booking_services s ON a.service_id = s.id
            LEFT JOIN booking_staff st ON a.staff_id = st.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getForDate(string $date, ?int $staffId = null): array
    {
        $sql = "SELECT a.*, s.name AS service_name, s.color AS service_color FROM booking_appointments a LEFT JOIN booking_services s ON a.service_id = s.id WHERE a.date = ? AND a.status NOT IN ('cancelled')";
        $params = [$date];
        if ($staffId) { $sql .= " AND a.staff_id = ?"; $params[] = $staffId; }
        $sql .= " ORDER BY a.start_time ASC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO booking_appointments (service_id, staff_id, customer_name, customer_email, customer_phone, date, start_time, end_time, status, notes, price_paid, payment_status, source) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)$data['service_id'], !empty($data['staff_id']) ? (int)$data['staff_id'] : null,
            $data['customer_name'], $data['customer_email'] ?? '', $data['customer_phone'] ?? '',
            $data['date'], $data['start_time'], $data['end_time'],
            $data['status'] ?? 'pending', $data['notes'] ?? '',
            (float)($data['price_paid'] ?? 0), $data['payment_status'] ?? 'none',
            $data['source'] ?? 'widget',
        ]);
        $id = (int)$pdo->lastInsertId();

        if (function_exists('cms_event')) {
            cms_event('booking.created', ['appointment_id' => $id, 'service_id' => (int)$data['service_id'], 'customer_email' => $data['customer_email'] ?? '']);
        }

        return $id;
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $allowed = ['service_id','staff_id','customer_name','customer_email','customer_phone','date','start_time','end_time','status','notes','price_paid','payment_status','reminder_sent'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "`{$f}` = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $result = db()->prepare("UPDATE booking_appointments SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);

        if ($result && isset($data['status'])) {
            if (function_exists('cms_event')) {
                cms_event('booking.' . $data['status'], ['appointment_id' => $id, 'status' => $data['status']]);
            }
        }

        return $result;
    }

    public static function delete(int $id): bool
    {
        return db()->prepare("DELETE FROM booking_appointments WHERE id = ?")->execute([$id]);
    }

    public static function getTodayCount(): int
    {
        return (int)db()->query("SELECT COUNT(*) FROM booking_appointments WHERE date = CURDATE() AND status NOT IN ('cancelled')")->fetchColumn();
    }

    public static function getUpcoming(int $limit = 10): array
    {
        $stmt = db()->prepare("
            SELECT a.*, s.name AS service_name, s.color AS service_color, st.name AS staff_name
            FROM booking_appointments a
            LEFT JOIN booking_services s ON a.service_id = s.id
            LEFT JOIN booking_staff st ON a.staff_id = st.id
            WHERE a.date >= CURDATE() AND a.status IN ('pending','confirmed')
            ORDER BY a.date ASC, a.start_time ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Customer History ──
    public static function getByCustomerEmail(string $email): array
    {
        $stmt = db()->prepare("SELECT a.*, s.name AS service_name, st.name AS staff_name FROM booking_appointments a LEFT JOIN booking_services s ON a.service_id = s.id LEFT JOIN booking_staff st ON a.staff_id = st.id WHERE a.customer_email = ? ORDER BY a.date DESC, a.start_time DESC");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getCustomerStats(string $email): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(status='completed') as completed, SUM(status='cancelled') as cancelled, SUM(status='no_show') as no_shows, COALESCE(SUM(price_paid),0) as total_spent, MIN(date) as first_visit, MAX(date) as last_visit FROM booking_appointments WHERE customer_email = ?");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // ── Recurring Appointments ──
    public static function createRecurring(array $data, string $frequency, int $count): array
    {
        $ids = [];
        $baseDate = $data['date'] ?? date('Y-m-d');
        $intervals = ['daily' => '+1 day', 'weekly' => '+1 week', 'biweekly' => '+2 weeks', 'monthly' => '+1 month'];
        $interval = $intervals[$frequency] ?? '+1 week';

        for ($i = 0; $i < $count; $i++) {
            $data['date'] = date('Y-m-d', strtotime($baseDate . ' ' . str_replace('+', '+' . $i . ' ', $interval)));
            if ($i > 0) $data['date'] = date('Y-m-d', strtotime($baseDate . ' +' . $i . ' ' . ltrim($interval, '+')));
            $ids[] = self::create($data);
        }
        return $ids;
    }

    // ── Reschedule ──
    public static function reschedule(int $id, string $newDate, string $newStartTime, ?string $newEndTime = null): bool
    {
        $pdo = db();
        $appt = self::get($id);
        if (!$appt || in_array($appt['status'], ['completed', 'cancelled'])) return false;
        $endTime = $newEndTime ?: date('H:i', strtotime($newStartTime) + (strtotime($appt['end_time']) - strtotime($appt['start_time'])));
        $stmt = $pdo->prepare("UPDATE booking_appointments SET date = ?, start_time = ?, end_time = ?, status = 'confirmed' WHERE id = ?");
        $stmt->execute([$newDate, $newStartTime, $endTime, $id]);
        return true;
    }

    // ── Waitlist ──
    public static function getWaitlist(string $date, int $serviceId): array
    {
        $stmt = db()->prepare("SELECT * FROM booking_appointments WHERE date = ? AND service_id = ? AND status = 'waitlisted' ORDER BY created_at ASC");
        $stmt->execute([$date, $serviceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Export ──
    public static function export(string $dateFrom, string $dateTo): array
    {
        $stmt = db()->prepare("SELECT a.*, s.name AS service_name, st.name AS staff_name FROM booking_appointments a LEFT JOIN booking_services s ON a.service_id = s.id LEFT JOIN booking_staff st ON a.staff_id = st.id WHERE a.date BETWEEN ? AND ? ORDER BY a.date, a.start_time");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getStats(): array
    {
        $pdo = db();
        return [
            'today'     => (int)$pdo->query("SELECT COUNT(*) FROM booking_appointments WHERE date = CURDATE() AND status NOT IN ('cancelled')")->fetchColumn(),
            'this_week' => (int)$pdo->query("SELECT COUNT(*) FROM booking_appointments WHERE date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status NOT IN ('cancelled')")->fetchColumn(),
            'pending'   => (int)$pdo->query("SELECT COUNT(*) FROM booking_appointments WHERE status = 'pending'")->fetchColumn(),
            'total'     => (int)$pdo->query("SELECT COUNT(*) FROM booking_appointments")->fetchColumn(),
            'revenue'   => (float)$pdo->query("SELECT COALESCE(SUM(price_paid),0) FROM booking_appointments WHERE payment_status = 'paid'")->fetchColumn(),
            'no_shows'  => (int)$pdo->query("SELECT COUNT(*) FROM booking_appointments WHERE status = 'no_show' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn(),
        ];
    }
}
