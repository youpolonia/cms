<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Automation Rules Controller
 * Manages automation rules that connect CMS events to n8n workflows
 */
class AutomationRulesController
{
    public function __construct()
    {
        require_once CMS_ROOT . '/core/automation_rules.php';
        require_once CMS_ROOT . '/core/n8n_events.php';
    }

    /**
     * Display automation rules page and handle POST actions
     */
    public function index(Request $request): void
    {
        $message = null;
        $messageType = null;

        // Load rules
        $loadResult = automation_rules_load();
        $rules = $loadResult['rules'];
        $loadError = !$loadResult['ok'] ? $loadResult['error'] : null;

        // Handle POST actions
        if ($request->method() === 'POST' && $loadError === null) {
            $action = trim($request->post('action', ''));
            $ruleId = trim($request->post('rule_id', ''));

            switch ($action) {
                case 'add_rule':
                    $result = $this->handleAddRule($request, $rules);
                    if ($result['redirect']) {
                        Response::redirect('/admin/automation-rules?msg=added');
                        return;
                    }
                    $message = $result['message'];
                    $messageType = $result['type'];
                    $rules = $result['rules'];
                    break;

                case 'update_rule':
                    $result = $this->handleUpdateRule($request, $rules);
                    if ($result['redirect']) {
                        Response::redirect('/admin/automation-rules?msg=updated');
                        return;
                    }
                    $message = $result['message'];
                    $messageType = $result['type'];
                    break;

                case 'delete_rule':
                    $result = $this->handleDeleteRule($ruleId, $rules);
                    if ($result['redirect']) {
                        Response::redirect('/admin/automation-rules?msg=deleted');
                        return;
                    }
                    $message = $result['message'];
                    $messageType = $result['type'];
                    break;

                case 'toggle_rule':
                    $result = $this->handleToggleRule($ruleId, $rules);
                    if ($result['redirect']) {
                        Response::redirect('/admin/automation-rules?msg=toggled');
                        return;
                    }
                    $message = $result['message'];
                    $messageType = $result['type'];
                    break;

                default:
                    $message = 'Invalid action';
                    $messageType = 'error';
            }

            // Reload rules after modification
            $loadResult = automation_rules_load();
            $rules = $loadResult['rules'];
        }

        // Handle flash messages from redirects
        if (isset($_GET['msg'])) {
            $msg = $_GET['msg'];
            $messages = [
                'added' => 'Rule added successfully.',
                'updated' => 'Rule updated successfully.',
                'deleted' => 'Rule deleted successfully.',
                'toggled' => 'Rule toggled successfully.'
            ];
            if (isset($messages[$msg])) {
                $message = $messages[$msg];
                $messageType = 'success';
            }
        }

        // Check for edit mode
        $editRuleId = isset($_GET['edit']) ? trim($_GET['edit']) : null;
        $editRule = null;
        if ($editRuleId !== null) {
            foreach ($rules as $rule) {
                if ($rule['id'] === $editRuleId) {
                    $editRule = $rule;
                    break;
                }
            }
        }

        // Calculate stats
        $totalCount = count($rules);
        $activeCount = 0;
        foreach ($rules as $rule) {
            if ($rule['active'] ?? false) {
                $activeCount++;
            }
        }

        // Available events for the dropdown
        $availableEvents = [
            'Content' => [
                'blog.post_published' => 'Blog Post Published',
                'blog.post_updated' => 'Blog Post Updated',
                'content.created' => 'Content Created',
                'content.updated' => 'Content Updated',
                'content.deleted' => 'Content Deleted'
            ],
            'Media' => [
                'media.image_uploaded' => 'Image Uploaded',
                'media.file_uploaded' => 'File Uploaded',
                'media.deleted' => 'Media Deleted'
            ],
            'Users' => [
                'user.registered' => 'User Registered',
                'user.login' => 'User Login',
                'user.profile_updated' => 'Profile Updated'
            ],
            'Forms' => [
                'form.submission_created' => 'Form Submitted',
                'form.submission_approved' => 'Submission Approved'
            ],
            'System' => [
                'custom.manual' => 'Manual Trigger',
                'system.backup_completed' => 'Backup Completed',
                'system.cache_cleared' => 'Cache Cleared'
            ]
        ];

        render('admin/automation-rules/index', [
            'rules' => $rules,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'loadError' => $loadError,
            'message' => $message,
            'messageType' => $messageType,
            'editRule' => $editRule,
            'availableEvents' => $availableEvents
        ]);
    }

    /**
     * Handle add rule action
     */
    private function handleAddRule(Request $request, array $rules): array
    {
        $name = trim(substr($request->post('name', ''), 0, 255));
        $eventKey = trim($request->post('event_key', ''));
        $n8nEvent = trim(substr($request->post('n8n_event', ''), 0, 255));
        $notes = trim(substr($request->post('notes', ''), 0, 1000));

        if ($name === '' || $eventKey === '' || $n8nEvent === '') {
            return [
                'redirect' => false,
                'message' => 'Name, event key, and n8n event are required.',
                'type' => 'error',
                'rules' => $rules
            ];
        }

        $newRule = [
            'id' => 'rule_' . time() . '_' . random_int(1000, 9999),
            'name' => $name,
            'event_key' => $eventKey,
            'action_type' => 'n8n_webhook',
            'action_config' => [
                'event' => $n8nEvent
            ],
            'active' => true,
            'notes' => $notes
        ];

        $rules[] = $newRule;
        $saveResult = automation_rules_save($rules);

        if ($saveResult['ok']) {
            return ['redirect' => true, 'rules' => $rules];
        }

        return [
            'redirect' => false,
            'message' => $saveResult['error'] ?? 'Failed to save rule.',
            'type' => 'error',
            'rules' => $rules
        ];
    }

    /**
     * Handle update rule action
     */
    private function handleUpdateRule(Request $request, array $rules): array
    {
        $ruleId = trim($request->post('rule_id', ''));
        $name = trim(substr($request->post('name', ''), 0, 255));
        $eventKey = trim($request->post('event_key', ''));
        $n8nEvent = trim(substr($request->post('n8n_event', ''), 0, 255));
        $notes = trim(substr($request->post('notes', ''), 0, 1000));

        if ($ruleId === '' || $name === '' || $eventKey === '' || $n8nEvent === '') {
            return [
                'redirect' => false,
                'message' => 'Invalid update request.',
                'type' => 'error'
            ];
        }

        $found = false;
        foreach ($rules as $idx => $rule) {
            if ($rule['id'] === $ruleId) {
                $rules[$idx]['name'] = $name;
                $rules[$idx]['event_key'] = $eventKey;
                $rules[$idx]['action_config']['event'] = $n8nEvent;
                $rules[$idx]['notes'] = $notes;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return [
                'redirect' => false,
                'message' => 'Rule not found.',
                'type' => 'error'
            ];
        }

        $saveResult = automation_rules_save($rules);

        if ($saveResult['ok']) {
            return ['redirect' => true];
        }

        return [
            'redirect' => false,
            'message' => $saveResult['error'] ?? 'Failed to update rule.',
            'type' => 'error'
        ];
    }

    /**
     * Handle delete rule action
     */
    private function handleDeleteRule(string $ruleId, array $rules): array
    {
        if ($ruleId === '') {
            return [
                'redirect' => false,
                'message' => 'Invalid delete request.',
                'type' => 'error'
            ];
        }

        $newRules = [];
        foreach ($rules as $rule) {
            if ($rule['id'] !== $ruleId) {
                $newRules[] = $rule;
            }
        }

        $saveResult = automation_rules_save($newRules);

        if ($saveResult['ok']) {
            return ['redirect' => true];
        }

        return [
            'redirect' => false,
            'message' => $saveResult['error'] ?? 'Failed to delete rule.',
            'type' => 'error'
        ];
    }

    /**
     * Handle toggle rule action
     */
    private function handleToggleRule(string $ruleId, array $rules): array
    {
        if ($ruleId === '') {
            return [
                'redirect' => false,
                'message' => 'Invalid toggle request.',
                'type' => 'error'
            ];
        }

        $found = false;
        foreach ($rules as $idx => $rule) {
            if ($rule['id'] === $ruleId) {
                $rules[$idx]['active'] = !$rule['active'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            return [
                'redirect' => false,
                'message' => 'Rule not found.',
                'type' => 'error'
            ];
        }

        $saveResult = automation_rules_save($rules);

        if ($saveResult['ok']) {
            return ['redirect' => true];
        }

        return [
            'redirect' => false,
            'message' => $saveResult['error'] ?? 'Failed to toggle rule.',
            'type' => 'error'
        ];
    }
}
