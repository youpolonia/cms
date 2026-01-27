<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function load_email_automation_config() {
    $configPath = CMS_ROOT . '/config/ai_email_automation.json';

    if (!file_exists($configPath) || !is_readable($configPath)) {
        return ['sequences' => []];
    }

    $content = @file_get_contents($configPath);
    if ($content === false) {
        return ['sequences' => []];
    }

    $decoded = @json_decode($content, true);
    if (!is_array($decoded)) {
        return ['sequences' => []];
    }

    if (!isset($decoded['sequences']) || !is_array($decoded['sequences'])) {
        return ['sequences' => []];
    }

    return $decoded;
}

$config = load_email_automation_config();
$sequences = $config['sequences'];

$errors = [];
$preview_steps = [];
$show_preview = false;
$selected_sequence = null;
$base_date_input = '';
$recipient_email_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $sequence_id = trim($_POST['sequence_id'] ?? '');
    $base_date_input = trim($_POST['base_date'] ?? '');
    $recipient_email_input = trim($_POST['recipient_email'] ?? '');

    if (empty($sequence_id)) {
        $errors[] = 'Sequence selection is required.';
    } else {
        $found = false;
        foreach ($sequences as $seq) {
            if (isset($seq['id']) && $seq['id'] === $sequence_id) {
                $selected_sequence = $seq;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $errors[] = 'Selected sequence does not exist.';
        }
    }

    if (empty($base_date_input)) {
        $errors[] = 'Base date is required.';
    } else {
        $baseDate = DateTime::createFromFormat('Y-m-d', $base_date_input);
        if (!$baseDate || $baseDate->format('Y-m-d') !== $base_date_input) {
            $errors[] = 'Base date must be in YYYY-MM-DD format.';
            $baseDate = null;
        }
    }

    if (!empty($recipient_email_input)) {
        $atPos = strpos($recipient_email_input, '@');
        if ($atPos === false || strpos($recipient_email_input, '.', $atPos) === false) {
            $errors[] = 'Recipient email is invalid.';
        }
    }

    if (empty($errors) && $selected_sequence !== null && isset($baseDate)) {
        $steps = $selected_sequence['steps'] ?? [];

        foreach ($steps as $index => $step) {
            $offset_days = isset($step['offset_days']) && is_numeric($step['offset_days'])
                ? intval($step['offset_days'])
                : 0;

            $scheduledDate = clone $baseDate;
            if ($offset_days > 0) {
                $scheduledDate->modify("+{$offset_days} days");
            }

            $preview_steps[] = [
                'step_index' => $index + 1,
                'offset_days' => $offset_days,
                'scheduled_date' => $scheduledDate->format('Y-m-d'),
                'subject' => $step['subject'] ?? '',
                'preview' => $step['preview'] ?? '',
                'cta' => $step['cta_label'] ?? '',
                'html' => $step['html'] ?? '',
                'text' => $step['text'] ?? ''
            ];
        }

        $show_preview = true;
    }
} else {
    $base_date_input = date('Y-m-d');
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="container" style="max-width:1200px;margin:20px auto;padding:0 20px;">
    <h1>AI Email Automation Preview</h1>
    <p style="color:#666;margin-bottom:20px;">
        Preview email automation sequences and their computed schedules. This tool is READ-ONLY and does not send emails, modify configurations, or call AI services.
    </p>

    <?php if (count($sequences) === 0): ?>
        <div style="background:#e7f3ff;border:1px solid #0066cc;color:#004080;padding:12px;margin-bottom:20px;border-radius:4px;">
            <strong>Info:</strong> No email automation sequences are defined yet. Configure them under 'AI Email Automation'.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background:#fee;border:1px solid #c33;color:#c33;padding:12px;margin-bottom:20px;border-radius:4px;">
            <strong>Validation Errors:</strong>
            <ul style="margin:8px 0 0 20px;padding:0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" style="background:#f9f9f9;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:30px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom:15px;">
            <label for="sequence_id" style="display:block;margin-bottom:5px;font-weight:bold;">
                Sequence <span style="color:#c33;">*</span>
            </label>
            <select
                name="sequence_id"
                id="sequence_id"
                required
                <?= count($sequences) === 0 ? 'disabled' : '' ?>
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;"
            >
                <option value="">-- Select Sequence --</option>
                <?php foreach ($sequences as $seq): ?>
                    <?php
                    $seqId = $seq['id'] ?? '';
                    $seqName = $seq['name'] ?? 'Unnamed';
                    $selected = ($show_preview && $selected_sequence !== null && ($selected_sequence['id'] ?? '') === $seqId) ? 'selected' : '';
                    ?>
                    <option value="<?= esc($seqId) ?>" <?= $selected ?>>
                        <?= esc($seqName) ?> (<?= esc($seqId) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:#666;">Select an email automation sequence to preview.</small>
        </div>

        <div style="margin-bottom:15px;">
            <label for="base_date" style="display:block;margin-bottom:5px;font-weight:bold;">
                Base Date <span style="color:#c33;">*</span>
            </label>
            <input
                type="date"
                name="base_date"
                id="base_date"
                value="<?= esc($base_date_input) ?>"
                required
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;"
            >
            <small style="color:#666;">Starting date for computing scheduled send dates.</small>
        </div>

        <div style="margin-bottom:20px;">
            <label for="recipient_email" style="display:block;margin-bottom:5px;font-weight:bold;">
                Recipient Email (Preview Only)
            </label>
            <input
                type="text"
                name="recipient_email"
                id="recipient_email"
                value="<?= esc($recipient_email_input) ?>"
                placeholder="user@example.com"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;"
            >
            <small style="color:#666;">Optional. For display only, not used for sending.</small>
        </div>

        <button
            type="submit"
            style="background:#0066cc;color:#fff;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;"
            <?= count($sequences) === 0 ? 'disabled' : '' ?>
        >
            Preview Sequence
        </button>
    </form>

    <?php if ($show_preview && $selected_sequence !== null): ?>
        <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                Sequence Summary
            </h2>
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;width:200px;">Sequence Name:</td>
                    <td style="padding:8px;border-bottom:1px solid #eee;"><?= esc($selected_sequence['name'] ?? '') ?></td>
                </tr>
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Sequence ID:</td>
                    <td style="padding:8px;border-bottom:1px solid #eee;"><?= esc($selected_sequence['id'] ?? '') ?></td>
                </tr>
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Event Key:</td>
                    <td style="padding:8px;border-bottom:1px solid #eee;"><?= esc($selected_sequence['event_key'] ?? '') ?></td>
                </tr>
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Base Date:</td>
                    <td style="padding:8px;border-bottom:1px solid #eee;"><?= esc($base_date_input) ?></td>
                </tr>
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Recipient Email:</td>
                    <td style="padding:8px;border-bottom:1px solid #eee;">
                        <?= !empty($recipient_email_input) ? esc($recipient_email_input) : '(not specified)' ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px;font-weight:bold;">Number of Steps:</td>
                    <td style="padding:8px;"><?= count($preview_steps) ?></td>
                </tr>
            </table>
        </div>

        <?php if (count($preview_steps) === 0): ?>
            <div style="background:#fff8e1;border:1px solid #ffc107;color:#f57c00;padding:12px;margin-bottom:20px;border-radius:4px;">
                <strong>Note:</strong> This sequence has no steps defined yet.
            </div>
        <?php else: ?>
            <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;">
                <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                    Steps Overview
                </h2>
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5f5f5;">
                            <th style="padding:10px;text-align:left;border-bottom:2px solid #ddd;">#</th>
                            <th style="padding:10px;text-align:left;border-bottom:2px solid #ddd;">Offset</th>
                            <th style="padding:10px;text-align:left;border-bottom:2px solid #ddd;">Scheduled Date</th>
                            <th style="padding:10px;text-align:left;border-bottom:2px solid #ddd;">Subject</th>
                            <th style="padding:10px;text-align:left;border-bottom:2px solid #ddd;">CTA</th>
                            <th style="padding:10px;text-align:left;border-bottom:2px solid #ddd;">Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview_steps as $pstep): ?>
                            <tr>
                                <td style="padding:10px;border-bottom:1px solid #eee;"><?= esc($pstep['step_index']) ?></td>
                                <td style="padding:10px;border-bottom:1px solid #eee;">
                                    +<?= esc($pstep['offset_days']) ?> days
                                </td>
                                <td style="padding:10px;border-bottom:1px solid #eee;"><?= esc($pstep['scheduled_date']) ?></td>
                                <td style="padding:10px;border-bottom:1px solid #eee;"><?= esc($pstep['subject']) ?></td>
                                <td style="padding:10px;border-bottom:1px solid #eee;"><?= esc($pstep['cta']) ?></td>
                                <td style="padding:10px;border-bottom:1px solid #eee;"><?= esc($pstep['preview']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;">
                <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                    Detailed Email Bodies
                </h2>
                <?php foreach ($preview_steps as $pstep): ?>
                    <div style="margin-bottom:30px;padding-bottom:20px;border-bottom:1px solid #eee;">
                        <h3 style="font-size:16px;margin:0 0 15px 0;color:#333;">
                            Step <?= esc($pstep['step_index']) ?> – <?= esc($pstep['subject']) ?>
                            <span style="color:#666;font-weight:normal;">(Scheduled: <?= esc($pstep['scheduled_date']) ?>)</span>
                        </h3>

                        <div style="margin-bottom:15px;">
                            <label style="display:block;font-weight:bold;margin-bottom:5px;">HTML Body</label>
                            <textarea
                                readonly
                                rows="8"
                                style="width:100%;font-family:monospace;font-size:12px;padding:8px;border:1px solid #ccc;border-radius:3px;background:#f9f9f9;"
                            ><?= esc($pstep['html']) ?></textarea>
                        </div>

                        <div>
                            <label style="display:block;font-weight:bold;margin-bottom:5px;">Text Body</label>
                            <textarea
                                readonly
                                rows="6"
                                style="width:100%;font-family:monospace;font-size:12px;padding:8px;border:1px solid #ccc;border-radius:3px;background:#f9f9f9;"
                            ><?= esc($pstep['text']) ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
