<?php
/**
 * Jessie Newsletter+ — Admin Router
 * Handles /admin/newsletter/* requests
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-newsletter';
require_once $pluginDir . '/includes/class-newsletter-list.php';
require_once $pluginDir . '/includes/class-newsletter-subscriber.php';
require_once $pluginDir . '/includes/class-newsletter-campaign.php';
require_once $pluginDir . '/includes/class-newsletter-sender.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

// Dashboard
if ($uri === '/admin/newsletter' || $uri === '/admin/newsletter/') {
    require $pluginDir . '/views/admin/dashboard.php';
    exit;
}

// ─── CAMPAIGNS ───
if ($uri === '/admin/newsletter/campaigns') { require $pluginDir . '/views/admin/campaigns.php'; exit; }

if ($uri === '/admin/newsletter/campaigns/create') {
    $campaign = null;
    if (!empty($_GET['template_id'])) {
        $tpl = db()->prepare("SELECT content_html FROM newsletter_templates WHERE id = ?")->fetch(\PDO::FETCH_ASSOC);
    }
    require $pluginDir . '/views/admin/campaign-form.php';
    exit;
}

if (preg_match('#^/admin/newsletter/campaigns/(\d+)/edit$#', $uri, $m)) {
    $campaign = \NewsletterCampaign::get((int)$m[1]);
    if (!$campaign) { \Core\Session::flash('error', 'Campaign not found.'); \Core\Response::redirect('/admin/newsletter/campaigns'); }
    require $pluginDir . '/views/admin/campaign-form.php';
    exit;
}

if ($uri === '/admin/newsletter/campaigns/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $id = \NewsletterCampaign::create($data);
    if (!empty($data['scheduled_at'])) {
        \NewsletterCampaign::update($id, ['status' => 'scheduled', 'scheduled_at' => $data['scheduled_at']]);
    }
    \Core\Session::flash('success', 'Campaign saved.');
    \Core\Response::redirect('/admin/newsletter/campaigns/' . $id . '/edit');
}

if (preg_match('#^/admin/newsletter/campaigns/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \NewsletterCampaign::update((int)$m[1], $data);
    if (!empty($data['scheduled_at'])) {
        \NewsletterCampaign::update((int)$m[1], ['status' => 'scheduled']);
    }
    \Core\Session::flash('success', 'Campaign updated.');
    \Core\Response::redirect('/admin/newsletter/campaigns/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/newsletter/campaigns/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \NewsletterCampaign::delete((int)$m[1]);
    \Core\Session::flash('success', 'Campaign deleted.');
    \Core\Response::redirect('/admin/newsletter/campaigns');
}

// ─── SUBSCRIBERS ───
if ($uri === '/admin/newsletter/subscribers') { require $pluginDir . '/views/admin/subscribers.php'; exit; }
if ($uri === '/admin/newsletter/subscribers/add') { require $pluginDir . '/views/admin/subscriber-form.php'; exit; }
if ($uri === '/admin/newsletter/subscribers/import') { require $pluginDir . '/views/admin/import.php'; exit; }

if ($uri === '/admin/newsletter/subscribers/store' && $method === 'POST') {
    csrf_validate_or_403();
    $lists = array_map('intval', $_POST['lists'] ?? []);
    \NewsletterSubscriber::subscribe($_POST['email'] ?? '', $_POST['name'] ?? '', $lists, 'admin');
    \Core\Session::flash('success', 'Subscriber added.');
    \Core\Response::redirect('/admin/newsletter/subscribers');
}

// ─── LISTS ───
if ($uri === '/admin/newsletter/lists') { require $pluginDir . '/views/admin/lists.php'; exit; }

if ($uri === '/admin/newsletter/lists/create') {
    $list = null;
    require $pluginDir . '/views/admin/list-form.php';
    exit;
}

if (preg_match('#^/admin/newsletter/lists/(\d+)/edit$#', $uri, $m)) {
    $list = \NewsletterList::get((int)$m[1]);
    if (!$list) { \Core\Session::flash('error', 'List not found.'); \Core\Response::redirect('/admin/newsletter/lists'); }
    require $pluginDir . '/views/admin/list-form.php';
    exit;
}

if ($uri === '/admin/newsletter/lists/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \NewsletterList::create($data);
    \Core\Session::flash('success', 'List created.');
    \Core\Response::redirect('/admin/newsletter/lists');
}

if (preg_match('#^/admin/newsletter/lists/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \NewsletterList::update((int)$m[1], $data);
    \Core\Session::flash('success', 'List updated.');
    \Core\Response::redirect('/admin/newsletter/lists');
}

if (preg_match('#^/admin/newsletter/lists/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \NewsletterList::delete((int)$m[1]);
    \Core\Session::flash('success', 'List deleted.');
    \Core\Response::redirect('/admin/newsletter/lists');
}

// ─── TEMPLATES ───
if ($uri === '/admin/newsletter/templates') { require $pluginDir . '/views/admin/templates.php'; exit; }

// Unsubscribe page (frontend)
if ($uri === '/newsletter/unsubscribe') {
    $email = $_GET['email'] ?? '';
    if ($email && $_SERVER['REQUEST_METHOD'] === 'POST') {
        \NewsletterSubscriber::unsubscribe($email);
    }
    echo '<!DOCTYPE html><html><head><title>Unsubscribe</title></head><body style="font-family:sans-serif;max-width:400px;margin:80px auto;text-align:center">';
    if ($method === 'POST') {
        echo '<h2>✅ Unsubscribed</h2><p>You have been removed from our mailing list.</p>';
    } else {
        echo '<h2>Unsubscribe</h2><p>Click below to unsubscribe <strong>' . htmlspecialchars($email) . '</strong></p>';
        echo '<form method="post"><button type="submit" style="padding:12px 24px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer">Unsubscribe</button></form>';
    }
    echo '</body></html>';
    exit;
}
