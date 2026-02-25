<?php
declare(strict_types=1);

class BookingAI
{
    /**
     * Generate a professional service description.
     */
    public static function generateServiceDescription(string $serviceName, string $category = '', string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $prompt = "Write a professional, engaging service description for a booking service.\n\n"
            . "Service: {$serviceName}\n"
            . ($category ? "Category: {$category}\n" : '')
            . "Language: {$language}\n\n"
            . "Return JSON:\n"
            . '{"description": "2-3 paragraph description", "short_description": "1 sentence summary", "suggested_duration": 60, "suggested_price_range": "$50-$100"}'
            . "\n\nReturn ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 500, 'temperature' => 0.4]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Suggest optimal time slots based on booking patterns.
     */
    public static function suggestOptimalSlots(int $serviceId): array
    {
        $pdo = db();

        // Analyze booking patterns
        $stmt = $pdo->prepare("
            SELECT DAYNAME(date) AS day_name, HOUR(start_time) AS hour,
                   COUNT(*) AS bookings, AVG(CASE WHEN status='completed' THEN 1 WHEN status='no_show' THEN 0 ELSE 0.5 END) AS completion_rate
            FROM booking_appointments
            WHERE service_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY day_name, hour
            ORDER BY bookings DESC
        ");
        $stmt->execute([$serviceId]);
        $patterns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($patterns)) {
            return ['ok' => true, 'suggestions' => [], 'message' => 'Not enough booking data yet (need at least 90 days).'];
        }

        // Find peak hours and low hours
        $peak = array_slice($patterns, 0, 5);
        $low = array_filter($patterns, fn($p) => $p['bookings'] < 3);

        return [
            'ok' => true,
            'peak_times' => array_map(fn($p) => ['day' => $p['day_name'], 'hour' => $p['hour'] . ':00', 'bookings' => $p['bookings']], $peak),
            'low_times' => array_values(array_map(fn($p) => ['day' => $p['day_name'], 'hour' => $p['hour'] . ':00', 'bookings' => $p['bookings']], array_slice(array_values($low), 0, 5))),
            'recommendations' => [
                'Consider extending hours on ' . ($peak[0]['day_name'] ?? 'busy days'),
                count($low) > 5 ? 'Many empty slots — consider promotions for off-peak times' : 'Good utilization across time slots',
            ],
        ];
    }

    /**
     * Generate a follow-up email for a completed appointment.
     */
    public static function generateFollowUp(int $appointmentId, string $language = 'en'): array
    {
        $appt = \BookingAppointment::get($appointmentId);
        if (!$appt) return ['ok' => false, 'error' => 'Appointment not found'];

        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $businessName = \BookingCalendar::getSetting('business_name', 'Our Business');

        $prompt = "Write a warm, professional follow-up email after a service appointment.\n\n"
            . "Business: {$businessName}\n"
            . "Customer: {$appt['customer_name']}\n"
            . "Service: {$appt['service_name']}\n"
            . "Date: {$appt['date']}\n"
            . "Language: {$language}\n\n"
            . "Include: thank you, ask for feedback/review, mention rebooking.\n"
            . "Return JSON: {\"subject\": \"...\", \"body\": \"...\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 500, 'temperature' => 0.5]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'email' => $data] : ['ok' => false, 'error' => 'Failed to generate email'];
    }

    private static function parseJson(string $response): ?array
    {
        $response = trim($response);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
        $data = json_decode($response, true);
        if ($data) return $data;
        if (preg_match('/\{[\s\S]*\}/', $response, $m)) return json_decode($m[0], true);
        return null;
    }
}
