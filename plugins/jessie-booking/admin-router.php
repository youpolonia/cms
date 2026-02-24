<?php
/**
 * Jessie Booking — Admin Router
 * Handles /admin/booking/* requests
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-booking';
require_once $pluginDir . '/includes/class-booking-service.php';
require_once $pluginDir . '/includes/class-booking-staff.php';
require_once $pluginDir . '/includes/class-booking-appointment.php';
require_once $pluginDir . '/includes/class-booking-calendar.php';
require_once $pluginDir . '/includes/class-booking-notifications.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

// Route matching
if ($uri === '/admin/booking' || $uri === '/admin/booking/') {
    require $pluginDir . '/views/admin/dashboard.php';
    exit;
}

if ($uri === '/admin/booking/services') {
    require $pluginDir . '/views/admin/services.php';
    exit;
}

if ($uri === '/admin/booking/services/create') {
    $service = null;
    require $pluginDir . '/views/admin/service-form.php';
    exit;
}

if (preg_match('#^/admin/booking/services/(\d+)/edit$#', $uri, $m)) {
    $service = \BookingService::get((int)$m[1]);
    if (!$service) { \Core\Session::flash('error', 'Service not found.'); \Core\Response::redirect('/admin/booking/services'); }
    require $pluginDir . '/views/admin/service-form.php';
    exit;
}

if ($uri === '/admin/booking/services/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST;
    unset($data['csrf_token']);
    \BookingService::create($data);
    \Core\Session::flash('success', 'Service created.');
    \Core\Response::redirect('/admin/booking/services');
}

if (preg_match('#^/admin/booking/services/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST;
    unset($data['csrf_token']);
    \BookingService::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Service updated.');
    \Core\Response::redirect('/admin/booking/services/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/booking/services/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \BookingService::delete((int)$m[1]);
    \Core\Session::flash('success', 'Service deleted.');
    \Core\Response::redirect('/admin/booking/services');
}

if ($uri === '/admin/booking/staff') {
    require $pluginDir . '/views/admin/staff.php';
    exit;
}

if ($uri === '/admin/booking/staff/create') {
    $staffMember = null;
    require $pluginDir . '/views/admin/staff-form.php';
    exit;
}

if (preg_match('#^/admin/booking/staff/(\d+)/edit$#', $uri, $m)) {
    $staffMember = \BookingStaff::get((int)$m[1]);
    if (!$staffMember) { \Core\Session::flash('error', 'Staff not found.'); \Core\Response::redirect('/admin/booking/staff'); }
    require $pluginDir . '/views/admin/staff-form.php';
    exit;
}

if ($uri === '/admin/booking/staff/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST;
    unset($data['csrf_token']);
    if (isset($data['services'])) $data['services'] = array_map('intval', $data['services']);
    \BookingStaff::create($data);
    \Core\Session::flash('success', 'Staff member added.');
    \Core\Response::redirect('/admin/booking/staff');
}

if (preg_match('#^/admin/booking/staff/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST;
    unset($data['csrf_token']);
    if (isset($data['services'])) $data['services'] = array_map('intval', $data['services']);
    else $data['services'] = [];
    \BookingStaff::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Staff member updated.');
    \Core\Response::redirect('/admin/booking/staff/' . $m[1] . '/edit');
}

if ($uri === '/admin/booking/appointments') {
    require $pluginDir . '/views/admin/appointments.php';
    exit;
}

if ($uri === '/admin/booking/calendar') {
    require $pluginDir . '/views/admin/calendar.php';
    exit;
}

if ($uri === '/admin/booking/settings') {
    require $pluginDir . '/views/admin/settings.php';
    exit;
}

if ($uri === '/admin/booking/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $fields = ['business_name','notification_email','slot_interval','min_advance_hours','max_advance_days','reminder_hours','auto_confirm'];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '0';
        $pdo->prepare("INSERT INTO booking_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$f, $val, $val]);
    }
    // Business hours
    $hours = [];
    foreach (['mon','tue','wed','thu','fri','sat','sun'] as $day) {
        $open = $_POST["hours_{$day}_open"] ?? '';
        $close = $_POST["hours_{$day}_close"] ?? '';
        $hours[$day] = ($open && $close) ? [$open, $close] : [];
    }
    $pdo->prepare("INSERT INTO booking_settings (`key`, `value`) VALUES ('business_hours', ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([json_encode($hours), json_encode($hours)]);

    \Core\Session::flash('success', 'Settings saved.');
    \Core\Response::redirect('/admin/booking/settings');
}

// Booking widget (frontend)
if ($uri === '/booking' || $uri === '/booking/') {
    require $pluginDir . '/views/frontend/booking-widget.php';
    exit;
}
