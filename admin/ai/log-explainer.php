<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied.');
}

require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_hf.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

if (!function_exists('esc')) {
    function esc($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

function tailFile($path, $maxLines) {
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }

    $handle = @fopen($path, 'r');
    if ($handle === false) {
        return '';
    }

    $fileSize = filesize($path);
    if ($fileSize === 0) {
        fclose($handle);
        return '';
    }

    $lines = [];
    $buffer = '';
    $chunkSize = 8192;
    $pos = max(0, $fileSize - ($maxLines * 200));

    fseek($handle, $pos);

    while (!feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        if ($chunk === false) {
            break;
        }
        $buffer .= $chunk;
    }

    fclose($handle);

    $allLines = explode("\n", $buffer);
    $lines = array_slice($allLines, -$maxLines);

    return implode("\n", $lines);
}

$logOptions = [
    'app_errors' => [
        'label' => 'Application Errors (app_errors.log)',
        'path'  => CMS_ROOT . '/logs/app_errors.log',
    ],
    'php_errors' => [
        'label' => 'PHP Errors (php_errors.log)',
        'path'  => CMS_ROOT . '/logs/php_errors.log',
    ],
];

$selectedLogKey = 'app_errors';
$lineCount = 200;
$maxLineCount = 1000;
$useAi = false;

$errors = [];
$excerpt = '';
$aiUsed = false;
$aiWarning = null;
$aiResult = [
    'summary' => null,
    'probable_causes' => [],
    'recommendations' => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $selectedLogKey = isset($_POST['log']) ? trim((string)$_POST['log']) : 'app_errors';
    if (!isset($logOptions[$selectedLogKey])) {
        $selectedLogKey = 'app_errors';
    }

    $lineCount = isset($_POST['lines']) ? (int)$_POST['lines'] : 200;
    if ($lineCount <= 0 || $lineCount > $maxLineCount) {
        $lineCount = 200;
    }
    $lineCount = max(10, min($maxLineCount, $lineCount));

    $useAi = isset($_POST['use_ai']) && $_POST['use_ai'] === '1';

    $logPath = $logOptions[$selectedLogKey]['path'];
    $excerpt = tailFile($logPath, $lineCount);

    if ($excerpt === '' || trim($excerpt) === '') {
        $errors[] = 'The selected log file is missing or empty.';
    }

    if ($useAi && empty($errors) && $excerpt !== '') {
        $hfConfig = ai_hf_config_load();
        $hfConfigured = ai_hf_is_configured($hfConfig);

        if (!$hfConfigured) {
            $aiWarning = 'Hugging Face is not configured. Showing raw log excerpt only.';
        } else {
            $excerptTruncated = substr($excerpt, -5000);

            $promptParts = [];
            $promptParts[] = 'You are a log analysis expert. Analyze the following log excerpt and provide structured diagnostics.';
            $promptParts[] = 'Log excerpt:';
            $promptParts[] = $excerptTruncated;
            $promptParts[] = 'Respond with JSON ONLY, no markdown, no extra text.';
            $promptParts[] = 'Use this exact schema:';
            $promptParts[] = '{"summary":"Short summary of main issues (max 3 sentences)","probable_causes":["Cause 1","Cause 2"],"recommendations":["Action 1","Action 2"]}';
            $promptParts[] = 'Requirements: summary must be a string, probable_causes and recommendations must be arrays of strings.';

            $prompt = implode("\n\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.5,
                'top_p' => 0.9
            ];

            $result = ai_hf_infer($hfConfig, $prompt, $options);

            if (!$result['ok'] || $result['status'] < 200 || $result['status'] >= 300) {
                $aiWarning = 'AI analysis failed. Showing raw log excerpt only.';
            } else {
                $aiData = null;
                if (is_array($result['json'])) {
                    $aiData = $result['json'];
                } else {
                    $decoded = @json_decode($result['body'], true);
                    if (is_array($decoded)) {
                        $aiData = $decoded;
                    }
                }

                if ($aiData === null || !is_array($aiData)) {
                    $aiWarning = 'AI returned an unexpected format. Showing raw log excerpt only.';
                } else {
                    $aiResult['summary'] = isset($aiData['summary']) ? trim(substr((string)$aiData['summary'], 0, 500)) : null;

                    if (isset($aiData['probable_causes']) && is_array($aiData['probable_causes'])) {
                        $aiResult['probable_causes'] = array_slice($aiData['probable_causes'], 0, 10);
                    }

                    if (isset($aiData['recommendations']) && is_array($aiData['recommendations'])) {
                        $aiResult['recommendations'] = array_slice($aiData['recommendations'], 0, 10);
                    }

                    $aiUsed = true;
                }
            }
        }
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>
<main class="container">
    <h1>AI Log Explainer</h1>

    <p style="margin-bottom: 1.5rem; color: #666;">
        This DEV_MODE-only tool reads application and PHP error logs for analysis. It is read-only and does not modify any files.
    </p>

    <?php if (!empty($errors)): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
            <strong>Error:</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($aiWarning !== null): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> <?= esc($aiWarning) ?>
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 800px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom: 1rem;">
            <label for="log"><strong>Log File</strong></label>
            <select id="log" name="log" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <?php foreach ($logOptions as $key => $option): ?>
                    <option value="<?= esc($key) ?>"<?= $selectedLogKey === $key ? ' selected' : '' ?>><?= esc($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <small style="color: #666;">Select which log file to analyze</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="lines"><strong>Number of Lines</strong></label>
            <input type="number" id="lines" name="lines" value="<?= esc((string)$lineCount) ?>" min="10" max="<?= $maxLineCount ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <small style="color: #666;">Tail last N lines (min: 10, max: <?= $maxLineCount ?>)</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block;">
                <input type="checkbox" name="use_ai" value="1"<?= $useAi ? ' checked' : '' ?>>
                <strong>Use Hugging Face AI analysis</strong>
            </label>
            <small style="color: #666; display: block; margin-left: 1.5rem;">Enable AI-powered log analysis (requires Hugging Face configuration)</small>
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Analyze Logs</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $excerpt !== '' && empty($errors)): ?>
        <div style="margin-top: 2rem;">
            <?php if ($aiUsed && ($aiResult['summary'] !== null || !empty($aiResult['probable_causes']) || !empty($aiResult['recommendations']))): ?>
                <div style="margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #28a745; background: #d4edda; border-radius: 4px;">
                    <h2 style="margin: 0 0 1rem 0; color: #155724;">AI Analysis</h2>

                    <?php if ($aiResult['summary'] !== null): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: #155724;">Summary</h3>
                            <p style="margin: 0; color: #155724;"><?= esc($aiResult['summary']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($aiResult['probable_causes'])): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: #155724;">Probable Causes</h3>
                            <ul style="margin: 0; padding-left: 1.5rem; color: #155724;">
                                <?php foreach ($aiResult['probable_causes'] as $cause): ?>
                                    <li><?= esc($cause) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($aiResult['recommendations'])): ?>
                        <div>
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: #155724;">Recommendations</h3>
                            <ul style="margin: 0; padding-left: 1.5rem; color: #155724;">
                                <?php foreach ($aiResult['recommendations'] as $rec): ?>
                                    <li><?= esc($rec) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="padding: 1.5rem; border: 1px solid #ccc; background: #f9f9f9; border-radius: 4px;">
                <h2 style="margin: 0 0 1rem 0;">Raw Log Excerpt</h2>
                <div style="margin-bottom: 0.5rem; padding: 0.5rem; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px;">
                    <strong>File:</strong> <?= esc($logOptions[$selectedLogKey]['label']) ?>
                    <br>
                    <strong>Lines:</strong> Last <?= esc((string)$lineCount) ?> lines
                </div>
                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 1rem; border-radius: 4px; overflow-x: auto; max-height: 600px; overflow-y: auto; font-size: 0.875rem; line-height: 1.4;"><?= esc($excerpt) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
