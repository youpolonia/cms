<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * N8n Integration Settings Controller
 * Manages n8n workflow platform connection and configuration
 */
class N8nSettingsController
{
    public function __construct()
    {
        // Load n8n client functions
        require_once CMS_ROOT . '/core/n8n_client.php';
    }

    /**
     * Display settings page
     */
    public function index(Request $request): void
    {
        $config = n8n_config_load();
        
        // Get workflows if connected
        $workflows = [];
        $workflowsError = null;
        
        if (n8n_is_configured($config)) {
            $result = n8n_list_workflows(20);
            if ($result['ok']) {
                $workflows = $result['workflows'];
            } else {
                $workflowsError = $result['error'];
            }
        }

        // Load connection log
        $connectionLog = $this->getConnectionLog();

        render('admin/n8n/settings', [
            'config' => $config,
            'workflows' => $workflows,
            'workflowsError' => $workflowsError,
            'connectionLog' => $connectionLog,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Save settings
     */
    public function save(Request $request): void
    {
        $data = [
            'enabled' => $request->post('enabled') === '1',
            'base_url' => trim($request->post('base_url', '')),
            'auth_type' => $request->post('auth_type', 'none'),
            'username' => trim($request->post('username', '')),
            'timeout' => (int) $request->post('timeout', 10),
            'verify_ssl' => $request->post('verify_ssl') === '1'
        ];

        // Only update secrets if provided
        $apiKey = trim($request->post('api_key', ''));
        if ($apiKey !== '') {
            $data['api_key'] = $apiKey;
        }

        $webhookSecret = trim($request->post('webhook_secret', ''));
        if ($webhookSecret !== '') {
            $data['webhook_secret'] = $webhookSecret;
        }

        $password = trim($request->post('password', ''));
        if ($password !== '') {
            $data['password'] = $password;
        }

        // Validate base_url
        if ($data['enabled'] && empty($data['base_url'])) {
            Session::flash('error', 'Base URL is required when n8n integration is enabled.');
            Response::redirect('/admin/n8n-settings');
            return;
        }

        // Validate URL format
        if (!empty($data['base_url']) && !filter_var($data['base_url'], FILTER_VALIDATE_URL)) {
            Session::flash('error', 'Invalid Base URL format. Please enter a valid URL.');
            Response::redirect('/admin/n8n-settings');
            return;
        }

        if (n8n_config_save($data)) {
            $this->logConnection('config_saved', 'Configuration updated successfully');
            Session::flash('success', 'n8n settings saved successfully.');
        } else {
            Session::flash('error', 'Failed to save settings. Check file permissions.');
        }

        Response::redirect('/admin/n8n-settings');
    }

    /**
     * AJAX health check
     */
    public function healthCheck(Request $request): void
    {
        header('Content-Type: application/json');

        $result = n8n_client_health_check();
        
        // Log the attempt
        $status = $result['ok'] ? 'success' : 'failed';
        $message = $result['ok'] ? 'Health check passed' : ($result['error'] ?? 'Connection failed');
        $this->logConnection('health_check_' . $status, $message);

        echo json_encode([
            'ok' => $result['ok'],
            'statusCode' => $result['statusCode'] ?? null,
            'error' => $result['error'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * AJAX list workflows
     */
    public function listWorkflows(Request $request): void
    {
        header('Content-Type: application/json');

        $limit = min(100, max(1, (int) ($request->get('limit') ?? 50)));
        $result = n8n_list_workflows($limit);

        echo json_encode([
            'ok' => $result['ok'],
            'workflows' => $result['workflows'] ?? [],
            'error' => $result['error'] ?? null,
            'count' => count($result['workflows'] ?? [])
        ]);
        exit;
    }

    /**
     * AJAX test webhook
     */
    public function testWebhook(Request $request): void
    {
        header('Content-Type: application/json');

        $webhookUrl = trim($request->post('webhook_url', ''));
        $payload = $request->post('payload', '{}');

        if (empty($webhookUrl)) {
            echo json_encode(['ok' => false, 'error' => 'Webhook URL is required']);
            exit;
        }

        // Validate URL
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid webhook URL']);
            exit;
        }

        // Parse payload
        $payloadData = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $payloadData = ['test' => true, 'source' => 'cms', 'timestamp' => time()];
        }

        // Send test request
        $config = n8n_config_load();
        $timeout = $config['timeout'] ?? 10;

        $contextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($payloadData),
                'timeout' => $timeout,
                'ignore_errors' => true
            ]
        ];

        if (!($config['verify_ssl'] ?? true)) {
            $contextOptions['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false
            ];
        }

        $context = stream_context_create($contextOptions);
        $response = @file_get_contents($webhookUrl, false, $context);

        // Parse status
        $status = null;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                    $status = (int) $matches[1];
                    break;
                }
            }
        }

        $ok = $status !== null && $status >= 200 && $status < 300;
        
        $this->logConnection(
            'webhook_test_' . ($ok ? 'success' : 'failed'),
            "Webhook test to {$webhookUrl}: HTTP {$status}"
        );

        echo json_encode([
            'ok' => $ok,
            'statusCode' => $status,
            'response' => $response !== false ? substr($response, 0, 500) : null,
            'error' => $ok ? null : "HTTP {$status}"
        ]);
        exit;
    }

    /**
     * Clear connection log
     */
    public function clearLog(Request $request): void
    {
        $logFile = \CMS_ROOT . '/storage/logs/n8n_connection.log';
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        Session::flash('success', 'Connection log cleared.');
        Response::redirect('/admin/n8n-settings');
    }

    /**
     * Log connection event
     */
    private function logConnection(string $event, string $message): void
    {
        $logDir = \CMS_ROOT . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/n8n_connection.log';
        $entry = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]) . "\n";

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // Keep only last 100 entries
        $this->trimLog($logFile, 100);
    }

    /**
     * Get connection log
     */
    private function getConnectionLog(int $limit = 10): array
    {
        $logFile = \CMS_ROOT . '/storage/logs/n8n_connection.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines);
        $lines = array_slice($lines, 0, $limit);

        $log = [];
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if ($entry) {
                $log[] = $entry;
            }
        }

        return $log;
    }

    /**
     * Trim log file to max entries
     */
    private function trimLog(string $file, int $maxLines): void
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, -$maxLines);
            file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX);
        }
    }
}
