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

// ─── SETTINGS ───
if ($uri === '/admin/membership/settings') {
    require $pluginDir . '/views/admin/settings.php';
    exit;
}

if ($uri === '/admin/membership/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `membership_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fields = ['trial_days','grace_period_days','auto_renew','signup_redirect','expiry_reminder_days','welcome_email','content_restriction_msg'];
    $checkboxes = ['auto_renew','welcome_email'];
    foreach ($fields as $f) {
        $val = in_array($f, $checkboxes) ? (isset($_POST[$f]) ? '1' : '0') : ($_POST[$f] ?? '');
        $pdo->prepare("INSERT INTO `membership_settings` (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$f, $val, $val]);
    }
    \Core\Session::flash('success', 'Settings saved!');
    \Core\Response::redirect('/admin/membership/settings?saved=1');
}

