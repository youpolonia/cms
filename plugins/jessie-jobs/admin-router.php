<?php
/**
 * Jessie Jobs — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-jobs';
require_once $pluginDir . '/includes/class-job-listing.php';
require_once $pluginDir . '/includes/class-job-application.php';
require_once $pluginDir . '/includes/class-job-company.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/admin/jobs' || $uri === '/admin/jobs/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── JOB LISTINGS ───
if ($uri === '/admin/jobs/listings') { require $pluginDir . '/views/admin/listings.php'; exit; }
if ($uri === '/admin/jobs/listings/create') { $job = null; require $pluginDir . '/views/admin/job-form.php'; exit; }

if (preg_match('#^/admin/jobs/listings/(\d+)/edit$#', $uri, $m)) {
    $job = \JobListing::get((int)$m[1]);
    if (!$job) { \Core\Session::flash('error', 'Not found.'); \Core\Response::redirect('/admin/jobs/listings'); }
    require $pluginDir . '/views/admin/job-form.php'; exit;
}

if ($uri === '/admin/jobs/listings/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $id = \JobListing::create($data);
    \Core\Session::flash('success', 'Job created.'); \Core\Response::redirect('/admin/jobs/listings/' . $id . '/edit');
}

if (preg_match('#^/admin/jobs/listings/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \JobListing::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Job updated.'); \Core\Response::redirect('/admin/jobs/listings/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/jobs/listings/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \JobListing::delete((int)$m[1]);
    \Core\Session::flash('success', 'Job deleted.'); \Core\Response::redirect('/admin/jobs/listings');
}

// ─── APPLICATIONS ───
if ($uri === '/admin/jobs/applications') { require $pluginDir . '/views/admin/applications.php'; exit; }

// ─── COMPANIES ───
if ($uri === '/admin/jobs/companies') { require $pluginDir . '/views/admin/companies.php'; exit; }

if ($uri === '/admin/jobs/companies/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \JobCompany::create($data);
    \Core\Session::flash('success', 'Company created.'); \Core\Response::redirect('/admin/jobs/companies');
}

if (preg_match('#^/admin/jobs/companies/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \JobCompany::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Company updated.'); \Core\Response::redirect('/admin/jobs/companies');
}

if (preg_match('#^/admin/jobs/companies/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \JobCompany::delete((int)$m[1]);
    \Core\Session::flash('success', 'Company deleted.'); \Core\Response::redirect('/admin/jobs/companies');
}

// ─── SETTINGS ───
if ($uri === '/admin/jobs/settings') {
    require $pluginDir . '/views/admin/settings.php';
    exit;
}

if ($uri === '/admin/jobs/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `jobs_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fields = ['application_mode','resume_upload','moderation','items_per_page','expiry_days','salary_display','alert_email','categories'];
    $checkboxes = ['resume_upload','salary_display'];
    foreach ($fields as $f) {
        $val = in_array($f, $checkboxes) ? (isset($_POST[$f]) ? '1' : '0') : ($_POST[$f] ?? '');
        $pdo->prepare("INSERT INTO `jobs_settings` (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$f, $val, $val]);
    }
    \Core\Session::flash('success', 'Settings saved!');
    \Core\Response::redirect('/admin/jobs/settings?saved=1');
}

