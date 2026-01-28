<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class NotificationsController
{
    public function __construct()
    {
        require_once CMS_ROOT . '/includes/system/notificationmanager.php';
    }

    public function index(Request $request): void
    {
        $filter = $request->get('filter', 'all');
        $validFilters = ['all', 'info', 'warning', 'error', 'system'];

        if (!in_array($filter, $validFilters, true)) {
            $filter = 'all';
        }

        $notifications = \NotificationManager::getQueuedNotifications();

        if ($filter !== 'all') {
            $notifications = array_filter($notifications, fn($n) => ($n['type'] ?? 'info') === $filter);
            $notifications = array_values($notifications);
        }

        usort($notifications, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));

        $allNotifications = \NotificationManager::getQueuedNotifications();
        $stats = [
            'total' => count($allNotifications),
            'unread' => count(array_filter($allNotifications, fn($n) => empty($n['read']))),
            'info' => count(array_filter($allNotifications, fn($n) => ($n['type'] ?? '') === 'info')),
            'warning' => count(array_filter($allNotifications, fn($n) => ($n['type'] ?? '') === 'warning')),
            'error' => count(array_filter($allNotifications, fn($n) => ($n['type'] ?? '') === 'error')),
            'system' => count(array_filter($allNotifications, fn($n) => ($n['type'] ?? '') === 'system')),
        ];

        render('admin/notifications/index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'validFilters' => $validFilters,
            'stats' => $stats,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function markAllRead(Request $request): void
    {
        $success = \NotificationManager::markAllAsRead();

        if ($success) {
            Session::flash('success', 'All notifications marked as read.');
        } else {
            Session::flash('error', 'Failed to mark notifications as read.');
        }

        Response::redirect('/admin/notifications');
    }

    public function delete(Request $request): void
    {
        $id = $request->post('id', '');

        if (empty($id)) {
            Session::flash('error', 'Invalid notification ID.');
            Response::redirect('/admin/notifications');
        }

        $success = \NotificationManager::deleteNotification($id);

        if ($success) {
            Session::flash('success', 'Notification deleted.');
        } else {
            Session::flash('error', 'Failed to delete notification.');
        }

        Response::redirect('/admin/notifications');
    }

    public function clearAll(Request $request): void
    {
        $file = \CMS_ROOT . '/logs/notifications.json';
        $success = file_put_contents($file, '[]') !== false;

        if ($success) {
            Session::flash('success', 'All notifications cleared.');
        } else {
            Session::flash('error', 'Failed to clear notifications.');
        }

        Response::redirect('/admin/notifications');
    }
}
