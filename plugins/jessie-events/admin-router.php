<?php
/**
 * Jessie Events — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');
$pluginDir = CMS_ROOT . '/plugins/jessie-events';
require_once $pluginDir . '/includes/class-event-manager.php';
require_once $pluginDir . '/includes/class-event-ticket.php';
require_once $pluginDir . '/includes/class-event-order.php';
\Core\Session::requireRole('admin');
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

// Dashboard
if ($uri === '/admin/events' || $uri === '/admin/events/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── EVENTS ───
if ($uri === '/admin/events/list') { require $pluginDir . '/views/admin/events.php'; exit; }
if ($uri === '/admin/events/create') { $event = null; require $pluginDir . '/views/admin/event-form.php'; exit; }
if (preg_match('#^/admin/events/(\d+)/edit$#', $uri, $m)) {
    $event = \EventManager::get((int)$m[1]);
    if (!$event) { \Core\Session::flash('error', 'Not found'); \Core\Response::redirect('/admin/events/list'); }
    require $pluginDir . '/views/admin/event-form.php'; exit;
}
if ($uri === '/admin/events/store' && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    $id = \EventManager::create($data);
    \Core\Session::flash('success', 'Event created.'); \Core\Response::redirect('/admin/events/' . $id . '/edit');
}
if (preg_match('#^/admin/events/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    \EventManager::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Event updated.'); \Core\Response::redirect('/admin/events/' . $m[1] . '/edit');
}
if (preg_match('#^/admin/events/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); \EventManager::delete((int)$m[1]);
    \Core\Session::flash('success', 'Event deleted.'); \Core\Response::redirect('/admin/events/list');
}

// ─── TICKETS (inline per event) ───
if (preg_match('#^/admin/events/(\d+)/tickets$#', $uri, $m)) {
    $eventId = (int)$m[1]; $event = \EventManager::get($eventId);
    if (!$event) { \Core\Session::flash('error', 'Event not found'); \Core\Response::redirect('/admin/events/list'); }
    require $pluginDir . '/views/admin/tickets.php'; exit;
}
if (preg_match('#^/admin/events/(\d+)/tickets/store$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    $data['event_id'] = (int)$m[1];
    \EventTicket::create($data);
    \Core\Session::flash('success', 'Ticket created.'); \Core\Response::redirect('/admin/events/' . $m[1] . '/tickets');
}
if (preg_match('#^/admin/events/tickets/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    $ticket = \EventTicket::get((int)$m[1]);
    if ($ticket) {
        \EventTicket::update((int)$m[1], $data);
        \Core\Session::flash('success', 'Ticket updated.');
        \Core\Response::redirect('/admin/events/' . $ticket['event_id'] . '/tickets');
    }
    \Core\Response::redirect('/admin/events/list');
}
if (preg_match('#^/admin/events/tickets/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $ticket = \EventTicket::get((int)$m[1]);
    if ($ticket) {
        \EventTicket::delete((int)$m[1]);
        \Core\Session::flash('success', 'Ticket deleted.');
        \Core\Response::redirect('/admin/events/' . $ticket['event_id'] . '/tickets');
    }
    \Core\Response::redirect('/admin/events/list');
}

// ─── ORDERS ───
if ($uri === '/admin/events/orders') { require $pluginDir . '/views/admin/orders.php'; exit; }

// ─── SETTINGS ───
if ($uri === '/admin/events/settings') { require $pluginDir . '/views/admin/settings.php'; exit; }
if ($uri === '/admin/events/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    foreach ($data as $key => $value) \EventManager::setSetting($key, $value);
    \Core\Session::flash('success', 'Settings saved.'); \Core\Response::redirect('/admin/events/settings');
}
