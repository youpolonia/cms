<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_email_campaign.php';
require_once CMS_ROOT . '/core/ai_email_automation.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo '403 Forbidden - This page is only accessible in development mode.';
    exit;
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$form = [
    'campaign_json' => '',
    'recipient_email' => '',
    'start_time' => '',
    'interval_minutes' => '1440',
    'subject_prefix' => ''
];

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'schedule_test_campaign') {
    csrf_validate_or_403();

    $form['campaign_json'] = isset($_POST['campaign_json']) ? trim($_POST['campaign_json']) : '';
    $form['recipient_email'] = isset($_POST['recipient_email']) ? trim($_POST['recipient_email']) : '';
    $form['start_time'] = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $form['interval_minutes'] = isset($_POST['interval_minutes']) ? trim($_POST['interval_minutes']) : '1440';
    $form['subject_prefix'] = isset($_POST['subject_prefix']) ? trim($_POST['subject_prefix']) : '';

    $options = [];
    if ($form['start_time'] !== '') {
        $options['start_time'] = $form['start_time'];
    }
    if ($form['interval_minutes'] !== '') {
        $options['interval_minutes'] = (int)$form['interval_minutes'];
    }
    if ($form['subject_prefix'] !== '') {
        $options['subject_prefix'] = $form['subject_prefix'];
    }

    $result = ai_email_automation_schedule_from_json($form['campaign_json'], $form['recipient_email'], $options);

    if ($result['ok']) {
        $success = [
            'queued' => $result['queued'],
            'recipient' => $result['recipient'],
            'start_time' => $result['start_time'],
            'end_time' => $result['end_time']
        ];
    } else {
        $error = $result['error'];
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>AI Email Campaign Scheduler (Phase 1)</h1>

        <p style="margin: 8px 0 16px 0; color: #495057;">
            Schedule AI-generated email campaigns to the email queue for a single test recipient.
        </p>

        <div style="padding: 16px; margin: 16px 0; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; color: #0c5460;">
            <h3 style="margin: 0 0 8px 0;">How to Use</h3>
            <ol style="margin: 8px 0 0 20px; padding: 0;">
                <li>Generate a campaign using <a href="/admin/ai-email-campaign.php">AI Email Campaign Generator</a></li>
                <li>Copy the Raw JSON output from the generator</li>
                <li>Paste it below and provide a test recipient email</li>
                <li>Configure start time, interval, and optional subject prefix</li>
                <li>Click "Schedule Campaign" to queue emails</li>
            </ol>
            <p style="margin: 12px 0 0 0;">
                <strong>Note:</strong> This is Phase 1 - campaigns are scheduled to the existing email queue for manual testing with a single recipient. The email queue will process these on schedule.
            </p>
        </div>

        <?php if (!defined('DEV_MODE') || DEV_MODE !== true): ?>
            <div style="padding: 12px; margin: 16px 0; border-radius: 4px; background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404;">
                <strong>Warning:</strong> This tool is only available in development mode.
            </div>
        <?php endif; ?>

        <?php if ($success !== null): ?>
            <div style="padding: 16px; margin: 16px 0; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                <h3 style="margin: 0 0 12px 0;">Campaign Scheduled Successfully</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold; width: 200px;">Emails Queued:</td>
                        <td style="padding: 4px 0;"><?php echo (int)$success['queued']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold;">Recipient:</td>
                        <td style="padding: 4px 0;"><?php echo esc($success['recipient']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold;">First Send Time:</td>
                        <td style="padding: 4px 0;"><?php echo esc(date('Y-m-d H:i:s', $success['start_time'])); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold;">Last Send Time:</td>
                        <td style="padding: 4px 0;"><?php echo esc(date('Y-m-d H:i:s', $success['end_time'])); ?></td>
                    </tr>
                </table>
                <p style="margin: 12px 0 0 0;">
                    <strong>Next Steps:</strong> View the <a href="/admin/email-queue/">Email Queue</a> to see your scheduled emails.
                </p>
            </div>
        <?php endif; ?>

        <?php if ($error !== null): ?>
            <div style="padding: 12px; margin: 16px 0; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                <strong>Error:</strong> <?php echo esc($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" style="max-width: 900px;">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="schedule_test_campaign">

            <div style="margin-bottom: 20px;">
                <label for="campaign_json" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Campaign JSON: <span style="color: #dc3545;">*</span>
                </label>
                <textarea
                    id="campaign_json"
                    name="campaign_json"
                    rows="12"
                    required
                    placeholder='Paste the JSON output from AI Email Campaign Generator here...'
                    style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 4px; font-family: monospace; font-size: 12px;"
                ><?php echo esc($form['campaign_json']); ?></textarea>
                <small style="color: #6c757d;">
                    The JSON must contain an "emails" array with objects that have "subject" and "html_body" fields.
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="recipient_email" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Test Recipient Email: <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="email"
                    id="recipient_email"
                    name="recipient_email"
                    value="<?php echo esc($form['recipient_email']); ?>"
                    placeholder="test@example.com"
                    required
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;"
                >
                <small style="color: #6c757d;">
                    All emails in the campaign will be sent to this address for testing.
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="start_time" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Start Time (optional):
                </label>
                <input
                    type="text"
                    id="start_time"
                    name="start_time"
                    value="<?php echo esc($form['start_time']); ?>"
                    placeholder='e.g. "now", "tomorrow 09:00", "2025-12-05 10:30"'
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;"
                >
                <small style="color: #6c757d;">
                    When to send the first email. Leave empty to start immediately. Uses PHP strtotime() format.
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="interval_minutes" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Interval Between Emails (minutes):
                </label>
                <input
                    type="number"
                    id="interval_minutes"
                    name="interval_minutes"
                    min="0"
                    max="10080"
                    value="<?php echo esc($form['interval_minutes']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;"
                >
                <small style="color: #6c757d;">
                    Time between each email in the sequence. Default: 1440 minutes (1 day). Max: 10080 (7 days).
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="subject_prefix" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Subject Line Prefix (optional):
                </label>
                <input
                    type="text"
                    id="subject_prefix"
                    name="subject_prefix"
                    value="<?php echo esc($form['subject_prefix']); ?>"
                    placeholder='e.g. "[TEST] "'
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;"
                >
                <small style="color: #6c757d;">
                    Optional prefix to add to all subject lines (e.g. "[TEST] " for test campaigns).
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <button
                    type="submit"
                    style="padding: 12px 24px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;"
                >
                    Schedule Campaign
                </button>
            </div>
        </form>

        <div style="padding: 16px; margin: 32px 0; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
            <h3 style="margin: 0 0 12px 0;">Technical Details</h3>
            <ul style="margin: 8px 0 0 20px; padding: 0; color: #6c757d;">
                <li>Emails are inserted into the existing <code>email_queue</code> table</li>
                <li>If the table has a <code>scheduled_at</code> column, it will be used for precise scheduling</li>
                <li>Otherwise, <code>created_at</code> timestamps are staggered to control send order</li>
                <li>All emails are marked as 'pending' status for the scheduler to process</li>
                <li>Phase 1 scope: single test recipient only, no production multi-recipient support</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
