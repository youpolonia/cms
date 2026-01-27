<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Automations Controller
 * Manages scheduled automation tasks - listing, enabling/disabling, and running
 */
class AutomationsController
{
    public function __construct()
    {
        // Load task classes
        require_once CMS_ROOT . '/core/tasks/backuptask.php';
        require_once CMS_ROOT . '/core/tasks/emailqueuetask.php';
        require_once CMS_ROOT . '/core/tasks/cacherefreshertask.php';
        require_once CMS_ROOT . '/core/tasks/sessioncleanertask.php';
        require_once CMS_ROOT . '/core/tasks/tempcleanertask.php';
        require_once CMS_ROOT . '/core/tasks/logrotationtask.php';
        require_once CMS_ROOT . '/core/automations.php';
    }

    /**
     * Display automations page and handle POST actions
     */
    public function index(Request $request): void
    {
        $notice = '';
        $noticeType = 'success';

        // Handle POST actions
        if ($request->method() === 'POST') {
            $action = trim($request->post('action', ''));
            $id = trim($request->post('id', ''));

            if ($id === '') {
                $notice = 'Invalid automation ID';
                $noticeType = 'error';
            } else {
                // Validate ID exists
                $automations = automations_list();
                $validIds = array_column($automations, 'id');

                if (!in_array($id, $validIds, true)) {
                    $notice = 'Automation not found';
                    $noticeType = 'error';
                } else {
                    switch ($action) {
                        case 'toggle_enabled':
                            // Find current status and toggle
                            $currentEnabled = false;
                            foreach ($automations as $auto) {
                                if ($auto['id'] === $id) {
                                    $currentEnabled = $auto['enabled'] ?? false;
                                    break;
                                }
                            }

                            $newEnabled = !$currentEnabled;
                            $result = automations_set_enabled($id, $newEnabled);

                            if ($result) {
                                $notice = $newEnabled ? 'Automation enabled successfully' : 'Automation disabled successfully';
                                $noticeType = 'success';
                            } else {
                                $notice = 'Failed to update automation status';
                                $noticeType = 'error';
                            }
                            break;

                        case 'run_now':
                            $result = automations_run_now($id);

                            if ($result) {
                                $notice = 'Automation executed successfully';
                                $noticeType = 'success';
                            } else {
                                $notice = 'Failed to execute automation';
                                $noticeType = 'error';
                            }
                            break;

                        default:
                            $notice = 'Invalid action';
                            $noticeType = 'error';
                    }
                }
            }
        }

        // Load automations for display
        $automations = automations_list();

        // Calculate stats
        $totalCount = count($automations);
        $enabledCount = 0;
        $disabledCount = 0;
        $lastRunInfo = null;

        foreach ($automations as $auto) {
            if ($auto['enabled'] ?? false) {
                $enabledCount++;
            } else {
                $disabledCount++;
            }

            // Track most recent run
            if (!empty($auto['last_run'])) {
                if ($lastRunInfo === null || $auto['last_run'] > $lastRunInfo['time']) {
                    $lastRunInfo = [
                        'time' => $auto['last_run'],
                        'name' => $auto['name']
                    ];
                }
            }
        }

        render('admin/automations/index', [
            'automations' => $automations,
            'totalCount' => $totalCount,
            'enabledCount' => $enabledCount,
            'disabledCount' => $disabledCount,
            'lastRunInfo' => $lastRunInfo,
            'notice' => $notice,
            'noticeType' => $noticeType
        ]);
    }
}
