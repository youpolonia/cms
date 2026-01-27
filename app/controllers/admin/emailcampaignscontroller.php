<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * AI Email Campaigns Controller
 * Handles generation and management of AI-powered email campaign sequences
 */
class EmailCampaignsController
{
    /**
     * Main index page - handles both display and AJAX actions
     */
    public function index(Request $request): void
    {
        // Load required dependencies
        require_once CMS_ROOT . '/core/ai_email_campaigns.php';
        require_once CMS_ROOT . '/core/ai_model_selector.php';

        $spec = null;
        $savedCampaign = null;
        $error = null;
        $successMessage = null;
        $previewSpec = null;
        $previewCampaignId = null;
        $testQueueResult = null;
        $testQueueErrors = [];

        // Handle AJAX requests
        if ($request->isPost() && $request->post('ajax_action')) {
            header('Content-Type: application/json');

            $ajaxAction = $request->post('ajax_action', '');

            switch ($ajaxAction) {
                case 'generate':
                    echo json_encode($this->handleGenerate($request));
                    return;

                case 'save':
                    echo json_encode($this->handleSave($request));
                    return;

                case 'delete':
                    echo json_encode($this->handleDelete($request));
                    return;

                case 'queue_test':
                    echo json_encode($this->handleQueueTest($request));
                    return;

                case 'load':
                    echo json_encode($this->handleLoad($request));
                    return;

                default:
                    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
                    return;
            }
        }

        // Handle regular POST (non-AJAX form submissions)
        if ($request->isPost()) {
            $action = $request->post('action', '');

            if ($action === 'generate_spec' || $action === 'generate_and_save') {
                $result = $this->generateCampaign($request);

                if ($result['ok']) {
                    $spec = $result['spec'];

                    if ($action === 'generate_and_save') {
                        $saveResult = ai_email_campaign_save_spec($spec);
                        if ($saveResult['ok']) {
                            $savedCampaign = $saveResult;
                            $successMessage = 'Campaign "' . esc($spec['campaign_name']) . '" saved! ID: ' . esc($saveResult['id']);
                        } else {
                            $error = 'Generated but failed to save: ' . ($saveResult['error'] ?? 'Unknown error');
                        }
                    }
                } else {
                    $error = $result['error'];
                }
            }

            if ($action === 'queue_test') {
                $queueResult = $this->processQueueTest($request);
                if ($queueResult['ok']) {
                    $testQueueResult = $queueResult;
                    $previewSpec = $queueResult['spec'] ?? null;
                    $previewCampaignId = $queueResult['campaign_id'] ?? null;
                } else {
                    $testQueueErrors[] = $queueResult['error'];
                    $previewSpec = $queueResult['spec'] ?? null;
                    $previewCampaignId = $queueResult['campaign_id'] ?? null;
                }
            }
        }

        // Handle GET request for viewing a specific campaign
        if ($request->isGet() && $request->get('view')) {
            $viewId = trim($request->get('view', ''));
            if ($viewId !== '') {
                $loadedSpec = ai_email_campaign_load_spec($viewId);
                if ($loadedSpec !== null) {
                    $previewSpec = $loadedSpec;
                    $previewCampaignId = $viewId;
                }
            }
        }

        // Use previewSpec for display if available, otherwise use spec from generation
        $displaySpec = $previewSpec !== null ? $previewSpec : $spec;

        // Load saved campaigns list
        $savedCampaigns = ai_email_campaign_list_specs();

        // Get available AI models for selector
        $availableModels = ai_model_selector_get_providers();
        $defaultProvider = ai_model_selector_get_default_provider();

        // Render view
        render('admin/email-campaigns/index', [
            'spec' => $spec,
            'savedCampaign' => $savedCampaign,
            'error' => $error,
            'successMessage' => $successMessage,
            'previewSpec' => $previewSpec,
            'previewCampaignId' => $previewCampaignId,
            'displaySpec' => $displaySpec,
            'testQueueResult' => $testQueueResult,
            'testQueueErrors' => $testQueueErrors,
            'savedCampaigns' => $savedCampaigns,
            'availableModels' => $availableModels,
            'defaultProvider' => $defaultProvider,
            'success' => Session::getFlash('success'),
            'flashError' => Session::getFlash('error')
        ]);
    }

    /**
     * Handle AJAX generate request
     */
    private function handleGenerate(Request $request): array
    {
        $params = [
            'campaign_name' => trim($request->post('campaign_name', '')),
            'goal' => trim($request->post('goal', '')),
            'audience' => trim($request->post('audience', '')),
            'offer' => trim($request->post('offer', '')),
            'language' => trim($request->post('language', 'en')),
            'tone' => trim($request->post('tone', 'professional')),
            'num_emails' => (int)$request->post('num_emails', 5),
            'provider' => trim($request->post('provider', '')),
            'model' => trim($request->post('model', ''))
        ];

        // Validate required fields
        if ($params['campaign_name'] === '' || $params['goal'] === '') {
            return ['ok' => false, 'error' => 'Campaign name and goal are required.'];
        }

        try {
            $result = ai_email_campaign_generate_spec($params);
            return $result;
        } catch (\Throwable $e) {
            error_log('[EmailCampaignsController] Generate error: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'An error occurred during generation.'];
        }
    }

    /**
     * Handle AJAX save request
     */
    private function handleSave(Request $request): array
    {
        $specJson = $request->post('spec', '');

        if (empty($specJson)) {
            return ['ok' => false, 'error' => 'No campaign data provided.'];
        }

        $spec = json_decode($specJson, true);
        if (!is_array($spec)) {
            return ['ok' => false, 'error' => 'Invalid campaign data.'];
        }

        try {
            $result = ai_email_campaign_save_spec($spec);
            return $result;
        } catch (\Throwable $e) {
            error_log('[EmailCampaignsController] Save error: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Failed to save campaign.'];
        }
    }

    /**
     * Handle AJAX delete request
     */
    private function handleDelete(Request $request): array
    {
        $campaignId = trim($request->post('campaign_id', ''));

        if (empty($campaignId)) {
            return ['ok' => false, 'error' => 'No campaign ID provided.'];
        }

        // Sanitize ID
        if (preg_match('/[^a-zA-Z0-9_\-]/', $campaignId)) {
            return ['ok' => false, 'error' => 'Invalid campaign ID.'];
        }

        $storageDir = CMS_ROOT . '/cms_storage/ai-email-campaigns';
        $filepath = $storageDir . '/' . $campaignId . '.json';

        if (!is_file($filepath)) {
            return ['ok' => false, 'error' => 'Campaign not found.'];
        }

        if (@unlink($filepath)) {
            return ['ok' => true, 'message' => 'Campaign deleted.'];
        }

        return ['ok' => false, 'error' => 'Failed to delete campaign file.'];
    }

    /**
     * Handle AJAX queue test request
     */
    private function handleQueueTest(Request $request): array
    {
        $campaignId = trim($request->post('campaign_id', ''));
        $emailIndex = (int)$request->post('email_index', 0);
        $recipients = trim($request->post('test_recipients', ''));

        if (empty($campaignId)) {
            return ['ok' => false, 'error' => 'No campaign selected.'];
        }

        if (empty($recipients)) {
            return ['ok' => false, 'error' => 'Please enter recipient email addresses.'];
        }

        $spec = ai_email_campaign_load_spec($campaignId);
        if ($spec === null) {
            return ['ok' => false, 'error' => 'Campaign not found.'];
        }

        $recipientsArray = preg_split('/[\s,]+/', $recipients, -1, PREG_SPLIT_NO_EMPTY);

        $result = ai_email_campaigns_queue_test($spec, $emailIndex, $recipientsArray);
        return $result;
    }

    /**
     * Handle AJAX load request
     */
    private function handleLoad(Request $request): array
    {
        $campaignId = trim($request->post('campaign_id', ''));

        if (empty($campaignId)) {
            return ['ok' => false, 'error' => 'No campaign ID provided.'];
        }

        $spec = ai_email_campaign_load_spec($campaignId);
        if ($spec === null) {
            return ['ok' => false, 'error' => 'Campaign not found.'];
        }

        return ['ok' => true, 'spec' => $spec, 'id' => $campaignId];
    }

    /**
     * Generate campaign from form data
     */
    private function generateCampaign(Request $request): array
    {
        $params = [
            'campaign_name' => trim($request->post('campaign_name', '')),
            'goal' => trim($request->post('goal', '')),
            'audience' => trim($request->post('audience', '')),
            'offer' => trim($request->post('offer', '')),
            'language' => trim($request->post('language', 'en')),
            'tone' => trim($request->post('tone', 'professional')),
            'num_emails' => (int)$request->post('num_emails', 5)
        ];

        if ($params['campaign_name'] === '' || $params['goal'] === '') {
            return ['ok' => false, 'error' => 'Campaign name and goal are required.'];
        }

        try {
            return ai_email_campaign_generate_spec($params);
        } catch (\Throwable $e) {
            error_log('[EmailCampaignsController] Generate error: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'An error occurred during generation.'];
        }
    }

    /**
     * Process queue test from form
     */
    private function processQueueTest(Request $request): array
    {
        $campaignId = trim($request->post('campaign_id', ''));
        $emailIndex = (int)$request->post('email_index', 0);
        $recipients = trim($request->post('test_recipients', ''));

        if (empty($campaignId)) {
            return ['ok' => false, 'error' => 'No campaign selected.'];
        }

        if (empty($recipients)) {
            return ['ok' => false, 'error' => 'Please enter recipient email addresses.'];
        }

        $spec = ai_email_campaign_load_spec($campaignId);
        if ($spec === null) {
            return ['ok' => false, 'error' => 'Campaign not found.'];
        }

        $recipientsArray = preg_split('/[\s,]+/', $recipients, -1, PREG_SPLIT_NO_EMPTY);

        $result = ai_email_campaigns_queue_test($spec, $emailIndex, $recipientsArray);
        $result['spec'] = $spec;
        $result['campaign_id'] = $campaignId;

        return $result;
    }
}
