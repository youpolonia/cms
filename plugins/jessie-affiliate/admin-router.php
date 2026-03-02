<?php
/**
 * Jessie Affiliate — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-affiliate';
require_once $pluginDir . '/includes/class-affiliate-program.php';
require_once $pluginDir . '/includes/class-affiliate.php';
require_once $pluginDir . '/includes/class-affiliate-ai.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

// ─── DASHBOARD ───
if ($uri === '/admin/affiliate' || $uri === '/admin/affiliate/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── PROGRAMS ───
if ($uri === '/admin/affiliate/programs') { require $pluginDir . '/views/admin/programs.php'; exit; }
if ($uri === '/admin/affiliate/programs/create') { $program = null; require $pluginDir . '/views/admin/program-form.php'; exit; }

if (preg_match('#^/admin/affiliate/programs/(\d+)/edit$#', $uri, $m)) {
    $program = \AffiliateProgram::get((int)$m[1]);
    if (!$program) { \Core\Session::flash('error', 'Program not found.'); \Core\Response::redirect('/admin/affiliate/programs'); }
    require $pluginDir . '/views/admin/program-form.php'; exit;
}

if ($uri === '/admin/affiliate/programs/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $id = \AffiliateProgram::create($data);
    \Core\Session::flash('success', 'Program created.'); \Core\Response::redirect('/admin/affiliate/programs/' . $id . '/edit');
}

if (preg_match('#^/admin/affiliate/programs/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \AffiliateProgram::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Program updated.'); \Core\Response::redirect('/admin/affiliate/programs/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/affiliate/programs/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \AffiliateProgram::delete((int)$m[1]);
    \Core\Session::flash('success', 'Program deleted.'); \Core\Response::redirect('/admin/affiliate/programs');
}

// ─── AFFILIATES ───
if ($uri === '/admin/affiliate/affiliates') { require $pluginDir . '/views/admin/affiliates.php'; exit; }

if (preg_match('#^/admin/affiliate/affiliates/(\d+)/approve$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::update((int)$m[1], ['status' => 'active']);
    \Core\Session::flash('success', 'Affiliate approved.'); \Core\Response::redirect('/admin/affiliate/affiliates');
}

if (preg_match('#^/admin/affiliate/affiliates/(\d+)/suspend$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::update((int)$m[1], ['status' => 'suspended']);
    \Core\Session::flash('success', 'Affiliate suspended.'); \Core\Response::redirect('/admin/affiliate/affiliates');
}

if (preg_match('#^/admin/affiliate/affiliates/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::delete((int)$m[1]);
    \Core\Session::flash('success', 'Affiliate deleted.'); \Core\Response::redirect('/admin/affiliate/affiliates');
}

// ─── CONVERSIONS ───
if ($uri === '/admin/affiliate/conversions') { require $pluginDir . '/views/admin/conversions.php'; exit; }

if (preg_match('#^/admin/affiliate/conversions/(\d+)/approve$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::approveConversion((int)$m[1]);
    \Core\Session::flash('success', 'Conversion approved.'); \Core\Response::redirect('/admin/affiliate/conversions');
}

if (preg_match('#^/admin/affiliate/conversions/(\d+)/reject$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::rejectConversion((int)$m[1]);
    \Core\Session::flash('success', 'Conversion rejected.'); \Core\Response::redirect('/admin/affiliate/conversions');
}

// ─── PAYOUTS ───
if ($uri === '/admin/affiliate/payouts') { require $pluginDir . '/views/admin/payouts.php'; exit; }

if ($uri === '/admin/affiliate/payouts/create' && $method === 'POST') {
    csrf_validate_or_403();
    $affiliateId = (int)($_POST['affiliate_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = ($_POST['payment_method'] ?? null) ?: '';
    $reference = ($_POST['payment_reference'] ?? null) ?: '';
    if ($affiliateId && $amount > 0) {
        \Affiliate::createPayout($affiliateId, $amount, $paymentMethod, $reference);
        \Core\Session::flash('success', 'Payout created.');
    } else {
        \Core\Session::flash('error', 'Invalid payout data.');
    }
    \Core\Response::redirect('/admin/affiliate/payouts');
}

if (preg_match('#^/admin/affiliate/payouts/(\d+)/complete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::completePayout((int)$m[1], ($_POST['payment_reference'] ?? null) ?: '');
    \Core\Session::flash('success', 'Payout marked as completed.'); \Core\Response::redirect('/admin/affiliate/payouts');
}

if (preg_match('#^/admin/affiliate/payouts/(\d+)/fail$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \Affiliate::failPayout((int)$m[1]);
    \Core\Session::flash('success', 'Payout marked as failed.'); \Core\Response::redirect('/admin/affiliate/payouts');
}

// ─── SETTINGS ───
if ($uri === '/admin/affiliate/settings') {
    require $pluginDir . '/views/admin/settings.php';
    exit;
}

if ($uri === '/admin/affiliate/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `affiliate_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fields = ['default_commission','cookie_days','min_payout','payout_schedule','auto_approve','self_referral','notification_email','terms_url'];
    $checkboxes = ['auto_approve','self_referral'];
    foreach ($fields as $f) {
        $val = in_array($f, $checkboxes) ? (isset($_POST[$f]) ? '1' : '0') : ($_POST[$f] ?? '');
        $pdo->prepare("INSERT INTO `affiliate_settings` (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$f, $val, $val]);
    }
    \Core\Session::flash('success', 'Settings saved!');
    \Core\Response::redirect('/admin/affiliate/settings?saved=1');
}

