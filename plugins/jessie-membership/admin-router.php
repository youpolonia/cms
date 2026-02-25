<?php
/**
 * Jessie Membership — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-membership';
require_once $pluginDir . '/includes/class-membership-plan.php';
require_once $pluginDir . '/includes/class-membership-member.php';
require_once $pluginDir . '/includes/class-membership-access.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/admin/membership' || $uri === '/admin/membership/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── PLANS ───
if ($uri === '/admin/membership/plans') { require $pluginDir . '/views/admin/plans.php'; exit; }
if ($uri === '/admin/membership/plans/create') { $plan = null; require $pluginDir . '/views/admin/plan-form.php'; exit; }

if (preg_match('#^/admin/membership/plans/(\d+)/edit$#', $uri, $m)) {
    $plan = \MembershipPlan::get((int)$m[1]);
    if (!$plan) { \Core\Session::flash('error', 'Plan not found.'); \Core\Response::redirect('/admin/membership/plans'); }
    require $pluginDir . '/views/admin/plan-form.php'; exit;
}

if ($uri === '/admin/membership/plans/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    if (isset($data['features'])) $data['features'] = array_filter($data['features']);
    \MembershipPlan::create($data);
    \Core\Session::flash('success', 'Plan created.'); \Core\Response::redirect('/admin/membership/plans');
}

if (preg_match('#^/admin/membership/plans/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    if (isset($data['features'])) $data['features'] = array_filter($data['features']);
    \MembershipPlan::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Plan updated.'); \Core\Response::redirect('/admin/membership/plans/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/membership/plans/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \MembershipPlan::delete((int)$m[1]);
    \Core\Session::flash('success', 'Plan deleted.'); \Core\Response::redirect('/admin/membership/plans');
}

// ─── MEMBERS ───
if ($uri === '/admin/membership/members') { require $pluginDir . '/views/admin/members.php'; exit; }
if ($uri === '/admin/membership/members/add') { require $pluginDir . '/views/admin/member-form.php'; exit; }

if ($uri === '/admin/membership/members/store' && $method === 'POST') {
    csrf_validate_or_403();
    \MembershipMember::create(['email' => $_POST['email'] ?? '', 'name' => $_POST['name'] ?? '', 'plan_id' => (int)($_POST['plan_id'] ?? 0), 'notes' => $_POST['notes'] ?? '']);
    \Core\Session::flash('success', 'Member added.'); \Core\Response::redirect('/admin/membership/members');
}

if (preg_match('#^/admin/membership/members/(\d+)/cancel$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \MembershipMember::cancel((int)$m[1]);
    \Core\Session::flash('success', 'Membership cancelled.'); \Core\Response::redirect('/admin/membership/members');
}

// ─── CONTENT RULES ───
if ($uri === '/admin/membership/content') { require $pluginDir . '/views/admin/content.php'; exit; }

if ($uri === '/admin/membership/content/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \MembershipAccess::createRule($data);
    \Core\Session::flash('success', 'Rule created.'); \Core\Response::redirect('/admin/membership/content');
}

if (preg_match('#^/admin/membership/content/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \MembershipAccess::deleteRule((int)$m[1]);
    \Core\Session::flash('success', 'Rule deleted.'); \Core\Response::redirect('/admin/membership/content');
}

// Frontend
if ($uri === '/membership/pricing' || $uri === '/pricing') { require $pluginDir . '/views/frontend/pricing.php'; exit; }
