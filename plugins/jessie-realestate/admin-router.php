<?php
/**
 * Jessie Real Estate — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');
$pluginDir = CMS_ROOT . '/plugins/jessie-realestate';
require_once $pluginDir . '/includes/class-realestate-property.php';
require_once $pluginDir . '/includes/class-realestate-agent.php';
\Core\Session::requireRole('admin');
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

// Dashboard
if ($uri === '/admin/realestate' || $uri === '/admin/realestate/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── PROPERTIES ───
if ($uri === '/admin/realestate/properties') { require $pluginDir . '/views/admin/properties.php'; exit; }
if ($uri === '/admin/realestate/properties/create') { $property = null; require $pluginDir . '/views/admin/property-form.php'; exit; }
if (preg_match('#^/admin/realestate/properties/(\d+)/edit$#', $uri, $m)) {
    $property = \RealEstateProperty::get((int)$m[1]);
    if (!$property) { \Core\Session::flash('error', 'Property not found'); \Core\Response::redirect('/admin/realestate/properties'); }
    require $pluginDir . '/views/admin/property-form.php'; exit;
}
if ($uri === '/admin/realestate/properties/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    if (!empty($data['features']) && is_string($data['features'])) { $data['features'] = array_map('trim', explode(',', $data['features'])); }
    if (!empty($data['images']) && is_string($data['images'])) { $data['images'] = array_values(array_filter(array_map('trim', explode("\n", $data['images'])))); }
    $id = \RealEstateProperty::create($data);
    \Core\Session::flash('success', 'Property created.'); \Core\Response::redirect('/admin/realestate/properties/' . $id . '/edit');
}
if (preg_match('#^/admin/realestate/properties/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    if (!empty($data['features']) && is_string($data['features'])) { $data['features'] = array_map('trim', explode(',', $data['features'])); }
    if (!empty($data['images']) && is_string($data['images'])) { $data['images'] = array_values(array_filter(array_map('trim', explode("\n", $data['images'])))); }
    \RealEstateProperty::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Property updated.'); \Core\Response::redirect('/admin/realestate/properties/' . $m[1] . '/edit');
}
if (preg_match('#^/admin/realestate/properties/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \RealEstateProperty::delete((int)$m[1]);
    \Core\Session::flash('success', 'Property deleted.'); \Core\Response::redirect('/admin/realestate/properties');
}

// ─── AGENTS ───
if ($uri === '/admin/realestate/agents') { require $pluginDir . '/views/admin/agents.php'; exit; }
if ($uri === '/admin/realestate/agents/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \RealEstateAgent::create($data);
    \Core\Session::flash('success', 'Agent created.'); \Core\Response::redirect('/admin/realestate/agents');
}
if (preg_match('#^/admin/realestate/agents/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \RealEstateAgent::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Agent updated.'); \Core\Response::redirect('/admin/realestate/agents');
}
if (preg_match('#^/admin/realestate/agents/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \RealEstateAgent::delete((int)$m[1]);
    \Core\Session::flash('success', 'Agent deleted.'); \Core\Response::redirect('/admin/realestate/agents');
}

// ─── INQUIRIES ───
if ($uri === '/admin/realestate/inquiries') { require $pluginDir . '/views/admin/inquiries.php'; exit; }
if (preg_match('#^/admin/realestate/inquiries/(\d+)/status$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $pdo->prepare("UPDATE re_inquiries SET status = ? WHERE id = ?")->execute([$_POST['status'] ?? 'read', (int)$m[1]]);
    \Core\Session::flash('success', 'Inquiry updated.'); \Core\Response::redirect('/admin/realestate/inquiries');
}
if (preg_match('#^/admin/realestate/inquiries/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    db()->prepare("DELETE FROM re_inquiries WHERE id = ?")->execute([(int)$m[1]]);
    \Core\Session::flash('success', 'Inquiry deleted.'); \Core\Response::redirect('/admin/realestate/inquiries');
}
