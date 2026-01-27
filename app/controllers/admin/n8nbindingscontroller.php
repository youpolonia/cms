<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * N8n Event Bindings Controller
 * Configure which n8n workflows are triggered by CMS events
 */
class N8nBindingsController
{
    public function __construct()
    {
        require_once CMS_ROOT . '/core/n8n_bindings.php';
    }

    /**
     * Display bindings page and handle save
     */
    public function index(Request $request): void
    {
        $saveMessage = null;
        $saveSuccess = null;

        // Handle POST save
        if ($request->method() === 'POST' && $request->post('action') === 'save_bindings') {
            csrf_validate_or_403();

            $rawBindings = $request->post('bindings') ?? [];
            $normalizedBindings = [];

            if (is_array($rawBindings)) {
                foreach ($rawBindings as $eventKey => $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $workflowId = trim((string)($row['workflow_id'] ?? ''));
                    $enabled = !empty($row['enabled']);

                    // Skip empty bindings
                    if ($workflowId === '' && !$enabled) {
                        continue;
                    }

                    $normalizedBindings[$eventKey] = [
                        'workflow_id' => $workflowId,
                        'enabled' => $enabled
                    ];
                }
            }

            // Save via n8n_bindings_save()
            try {
                $result = n8n_bindings_save($normalizedBindings);

                if ($result === true) {
                    $saveSuccess = true;
                    $saveMessage = 'Bindings saved successfully.';
                } else {
                    $saveSuccess = false;
                    $saveMessage = 'Failed to save n8n bindings. Please try again.';
                }
            } catch (\Exception $e) {
                error_log('N8nBindingsController: Save exception - ' . $e->getMessage());
                $saveSuccess = false;
                $saveMessage = 'An unexpected error occurred while saving n8n bindings.';
            }
        }

        // Load bindings and known events for display
        $bindings = [];
        $knownEvents = [];
        $bindingsError = null;

        try {
            $bindings = n8n_bindings_load();
            $knownEvents = n8n_bindings_known_events();
        } catch (\Exception $e) {
            error_log('N8nBindingsController: Failed to load bindings - ' . $e->getMessage());
            $bindingsError = 'Unable to load n8n bindings configuration.';
        }

        // Group events by category
        $eventGroups = $this->groupEventsByCategory($knownEvents);

        // Load n8n config for URL
        $n8nConfig = [];
        if (function_exists('n8n_config_load')) {
            $n8nConfig = n8n_config_load();
        }

        render('admin/n8n/bindings', [
            'bindings' => $bindings,
            'knownEvents' => $knownEvents,
            'eventGroups' => $eventGroups,
            'bindingsError' => $bindingsError,
            'saveMessage' => $saveMessage,
            'saveSuccess' => $saveSuccess,
            'n8nConfig' => $n8nConfig,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Group events by category based on event key prefix
     */
    private function groupEventsByCategory(array $events): array
    {
        $groups = [
            'content' => [
                'icon' => '📄',
                'label' => 'Content Events',
                'events' => []
            ],
            'user' => [
                'icon' => '👤',
                'label' => 'User Events',
                'events' => []
            ],
            'form' => [
                'icon' => '📝',
                'label' => 'Form Events',
                'events' => []
            ],
            'emailqueue' => [
                'icon' => '📧',
                'label' => 'Email Events',
                'events' => []
            ],
            'scheduler' => [
                'icon' => '⏰',
                'label' => 'Scheduler Events',
                'events' => []
            ],
            'media' => [
                'icon' => '🖼️',
                'label' => 'Media Events',
                'events' => []
            ],
            'comment' => [
                'icon' => '💬',
                'label' => 'Comment Events',
                'events' => []
            ]
        ];

        foreach ($events as $event) {
            $key = $event['key'];
            $prefix = explode('.', $key)[0];

            if (isset($groups[$prefix])) {
                $groups[$prefix]['events'][] = $event;
            } else {
                // Create new group for unknown prefix
                if (!isset($groups[$prefix])) {
                    $groups[$prefix] = [
                        'icon' => '⚡',
                        'label' => ucfirst($prefix) . ' Events',
                        'events' => []
                    ];
                }
                $groups[$prefix]['events'][] = $event;
            }
        }

        // Remove empty groups
        return array_filter($groups, fn($g) => !empty($g['events']));
    }
}
