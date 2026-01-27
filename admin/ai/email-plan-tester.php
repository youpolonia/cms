<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/core/ai_email_automation.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

cms_require_admin_role();

if (!function_exists('esc')) {
    function esc($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$plan = null;
$eventKey = '';
$baseDate = date('Y-m-d');
$recipientEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $eventKey = isset($_POST['event_key']) ? trim($_POST['event_key']) : '';
    $baseDate = isset($_POST['base_date']) ? trim($_POST['base_date']) : '';
    $recipientEmail = isset($_POST['recipient_email']) ? trim($_POST['recipient_email']) : '';

    if ($eventKey === '') {
        $errors[] = 'Event key is required.';
    } elseif (strlen($eventKey) > 255) {
        $errors[] = 'Event key must not exceed 255 characters.';
    } elseif (!preg_match('/^[a-z0-9._-]+$/', $eventKey)) {
        $errors[] = 'Event key must contain only lowercase letters, digits, dots, underscores, and hyphens.';
    }

    if ($baseDate === '') {
        $errors[] = 'Base date is required.';
    } else {
        $parsedDate = DateTime::createFromFormat('Y-m-d', $baseDate);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $baseDate) {
            $errors[] = 'Base date must be in YYYY-MM-DD format.';
        }
    }

    if ($recipientEmail !== '') {
        if (strlen($recipientEmail) > 255) {
            $errors[] = 'Recipient email must not exceed 255 characters.';
        } elseif (strpos($recipientEmail, '@') === false || strpos($recipientEmail, '.') === false) {
            $errors[] = 'Recipient email must be a valid email address.';
        }
    }

    if (empty($errors)) {
        $recipientEmailOrNull = $recipientEmail !== '' ? $recipientEmail : null;
        $plan = ai_email_automation_plan_for_event($eventKey, $baseDate, $recipientEmailOrNull);

        if (!is_array($plan)) {
            $plan = [
                'event_key' => $eventKey,
                'base_date' => $baseDate,
                'recipient_email' => $recipientEmailOrNull,
                'sequences' => []
            ];
        }

        if (!isset($plan['sequences']) || !is_array($plan['sequences'])) {
            $plan['sequences'] = [];
        }
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>AI Email Plan Tester</h1>

        <p>This tool previews planned email automation sequences for a given event key and base date. No emails are sent, and no AI calls are made—this is purely a read-only inspection tool.</p>

        <?php if (!empty($errors)): ?>
            <div style="background: #fee; border: 1px solid #c00; color: #c00; padding: 12px; margin: 16px 0; border-radius: 4px;">
                <strong>Validation Errors:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo esc($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors) && $plan !== null): ?>
            <?php if (count($plan['sequences']) > 0): ?>
                <div style="background: #efe; border: 1px solid #0a0; color: #060; padding: 12px; margin: 16px 0; border-radius: 4px;">
                    <strong>Plan generated successfully.</strong>
                </div>
            <?php else: ?>
                <div style="background: #ffc; border: 1px solid #aa0; color: #660; padding: 12px; margin: 16px 0; border-radius: 4px;">
                    <strong>No sequences matched this event key.</strong>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0;">
            <?php csrf_field(); ?>

            <div style="margin-bottom: 16px;">
                <label for="event_key" style="display: block; font-weight: bold; margin-bottom: 4px;">Event Key:</label>
                <input
                    type="text"
                    id="event_key"
                    name="event_key"
                    value="<?php echo esc($eventKey); ?>"
                    placeholder="e.g. user.registered or lead.captured"
                    style="width: 100%; max-width: 500px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                    required
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label for="base_date" style="display: block; font-weight: bold; margin-bottom: 4px;">Base Date:</label>
                <input
                    type="date"
                    id="base_date"
                    name="base_date"
                    value="<?php echo esc($baseDate); ?>"
                    style="width: 100%; max-width: 500px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                    required
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label for="recipient_email" style="display: block; font-weight: bold; margin-bottom: 4px;">Recipient Email (optional):</label>
                <input
                    type="text"
                    id="recipient_email"
                    name="recipient_email"
                    value="<?php echo esc($recipientEmail); ?>"
                    placeholder="optional@example.com"
                    style="width: 100%; max-width: 500px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                >
            </div>

            <button type="submit" style="background: #0066cc; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                Generate Plan
            </button>
        </form>

        <?php if ($plan !== null && !empty($plan['sequences'])): ?>
            <h2 style="margin-top: 32px;">Plan Summary</h2>

            <table style="width: 100%; border-collapse: collapse; margin: 16px 0; background: white;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Property</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Event Key</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($plan['event_key'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Base Date</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($plan['base_date'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Recipient Email</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($plan['recipient_email'] ?? '—'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Number of Sequences</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc(count($plan['sequences'])); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Total Steps</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <?php
                            $totalSteps = 0;
                            foreach ($plan['sequences'] as $seq) {
                                if (isset($seq['steps']) && is_array($seq['steps'])) {
                                    $totalSteps += count($seq['steps']);
                                }
                            }
                            echo esc($totalSteps);
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin-top: 32px;">Planned Email Steps</h2>

            <?php
            $allSteps = [];
            foreach ($plan['sequences'] as $seq) {
                if (!isset($seq['steps']) || !is_array($seq['steps'])) {
                    continue;
                }
                foreach ($seq['steps'] as $step) {
                    $allSteps[] = [
                        'sequence_id' => $seq['id'] ?? '',
                        'sequence_name' => $seq['name'] ?? '',
                        'step_index' => $step['step_index'] ?? 0,
                        'offset_days' => $step['offset_days'] ?? 0,
                        'scheduled_date' => $step['scheduled_date'] ?? '',
                        'subject' => $step['subject'] ?? '',
                        'cta' => $step['cta'] ?? ''
                    ];
                }
            }

            usort($allSteps, function($a, $b) {
                $dateCmp = strcmp($a['scheduled_date'], $b['scheduled_date']);
                if ($dateCmp !== 0) {
                    return $dateCmp;
                }
                $seqCmp = strcmp($a['sequence_id'], $b['sequence_id']);
                if ($seqCmp !== 0) {
                    return $seqCmp;
                }
                return $a['step_index'] - $b['step_index'];
            });
            ?>

            <table style="width: 100%; border-collapse: collapse; margin: 16px 0; background: white;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">#</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Sequence ID</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Sequence Name</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Step Index</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Offset (days)</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Scheduled Date</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Subject</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">CTA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rowNum = 1; ?>
                    <?php foreach ($allSteps as $step): ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($rowNum++); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['sequence_id']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['sequence_name']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['step_index']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['offset_days']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['scheduled_date']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['subject']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc($step['cta']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2 style="margin-top: 32px;">Raw JSON Plan</h2>

            <p style="color: #666; font-size: 14px; margin-bottom: 8px;">
                Copy this JSON if you want to debug or integrate with other tools.
            </p>

            <textarea
                readonly
                style="width: 100%; height: 400px; font-family: 'Courier New', monospace; font-size: 12px; padding: 12px; border: 1px solid #ddd; border-radius: 4px; overflow-y: auto; background: #f9f9f9;"
            ><?php echo esc(json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></textarea>
        <?php endif; ?>
    </div>
</div>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
