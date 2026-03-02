<?php
/**
 * Jessie Directory — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-directory';
require_once $pluginDir . '/includes/class-directory-listing.php';
require_once $pluginDir . '/includes/class-directory-category.php';
require_once $pluginDir . '/includes/class-directory-review.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/admin/directory' || $uri === '/admin/directory/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── LISTINGS ───
if ($uri === '/admin/directory/listings') { require $pluginDir . '/views/admin/listings.php'; exit; }
if ($uri === '/admin/directory/listings/create') { $listing = null; require $pluginDir . '/views/admin/listing-form.php'; exit; }

if (preg_match('#^/admin/directory/listings/(\d+)/edit$#', $uri, $m)) {
    $listing = \DirectoryListing::get((int)$m[1]);
    if (!$listing) { \Core\Session::flash('error', 'Not found.'); \Core\Response::redirect('/admin/directory/listings'); }
    require $pluginDir . '/views/admin/listing-form.php'; exit;
}

if ($uri === '/admin/directory/listings/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $id = \DirectoryListing::create($data);
    \Core\Session::flash('success', 'Listing created.'); \Core\Response::redirect('/admin/directory/listings/' . $id . '/edit');
}

if (preg_match('#^/admin/directory/listings/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \DirectoryListing::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Listing updated.'); \Core\Response::redirect('/admin/directory/listings/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/directory/listings/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \DirectoryListing::delete((int)$m[1]);
    \Core\Session::flash('success', 'Listing deleted.'); \Core\Response::redirect('/admin/directory/listings');
}

// ─── CATEGORIES ───
if ($uri === '/admin/directory/categories') { require $pluginDir . '/views/admin/categories.php'; exit; }

if ($uri === '/admin/directory/categories/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \DirectoryCategory::create($data);
    \Core\Session::flash('success', 'Category created.'); \Core\Response::redirect('/admin/directory/categories');
}

if (preg_match('#^/admin/directory/categories/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \DirectoryCategory::delete((int)$m[1]);
    \Core\Session::flash('success', 'Category deleted.'); \Core\Response::redirect('/admin/directory/categories');
}

// ─── CLAIMS ───
if ($uri === '/admin/directory/claims') { require $pluginDir . '/views/admin/claims.php'; exit; }

// ─── REVIEWS ───
if ($uri === '/admin/directory/reviews') { require $pluginDir . '/views/admin/reviews.php'; exit; }

// ─── SETTINGS ───
if ($uri === '/admin/directory/settings') {
    require $pluginDir . '/views/admin/settings.php';
    exit;
}

if ($uri === '/admin/directory/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `directory_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fields = ['moderation','map_provider','map_api_key','items_per_page','allow_reviews','allow_photos','max_photos','claim_enabled','featured_fee'];
    $checkboxes = ['allow_reviews','allow_photos','claim_enabled'];
    foreach ($fields as $f) {
        $val = in_array($f, $checkboxes) ? (isset($_POST[$f]) ? '1' : '0') : ($_POST[$f] ?? '');
        $pdo->prepare("INSERT INTO `directory_settings` (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$f, $val, $val]);
    }
    \Core\Session::flash('success', 'Settings saved!');
    \Core\Response::redirect('/admin/directory/settings?saved=1');
}

