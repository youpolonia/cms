<?php
/**
 * Jessie Booking — API Router
 * Handles /api/booking/* requests
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'] ?? '';
$uri = strtok($uri, '?');

// Load includes
require_once $pluginDir . '/includes/class-booking-service.php';
require_once $pluginDir . '/includes/class-booking-staff.php';
require_once $pluginDir . '/includes/class-booking-appointment.php';
require_once $pluginDir . '/includes/class-booking-calendar.php';
require_once $pluginDir . '/includes/class-booking-notifications.php';
require_once $pluginDir . '/includes/class-booking-ai.php';

header('Content-Type: application/json');

// Auth check for admin endpoints
$isAdmin = false;
if (function_exists('\\Core\\Session::isLoggedIn')) {
    $isAdmin = \Core\Session::isLoggedIn() && (\Core\Session::get('role') === 'admin');
} elseif (isset($_SESSION['user_role'])) {
    $isAdmin = $_SESSION['user_role'] === 'admin';
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Route matching
if (preg_match('#^/api/booking/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC: Available slots ───
        case 'availability':
            $date = $_GET['date'] ?? $input['date'] ?? date('Y-m-d');
            $serviceId = (int)($_GET['service_id'] ?? $input['service_id'] ?? 0);
            $staffId = !empty($_GET['staff_id']) ? (int)$_GET['staff_id'] : null;

            if (!$serviceId) { echo json_encode(['ok' => false, 'error' => 'service_id required']); exit; }
            $slots = \BookingCalendar::getAvailableSlots($date, $serviceId, $staffId);
            echo json_encode(['ok' => true, 'date' => $date, 'slots' => $slots]);
            exit;

        // ─── PUBLIC: Book appointment ───
        case 'book':
            if ($method !== 'POST') { echo json_encode(['ok' => false, 'error' => 'POST required']); exit; }

            $serviceId = (int)($input['service_id'] ?? 0);
            $date = $input['date'] ?? '';
            $startTime = $input['start_time'] ?? '';
            $name = trim($input['customer_name'] ?? '');

            if (!$serviceId || !$date || !$startTime || !$name) {
                echo json_encode(['ok' => false, 'error' => 'service_id, date, start_time, customer_name required']);
                exit;
            }

            // Verify slot is available
            $staffId = !empty($input['staff_id']) ? (int)$input['staff_id'] : null;
            if (!\BookingCalendar::isSlotAvailable($date, $startTime, $serviceId, $staffId)) {
                echo json_encode(['ok' => false, 'error' => 'This time slot is no longer available']);
                exit;
            }

            $service = \BookingService::get($serviceId);
            $endTime = date('H:i', strtotime($startTime) + ($service['duration_minutes'] ?? 60) * 60);
            $autoConfirm = \BookingCalendar::getSetting('auto_confirm', '0') === '1';

            $apptId = \BookingAppointment::create([
                'service_id'     => $serviceId,
                'staff_id'       => $staffId,
                'customer_name'  => $name,
                'customer_email' => $input['customer_email'] ?? '',
                'customer_phone' => $input['customer_phone'] ?? '',
                'date'           => $date,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'status'         => $autoConfirm ? 'confirmed' : 'pending',
                'notes'          => $input['notes'] ?? '',
                'price_paid'     => (float)($service['price'] ?? 0),
                'source'         => 'widget',
            ]);

            // Send notifications
            \BookingNotifications::sendConfirmation($apptId);
            \BookingNotifications::notifyStaff($apptId);

            // CRM integration
            if (function_exists('cms_event')) {
                cms_event('booking.created', [
                    'appointment_id' => $apptId,
                    'customer_name'  => $name,
                    'customer_email' => $input['customer_email'] ?? '',
                    'service'        => $service['name'],
                ]);
            }

            echo json_encode(['ok' => true, 'appointment_id' => $apptId, 'status' => $autoConfirm ? 'confirmed' : 'pending']);
            exit;

        // ─── PUBLIC: Services list ───
        case 'services':
            if ($method === 'GET') {
                $services = \BookingService::getAll('active');
                echo json_encode(['ok' => true, 'services' => $services]);
                exit;
            }
            break;

        // ─── PUBLIC: Staff for service ───
        case 'staff':
            if ($method === 'GET') {
                $serviceId = (int)($_GET['service_id'] ?? 0);
                $staff = $serviceId ? array_values(\BookingStaff::getForService($serviceId)) : \BookingStaff::getAll('active');
                echo json_encode(['ok' => true, 'staff' => $staff]);
                exit;
            }
            break;

        // ─── ADMIN ONLY below ───
        case 'appointments':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            if ($method === 'GET') {
                if ($id) {
                    $appt = \BookingAppointment::get($id);
                    echo json_encode(['ok' => (bool)$appt, 'appointment' => $appt]);
                } else {
                    $result = \BookingAppointment::getAll($_GET);
                    echo json_encode(['ok' => true] + $result);
                }
                exit;
            }
            if ($method === 'POST' && $id) {
                // Update status
                $status = $input['status'] ?? '';
                if ($status) {
                    \BookingAppointment::update($id, ['status' => $status]);
                    if ($status === 'confirmed') \BookingNotifications::sendConfirmation($id);
                    if ($status === 'cancelled') \BookingNotifications::sendCancellation($id);
                }
                echo json_encode(['ok' => true]);
                exit;
            }
            break;

        case 'calendar':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $year = (int)($_GET['year'] ?? date('Y'));
            $month = (int)($_GET['month'] ?? date('m'));
            $data = \BookingCalendar::getMonthData($year, $month);
            echo json_encode(['ok' => true, 'month_data' => $data]);
            exit;

        case 'week':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $start = $_GET['start'] ?? date('Y-m-d', strtotime('monday this week'));
            $data = \BookingCalendar::getWeekData($start);
            echo json_encode(['ok' => true, 'week_data' => $data]);
            exit;

        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(['ok' => true, 'stats' => \BookingAppointment::getStats()]);
            exit;

        // ─── AI endpoints ───
        case 'ai-description':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $result = \BookingAI::generateServiceDescription($input['name'] ?? '', $input['category'] ?? '', $input['language'] ?? 'en');
            echo json_encode($result);
            exit;

        case 'ai-suggest':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $result = \BookingAI::suggestOptimalSlots((int)($input['service_id'] ?? 0));
            echo json_encode($result);
            exit;

        case 'ai-followup':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $result = \BookingAI::generateFollowUp((int)($input['appointment_id'] ?? $id ?? 0), $input['language'] ?? 'en');
            echo json_encode($result);
            exit;

        case 'reminders':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $result = \BookingNotifications::sendReminders();
            echo json_encode(['ok' => true] + $result);
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
