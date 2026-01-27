<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/core/n8n_client.php';
require_once CMS_ROOT . '/core/n8n_events.php';
require_once CMS_ROOT . '/core/sites_context.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

cms_require_admin_role();

$currentSite = sites_bootstrap_current_site();

function esc($str) {
    if ($str === null) {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$result = null;
$formData = [
    'event_key' => '',
    'payload_json' => ''
];

$config = n8n_config_load();
$n8n_configured = n8n_is_configured($config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $event_key = isset($_POST['event_key']) ? trim($_POST['event_key']) : '';
    $payload_json = isset($_POST['payload_json']) ? trim($_POST['payload_json']) : '';

    $formData['event_key'] = $event_key;
    $formData['payload_json'] = $payload_json;

    if ($event_key === '') {
        $errors[] = 'Event key is required.';
    } elseif (!preg_match('/^[a-z0-9._-]+$/', $event_key)) {
        $errors[] = 'Event key must contain only lowercase letters, numbers, dots, underscores, and hyphens.';
    } elseif (strlen($event_key) > 255) {
        $errors[] = 'Event key must be 255 characters or less.';
    }

    $payloadArray = [];
    if ($payload_json !== '') {
        if (strlen($payload_json) > 20000) {
            $errors[] = 'Payload must be 20,000 characters or less.';
        } else {
            $decoded = @json_decode($payload_json, true);
            if ($decoded === null && $payload_json !== 'null') {
                $errors[] = 'Payload must be valid JSON.';
            } else {
                $payloadArray = is_array($decoded) ? $decoded : [];
            }
        }
    }

    if (empty($errors)) {
        $result = n8n_trigger_event($event_key, $payloadArray);
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>n8n Event Tester</h1>

        <div style="padding: 16px; margin: 16px 0; background-color: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px; color: #004085;">
            <h3 style="margin-top: 0;">What is this tool?</h3>
            <p style="margin: 8px 0;">
                This tool allows you to manually trigger n8n events using the configured bindings in your CMS.
                Enter an event key and optional payload, then see which bindings were triggered and their results.
            </p>
            <p style="margin: 8px 0; font-weight: bold; color: #856404;">
                <strong>DEV MODE ONLY:</strong> This is a diagnostics tool for development. Do not use in production.
            </p>
        </div>

        <div style="padding: 16px; margin: 16px 0; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
            <h3 style="margin-top: 0;">n8n Configuration Status</h3>
            <?php if (!$n8n_configured): ?>
                <p style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 12px; border-radius: 4px; margin: 0;">
                    <strong>Warning:</strong> n8n is not configured or disabled. Event triggers will likely fail or be skipped.
                </p>
            <?php else: ?>
                <p style="color: #155724; margin: 0;">
                    <strong>✓</strong> n8n is configured and ready.
                </p>
            <?php endif; ?>
        </div>

        <div style="margin: 16px 0; padding: 12px; background-color: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px; color: #0c4a6e;">
            <strong>Current Site Context</strong>
            <?php if ($currentSite !== null): ?>
                <?php
                $siteId = isset($currentSite['id']) ? (string)$currentSite['id'] : '';
                $siteName = isset($currentSite['name']) ? (string)$currentSite['name'] : '';
                $siteDomain = isset($currentSite['domain']) ? (string)$currentSite['domain'] : '';
                $siteLocale = isset($currentSite['locale']) ? (string)$currentSite['locale'] : '';
                $displayDomain = ($siteDomain === '' || $siteDomain === '*') ? '* (catch-all)' : $siteDomain;
                $displayLocale = ($siteLocale === '') ? 'n/a' : $siteLocale;
                ?>
                <div style="margin-top: 8px; font-size: 0.9rem;">
                    <div>ID: <?= esc($siteId) ?></div>
                    <div>Name: <?= esc($siteName) ?></div>
                    <div>Domain: <?= esc($displayDomain) ?> | Locale: <?= esc($displayLocale) ?></div>
                </div>
                <div style="margin-top: 8px; font-size: 0.85rem; color: #555;">
                    Events triggered from this page will include a <code>site</code> field in the JSON payload (id, domain, locale).
                </div>
            <?php else: ?>
                <div style="margin-top: 8px; font-size: 0.9rem;">
                    Single-site mode or no sites configured. Events will carry <code>"site": null</code> in the payload.
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="padding: 16px; margin: 16px 0; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                <strong>Validation Errors:</strong>
                <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo esc($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <h2>Trigger Event</h2>

        <form method="POST" action="" style="max-width: 800px; margin: 16px 0;">
            <?php csrf_field(); ?>

            <div style="margin-bottom: 16px;">
                <label for="event_key" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Event Key <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="event_key"
                    name="event_key"
                    required
                    maxlength="255"
                    pattern="[a-z0-9._-]+"
                    value="<?php echo esc($formData['event_key']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;"
                    placeholder="user.registered"
                />
                <small style="color: #6c757d; display: block; margin-top: 4px;">
                    Examples: user.registered, lead.captured, content.published
                </small>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="payload_json" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Payload (JSON, optional)
                </label>
                <textarea
                    id="payload_json"
                    name="payload_json"
                    rows="10"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; font-family: 'Courier New', Courier, monospace; background-color: #f8f9fa;"
                    placeholder='{"email": "user@example.com", "name": "John Doe"}'
                ><?php echo esc($formData['payload_json']); ?></textarea>
                <small style="color: #6c757d; display: block; margin-top: 4px;">
                    Enter a valid JSON object or array. Leave empty to use an empty payload.
                </small>
            </div>

            <div style="margin-top: 24px;">
                <button
                    type="submit"
                    style="padding: 10px 24px; background-color: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;"
                >
                    Trigger Event
                </button>
            </div>
        </form>

        <?php if ($result !== null): ?>
            <div style="margin-top: 32px; padding: 16px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                <h2 style="margin-top: 0;">Result</h2>

                <table style="width: 100%; border-collapse: collapse; margin: 16px 0; background-color: #fff; border: 1px solid #dee2e6;">
                    <tbody>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px; font-weight: bold; background-color: #f8f9fa; width: 30%;">Event Key</td>
                            <td style="padding: 12px;"><?php echo esc($result['event_key'] ?? '—'); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px; font-weight: bold; background-color: #f8f9fa;">Triggered Count</td>
                            <td style="padding: 12px;"><?php echo esc($result['triggered_count'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: bold; background-color: #f8f9fa;">Overall Status</td>
                            <td style="padding: 12px;">
                                <?php
                                $ok = isset($result['ok']) && $result['ok'];
                                $statusText = $ok ? 'Yes' : 'No';
                                $statusColor = $ok ? '#155724' : '#721c24';
                                ?>
                                <span style="color: <?php echo $statusColor; ?>; font-weight: bold;">
                                    <?php echo esc($statusText); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if (isset($result['reason'])): ?>
                    <div style="padding: 12px; margin: 16px 0; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
                        <strong>Note:</strong> <?php echo esc($result['reason']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($result['triggers']) && is_array($result['triggers']) && !empty($result['triggers'])): ?>
                    <h3>Triggered Bindings</h3>
                    <table style="width: 100%; border-collapse: collapse; margin: 16px 0; background-color: #fff; border: 1px solid #dee2e6;">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Binding ID</th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Workflow ID</th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Webhook Path</th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">HTTP Status</th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Result</th>
                                <th style="padding: 12px; text-align: left;">Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['triggers'] as $trigger): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($trigger['binding_id'] ?? '—'); ?></td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($trigger['workflow_id'] ?? '—'); ?></td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($trigger['webhook_path'] ?? '—'); ?></td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;">
                                        <?php
                                        if (isset($trigger['status'])) {
                                            echo esc($trigger['status']);
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;">
                                        <?php
                                        $triggerOk = isset($trigger['ok']) && $trigger['ok'];
                                        $resultText = $triggerOk ? 'OK' : 'Error';
                                        $resultColor = $triggerOk ? '#155724' : '#721c24';
                                        ?>
                                        <span style="color: <?php echo $resultColor; ?>; font-weight: bold;">
                                            <?php echo esc($resultText); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php
                                        if (isset($trigger['error']) && $trigger['error'] !== null) {
                                            $errorMsg = is_string($trigger['error']) ? $trigger['error'] : 'Unknown error';
                                            $truncated = strlen($errorMsg) > 100 ? substr($errorMsg, 0, 100) . '...' : $errorMsg;
                                            echo esc($truncated);
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h3>Raw Result (JSON)</h3>
                <textarea
                    readonly
                    rows="15"
                    style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 4px; font-family: 'Courier New', Courier, monospace; font-size: 12px; background-color: #1e1e1e; color: #d4d4d4; resize: vertical;"
                ><?php echo esc(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></textarea>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
