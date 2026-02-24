<?php
/**
 * Jessie Restaurant — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');
$pluginDir = CMS_ROOT . '/plugins/jessie-restaurant';
require_once $pluginDir . '/includes/class-restaurant-menu.php';
require_once $pluginDir . '/includes/class-restaurant-order.php';
\Core\Session::requireRole('admin');
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

// Dashboard
if ($uri === '/admin/restaurant' || $uri === '/admin/restaurant/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── MENU ITEMS ───
if ($uri === '/admin/restaurant/menu') { require $pluginDir . '/views/admin/menu.php'; exit; }
if ($uri === '/admin/restaurant/menu/create') { $item = null; require $pluginDir . '/views/admin/item-form.php'; exit; }
if (preg_match('#^/admin/restaurant/menu/(\d+)/edit$#', $uri, $m)) {
    $item = \RestaurantMenu::getItem((int)$m[1]);
    if (!$item) { \Core\Session::flash('error', 'Not found'); \Core\Response::redirect('/admin/restaurant/menu'); }
    require $pluginDir . '/views/admin/item-form.php'; exit;
}
if ($uri === '/admin/restaurant/menu/store' && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    $id = \RestaurantMenu::createItem($data);
    \Core\Session::flash('success', 'Item created.'); \Core\Response::redirect('/admin/restaurant/menu/' . $id . '/edit');
}
if (preg_match('#^/admin/restaurant/menu/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    \RestaurantMenu::updateItem((int)$m[1], $data);
    \Core\Session::flash('success', 'Item updated.'); \Core\Response::redirect('/admin/restaurant/menu/' . $m[1] . '/edit');
}
if (preg_match('#^/admin/restaurant/menu/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); \RestaurantMenu::deleteItem((int)$m[1]);
    \Core\Session::flash('success', 'Deleted.'); \Core\Response::redirect('/admin/restaurant/menu');
}

// ─── CATEGORIES ───
if ($uri === '/admin/restaurant/categories') { require $pluginDir . '/views/admin/categories.php'; exit; }
if ($uri === '/admin/restaurant/categories/store' && $method === 'POST') {
    csrf_validate_or_403(); $data = $_POST; unset($data['csrf_token']);
    \RestaurantMenu::createCategory($data);
    \Core\Session::flash('success', 'Category created.'); \Core\Response::redirect('/admin/restaurant/categories');
}
if (preg_match('#^/admin/restaurant/categories/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403(); \RestaurantMenu::deleteCategory((int)$m[1]);
    \Core\Session::flash('success', 'Deleted.'); \Core\Response::redirect('/admin/restaurant/categories');
}

// ─── ORDERS ───
if ($uri === '/admin/restaurant/orders') { require $pluginDir . '/views/admin/orders.php'; exit; }
if (preg_match('#^/admin/restaurant/orders/(\d+)$#', $uri, $m)) {
    $order = \RestaurantOrder::get((int)$m[1]);
    if (!$order) { \Core\Session::flash('error', 'Not found'); \Core\Response::redirect('/admin/restaurant/orders'); }
    require $pluginDir . '/views/admin/order-detail.php'; exit;
}

// ─── KITCHEN ───
if ($uri === '/admin/restaurant/kitchen') { require $pluginDir . '/views/admin/kitchen.php'; exit; }

// ─── SETTINGS ───
if ($uri === '/admin/restaurant/settings') { require $pluginDir . '/views/admin/settings.php'; exit; }
if ($uri === '/admin/restaurant/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    foreach ($data as $key => $value) \RestaurantMenu::setSetting($key, $value);
    \Core\Session::flash('success', 'Settings saved.'); \Core\Response::redirect('/admin/restaurant/settings');
}
