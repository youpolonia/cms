<?php
declare(strict_types=1);

class BookingNotifications
{
    /**
     * Send booking confirmation to customer.
     */
    public static function sendConfirmation(int $appointmentId): bool
    {
        $appt = \BookingAppointment::get($appointmentId);
        if (!$appt || empty($appt['customer_email'])) return false;

        $businessName = \BookingCalendar::getSetting('business_name', 'Our Business');
        $date = date('l, F j, Y', strtotime($appt['date']));
        $time = date('g:i A', strtotime($appt['start_time'])) . ' – ' . date('g:i A', strtotime($appt['end_time']));

        $subject = "Booking Confirmation — {$appt['service_name']}";
        $body = "Hello {$appt['customer_name']},\n\n"
            . "Your appointment has been confirmed!\n\n"
            . "📋 Service: {$appt['service_name']}\n"
            . "📅 Date: {$date}\n"
            . "🕐 Time: {$time}\n"
            . ($appt['staff_name'] ? "👤 With: {$appt['staff_name']}\n" : '')
            . ($appt['price_paid'] > 0 ? "💰 Price: \${$appt['price_paid']}\n" : '')
            . "\nIf you need to cancel or reschedule, please contact us.\n\n"
            . "Thank you,\n{$businessName}";

        return self::send($appt['customer_email'], $subject, $body);
    }

    /**
     * Notify admin/staff about new booking.
     */
    public static function notifyStaff(int $appointmentId): bool
    {
        $appt = \BookingAppointment::get($appointmentId);
        if (!$appt) return false;

        $emails = [];
        $notifyEmail = \BookingCalendar::getSetting('notification_email', '');
        if ($notifyEmail) $emails[] = $notifyEmail;
        if (!empty($appt['staff_email'])) $emails[] = $appt['staff_email'];
        if (empty($emails)) return false;

        $date = date('l, F j, Y', strtotime($appt['date']));
        $time = date('g:i A', strtotime($appt['start_time']));

        $subject = "New Booking: {$appt['customer_name']} — {$appt['service_name']}";
        $body = "New appointment booked:\n\n"
            . "Customer: {$appt['customer_name']}\n"
            . "Email: {$appt['customer_email']}\n"
            . "Phone: {$appt['customer_phone']}\n"
            . "Service: {$appt['service_name']}\n"
            . "Date: {$date}\n"
            . "Time: {$time}\n"
            . "Status: {$appt['status']}\n"
            . ($appt['notes'] ? "Notes: {$appt['notes']}\n" : '');

        $sent = false;
        foreach (array_unique($emails) as $email) {
            if (self::send($email, $subject, $body)) $sent = true;
        }
        return $sent;
    }

    /**
     * Send reminder email (call from cron/scheduler).
     */
    public static function sendReminders(): array
    {
        $hours = (int)\BookingCalendar::getSetting('reminder_hours', '24');
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT a.*, s.name AS service_name
            FROM booking_appointments a
            LEFT JOIN booking_services s ON a.service_id = s.id
            WHERE a.status IN ('confirmed','pending')
              AND a.reminder_sent = 0
              AND a.customer_email != ''
              AND CONCAT(a.date, ' ', a.start_time) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? HOUR)
        ");
        $stmt->execute([$hours]);
        $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sent = 0;
        $businessName = \BookingCalendar::getSetting('business_name', 'Our Business');

        foreach ($appointments as $appt) {
            $date = date('l, F j', strtotime($appt['date']));
            $time = date('g:i A', strtotime($appt['start_time']));

            $subject = "Reminder: Your appointment tomorrow — {$appt['service_name']}";
            $body = "Hello {$appt['customer_name']},\n\n"
                . "This is a friendly reminder about your upcoming appointment:\n\n"
                . "📋 {$appt['service_name']}\n"
                . "📅 {$date} at {$time}\n\n"
                . "We look forward to seeing you!\n\n{$businessName}";

            if (self::send($appt['customer_email'], $subject, $body)) {
                $pdo->prepare("UPDATE booking_appointments SET reminder_sent = 1 WHERE id = ?")->execute([$appt['id']]);
                $sent++;
            }
        }

        return ['sent' => $sent, 'total' => count($appointments)];
    }

    /**
     * Send cancellation notice.
     */
    public static function sendCancellation(int $appointmentId): bool
    {
        $appt = \BookingAppointment::get($appointmentId);
        if (!$appt || empty($appt['customer_email'])) return false;

        $businessName = \BookingCalendar::getSetting('business_name', 'Our Business');
        $date = date('l, F j, Y', strtotime($appt['date']));
        $time = date('g:i A', strtotime($appt['start_time']));

        $subject = "Appointment Cancelled — {$appt['service_name']}";
        $body = "Hello {$appt['customer_name']},\n\n"
            . "Your appointment has been cancelled:\n\n"
            . "📋 {$appt['service_name']}\n"
            . "📅 {$date} at {$time}\n\n"
            . "If you'd like to rebook, please visit our booking page.\n\n"
            . "Thank you,\n{$businessName}";

        return self::send($appt['customer_email'], $subject, $body);
    }

    private static function send(string $to, string $subject, string $body): bool
    {
        // Use CMS mailer if available
        if (function_exists('cms_send_email')) {
            return cms_send_email($to, $subject, $body);
        }

        $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8";
        return @mail($to, $subject, $body, $headers);
    }
}
