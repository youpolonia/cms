<?php
/**
 * Jessie CMS — Event System
 * Central event dispatcher — fires events to n8n webhooks, automation rules, and internal handlers.
 * 
 * This is the MISSING PIECE that connects CMS actions to n8n workflows.
 * 
 * Architecture:
 *   CMS Controller → cms_event('content.published', $payload)
 *     → n8n webhook (if binding configured)
 *     → automation rules (if matching rule exists)
 *     → internal handlers (extensible via cms_on())
 *     → event log (for audit trail)
 *
 * Non-blocking: webhook calls use fire-and-forget with short timeout.
 * Safe: never breaks the CMS request, even if n8n is down.
 */

// Internal handler registry
$_CMS_EVENT_HANDLERS = [];

/**
 * Fire a CMS event. This is the main entry point.
 * 
 * @param string $eventKey  Event identifier (e.g. 'content.published')
 * @param array  $payload   Event data (article/page/user details, etc.)
 * @return void
 */
function cms_event(string $eventKey, array $payload = []): void
{
    // Enrich payload with metadata
    $payload['_event'] = $eventKey;
    $payload['_timestamp'] = date('c');
    $payload['_ip'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $payload['_user'] = $_SESSION['username'] ?? 'system';
    $payload['_site_url'] = cms_event_get_setting('site_url') ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');

    // 1. Log the event
    cms_event_log($eventKey, $payload);

    // 2. Fire to n8n webhook (if binding exists)
    cms_event_fire_n8n($eventKey, $payload);

    // 3. Fire automation rules
    cms_event_fire_rules($eventKey, $payload);

    // 4. Fire internal handlers
    cms_event_fire_handlers($eventKey, $payload);
}

/**
 * Register an internal event handler
 * 
 * @param string   $eventKey  Event to listen for (* = all events)
 * @param callable $handler   function(string $event, array $payload): void
 */
function cms_on(string $eventKey, callable $handler): void
{
    global $_CMS_EVENT_HANDLERS;
    $_CMS_EVENT_HANDLERS[$eventKey][] = $handler;
}

/**
 * Fire n8n webhook for event
 */
function cms_event_fire_n8n(string $eventKey, array $payload): void
{
    // Load bindings
    if (!function_exists('n8n_bindings_get')) {
        $bindingsFile = CMS_ROOT . '/core/n8n_bindings.php';
        if (file_exists($bindingsFile)) {
            require_once $bindingsFile;
        } else {
            return;
        }
    }

    $binding = n8n_bindings_get($eventKey);

    if (!$binding || empty($binding['enabled']) || empty($binding['workflow_id'])) {
        return;
    }

    // Load n8n client
    if (!function_exists('n8n_config_load')) {
        require_once CMS_ROOT . '/core/n8n_client.php';
    }

    $config = n8n_config_load();
    if (!$config['enabled'] || empty($config['base_url'])) {
        return;
    }

    // Build webhook URL
    // n8n webhooks: {base_url}/webhook/{workflow_id} or custom URL in binding
    $webhookUrl = '';
    if (!empty($binding['webhook_url'])) {
        $webhookUrl = $binding['webhook_url'];
    } else {
        $webhookUrl = rtrim($config['base_url'], '/') . '/webhook/' . $binding['workflow_id'];
    }

    // Fire-and-forget with short timeout
    cms_event_async_post($webhookUrl, $payload, $config);
}

/**
 * Fire automation rules for event
 */
function cms_event_fire_rules(string $eventKey, array $payload): void
{
    $rulesFile = CMS_ROOT . '/core/automation_rules.php';
    if (!file_exists($rulesFile)) return;

    if (!function_exists('automation_rules_load')) {
        require_once $rulesFile;
    }

    // Don't call the broken automation_rules_handle_event that needs n8n_trigger_event.
    // Instead, directly process rules here.
    $loadResult = automation_rules_load();
    if (!$loadResult['ok']) return;

    foreach ($loadResult['rules'] as $rule) {
        if (empty($rule['active'])) continue;
        if (($rule['event_key'] ?? '') !== $eventKey) continue;

        $actionType = $rule['action_type'] ?? '';

        switch ($actionType) {
            case 'n8n_webhook':
                $webhookUrl = $rule['action_config']['webhook_url'] ?? '';
                if ($webhookUrl) {
                    $rulePayload = array_merge($payload, [
                        '_rule_id' => $rule['id'] ?? '',
                        '_rule_name' => $rule['name'] ?? '',
                    ]);
                    $config = function_exists('n8n_config_load') ? n8n_config_load() : [];
                    cms_event_async_post($webhookUrl, $rulePayload, $config);
                }
                break;

            case 'webhook':
                $webhookUrl = $rule['action_config']['url'] ?? '';
                if ($webhookUrl) {
                    cms_event_async_post($webhookUrl, $payload, []);
                }
                break;

            case 'email':
                $to = $rule['action_config']['to'] ?? '';
                $subject = $rule['action_config']['subject'] ?? "Event: {$eventKey}";
                if ($to && function_exists('cms_send_email')) {
                    $body = "Event: {$eventKey}\n\nPayload:\n" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    cms_send_email($to, $subject, $body);
                }
                break;
        }
    }
}

/**
 * Fire internal handlers
 */
function cms_event_fire_handlers(string $eventKey, array $payload): void
{
    global $_CMS_EVENT_HANDLERS;

    // Exact match handlers
    if (!empty($_CMS_EVENT_HANDLERS[$eventKey])) {
        foreach ($_CMS_EVENT_HANDLERS[$eventKey] as $handler) {
            try {
                $handler($eventKey, $payload);
            } catch (\Throwable $e) {
                error_log("[EventSystem] Handler error for {$eventKey}: " . $e->getMessage());
            }
        }
    }

    // Wildcard handlers
    if (!empty($_CMS_EVENT_HANDLERS['*'])) {
        foreach ($_CMS_EVENT_HANDLERS['*'] as $handler) {
            try {
                $handler($eventKey, $payload);
            } catch (\Throwable $e) {
                error_log("[EventSystem] Wildcard handler error: " . $e->getMessage());
            }
        }
    }

    // Prefix wildcard: 'content.*' matches 'content.published'
    $prefix = explode('.', $eventKey)[0] . '.*';
    if (!empty($_CMS_EVENT_HANDLERS[$prefix])) {
        foreach ($_CMS_EVENT_HANDLERS[$prefix] as $handler) {
            try {
                $handler($eventKey, $payload);
            } catch (\Throwable $e) {
                error_log("[EventSystem] Prefix handler error for {$prefix}: " . $e->getMessage());
            }
        }
    }
}

/**
 * Non-blocking HTTP POST (fire-and-forget)
 * Uses cURL with 2-second timeout, or fsockopen as fallback.
 * Never blocks the main request.
 */
function cms_event_async_post(string $url, array $payload, array $config = []): void
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if (!$json) return;

    $headers = ['Content-Type: application/json'];

    // Add webhook secret
    if (!empty($config['webhook_secret'])) {
        $headers[] = 'X-N8N-Webhook-Secret: ' . $config['webhook_secret'];
    }

    // Add HMAC signature for verification
    $secret = $config['webhook_secret'] ?? '';
    if ($secret) {
        $signature = hash_hmac('sha256', $json, $secret);
        $headers[] = 'X-CMS-Signature: sha256=' . $signature;
    }

    // Try cURL first (most reliable)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,           // 3 sec max — fire and forget
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => $config['verify_ssl'] ?? true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            cms_event_log_webhook($url, $payload['_event'] ?? '', true, $httpCode);
        } else {
            cms_event_log_webhook($url, $payload['_event'] ?? '', false, $httpCode, $error ?: $response);
        }
        return;
    }

    // Fallback: fsockopen (works on free hosting without cURL)
    $parts = parse_url($url);
    if (!$parts) return;

    $scheme = ($parts['scheme'] ?? 'http') === 'https' ? 'ssl://' : '';
    $host = $parts['host'] ?? '';
    $port = $parts['port'] ?? (($parts['scheme'] ?? 'http') === 'https' ? 443 : 80);
    $path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');

    $fp = @fsockopen($scheme . $host, $port, $errno, $errstr, 2);
    if (!$fp) {
        cms_event_log_webhook($url, $payload['_event'] ?? '', false, 0, "Connect failed: {$errstr}");
        return;
    }

    $headerStr = implode("\r\n", $headers);
    $request = "POST {$path} HTTP/1.1\r\n"
        . "Host: {$host}\r\n"
        . "{$headerStr}\r\n"
        . "Content-Length: " . strlen($json) . "\r\n"
        . "Connection: close\r\n\r\n"
        . $json;

    fwrite($fp, $request);
    // Don't wait for response — fire and forget
    fclose($fp);

    cms_event_log_webhook($url, $payload['_event'] ?? '', true, 0, 'Sent via fsockopen (fire-and-forget)');
}

/**
 * Log event to file
 */
function cms_event_log(string $eventKey, array $payload): void
{
    $logDir = CMS_ROOT . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/events.log';
    $line = date('Y-m-d H:i:s') . " [{$eventKey}]"
        . " user=" . ($payload['_user'] ?? 'system')
        . " ip=" . ($payload['_ip'] ?? '-')
        . " " . json_encode(
            array_diff_key($payload, array_flip(['_event', '_timestamp', '_ip', '_user', '_site_url'])),
            JSON_UNESCAPED_UNICODE
        )
        . PHP_EOL;

    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

    // Rotate at 5MB
    if (@filesize($logFile) > 5 * 1024 * 1024) {
        @rename($logFile, $logFile . '.' . date('Ymd-His'));
    }
}

/**
 * Log webhook call result
 */
function cms_event_log_webhook(string $url, string $event, bool $success, int $httpCode, string $error = ''): void
{
    $logDir = CMS_ROOT . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

    $status = $success ? 'OK' : 'FAIL';
    $line = date('Y-m-d H:i:s') . " [{$status}] event={$event} url={$url} http={$httpCode}"
        . ($error ? " error={$error}" : '')
        . PHP_EOL;

    @file_put_contents($logDir . '/webhooks.log', $line, FILE_APPEND | LOCK_EX);
}

/**
 * Get all known events (extended with new modules)
 */
function cms_event_known_events(): array
{
    $events = [
        // Content
        ['key' => 'content.published', 'label' => 'Content published', 'description' => 'Article or page published', 'category' => 'content'],
        ['key' => 'content.updated', 'label' => 'Content updated', 'description' => 'Article or page updated', 'category' => 'content'],
        ['key' => 'content.deleted', 'label' => 'Content deleted', 'description' => 'Article or page deleted', 'category' => 'content'],
        ['key' => 'article.published', 'label' => 'Article published', 'description' => 'New article published', 'category' => 'content'],
        ['key' => 'article.updated', 'label' => 'Article updated', 'description' => 'Existing article updated', 'category' => 'content'],
        ['key' => 'page.published', 'label' => 'Page published', 'description' => 'New page published', 'category' => 'content'],
        ['key' => 'page.updated', 'label' => 'Page updated', 'description' => 'Existing page updated', 'category' => 'content'],

        // Users
        ['key' => 'user.registered', 'label' => 'User registered', 'description' => 'New user account created', 'category' => 'user'],
        ['key' => 'user.updated', 'label' => 'User updated', 'description' => 'User profile updated', 'category' => 'user'],
        ['key' => 'user.deleted', 'label' => 'User deleted', 'description' => 'User account deleted', 'category' => 'user'],
        ['key' => 'user.login', 'label' => 'User login', 'description' => 'User logged in successfully', 'category' => 'user'],

        // Forms
        ['key' => 'form.submitted', 'label' => 'Form submitted', 'description' => 'Contact or custom form submitted', 'category' => 'form'],
        ['key' => 'form.contact', 'label' => 'Contact form', 'description' => 'Contact form specifically submitted', 'category' => 'form'],

        // Comments
        ['key' => 'comment.posted', 'label' => 'Comment posted', 'description' => 'New comment posted', 'category' => 'comment'],
        ['key' => 'comment.approved', 'label' => 'Comment approved', 'description' => 'Comment approved by moderator', 'category' => 'comment'],

        // Media
        ['key' => 'media.uploaded', 'label' => 'Media uploaded', 'description' => 'File uploaded to media library', 'category' => 'media'],
        ['key' => 'media.deleted', 'label' => 'Media deleted', 'description' => 'File deleted from media library', 'category' => 'media'],

        // Email
        ['key' => 'email.sent', 'label' => 'Email sent', 'description' => 'Email successfully sent', 'category' => 'email'],
        ['key' => 'email.failed', 'label' => 'Email failed', 'description' => 'Email sending failed', 'category' => 'email'],

        // Chatbot
        ['key' => 'chatbot.message', 'label' => 'Chat message', 'description' => 'Visitor sent a chatbot message', 'category' => 'chatbot'],
        ['key' => 'chatbot.session.started', 'label' => 'Chat started', 'description' => 'New chatbot session started', 'category' => 'chatbot'],

        // CRM
        ['key' => 'crm.contact.created', 'label' => 'CRM contact created', 'description' => 'New contact added to CRM', 'category' => 'crm'],
        ['key' => 'crm.contact.updated', 'label' => 'CRM contact updated', 'description' => 'CRM contact details changed', 'category' => 'crm'],
        ['key' => 'crm.deal.created', 'label' => 'Deal created', 'description' => 'New deal added to pipeline', 'category' => 'crm'],
        ['key' => 'crm.deal.won', 'label' => 'Deal won', 'description' => 'Deal moved to won stage', 'category' => 'crm'],
        ['key' => 'crm.deal.lost', 'label' => 'Deal lost', 'description' => 'Deal moved to lost stage', 'category' => 'crm'],

        // Shop
        ['key' => 'shop.order.created', 'label' => 'Order placed', 'description' => 'New order created', 'category' => 'shop'],
        ['key' => 'shop.order.paid', 'label' => 'Order paid', 'description' => 'Order payment confirmed', 'category' => 'shop'],
        ['key' => 'shop.order.shipped', 'label' => 'Order shipped', 'description' => 'Order marked as shipped', 'category' => 'shop'],
        ['key' => 'shop.order.status', 'label' => 'Order status changed', 'description' => 'Order status updated', 'category' => 'shop'],
        ['key' => 'shop.product.created', 'label' => 'Product created', 'description' => 'New product added', 'category' => 'shop'],
        ['key' => 'shop.stock.low', 'label' => 'Low stock alert', 'description' => 'Product stock below threshold', 'category' => 'shop'],

        // Theme
        ['key' => 'theme.generated', 'label' => 'Theme generated', 'description' => 'AI theme generated', 'category' => 'theme'],
        ['key' => 'theme.activated', 'label' => 'Theme activated', 'description' => 'Theme activated on site', 'category' => 'theme'],

        // System
        ['key' => 'system.backup', 'label' => 'Backup created', 'description' => 'System backup completed', 'category' => 'system'],
        ['key' => 'system.update', 'label' => 'System updated', 'description' => 'CMS updated to new version', 'category' => 'system'],
        ['key' => 'system.error', 'label' => 'System error', 'description' => 'Critical system error occurred', 'category' => 'system'],
    ];

    return $events;
}

/**
 * Helper: get setting from DB
 */
function cms_event_get_setting(string $key): string
{
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $cache[$key] = $stmt->fetchColumn() ?: '';
    } catch (\Throwable $e) {
        $cache[$key] = '';
    }

    return $cache[$key];
}

/**
 * Backward compatibility: n8n_trigger_event (was missing, called by automation_rules.php)
 */
function n8n_trigger_event(string $eventKey, array $payload = []): void
{
    cms_event($eventKey, $payload);
}
