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
    exit('Forbidden: DEV_MODE is not enabled');
}

require_once CMS_ROOT . '/core/ai_hf.php';

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$error_message = '';
$show_results = false;
$input_text = '';
$mode = 'explain';
$description = '';
$use_ai = false;
$ai_used = false;
$fallback_used = false;
$analysis = '';
$recommendations = [];
$patch = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $input_text = trim($_POST['input_text'] ?? '');
    $mode = trim($_POST['mode'] ?? 'explain');
    $description = trim($_POST['description'] ?? '');
    $use_ai = isset($_POST['use_ai']);

    if (strlen($input_text) > 20000) {
        $input_text = substr($input_text, 0, 20000);
    }

    if (strlen($description) > 2000) {
        $description = substr($description, 0, 2000);
    }

    if (!in_array($mode, ['explain', 'fix', 'patch', 'refactor'], true)) {
        $mode = 'explain';
    }

    if (empty($input_text)) {
        $error_message = 'Input text is required and cannot be empty.';
    } else {
        $show_results = true;

        if ($use_ai) {
            $config = ai_hf_config_load();
            $is_configured = ai_hf_is_configured($config);

            if (!$is_configured) {
                $fallback_used = true;
                $ai_used = false;
            } else {
                $truncated_input = substr($input_text, 0, 12000);

                $prompt = "You are a helpful code assistant. Analyze the following ";
                $prompt .= $mode === 'explain' ? "code or error" : ($mode === 'fix' ? "error to fix" : ($mode === 'patch' ? "code for patching" : "code for refactoring"));
                $prompt .= ".\n\n";

                if (!empty($description)) {
                    $prompt .= "Context: " . $description . "\n\n";
                }

                $prompt .= "Input:\n" . $truncated_input . "\n\n";
                $prompt .= "Return ONLY valid JSON with these exact keys:\n";
                $prompt .= "{\n";
                $prompt .= '  "analysis": "Explanation of the error or code behaviour.",';
                $prompt .= "\n";
                $prompt .= '  "recommendations": ["Actionable step 1", "Actionable step 2"],';
                $prompt .= "\n";
                $prompt .= '  "patch": "Unified diff patch as text or an empty string if no patch."';
                $prompt .= "\n}\n\n";
                $prompt .= "Do not include backticks or markdown. Return only the JSON object.";

                $ai_result = ai_hf_infer($config, $prompt, [
                    'max_new_tokens' => 800,
                    'temperature' => 0.4,
                    'top_p' => 0.9
                ]);

                if ($ai_result['success'] && !empty($ai_result['text'])) {
                    $raw_text = trim($ai_result['text']);
                    $decoded = json_decode($raw_text, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $analysis = $decoded['analysis'] ?? '';
                        $recommendations = is_array($decoded['recommendations'] ?? null) ? $decoded['recommendations'] : [];
                        $patch = $decoded['patch'] ?? '';
                        $ai_used = true;
                        $fallback_used = false;
                    } else {
                        $fallback_used = true;
                        $ai_used = false;
                    }
                } else {
                    $fallback_used = true;
                    $ai_used = false;
                }
            }
        } else {
            $fallback_used = true;
            $ai_used = false;
        }

        if ($fallback_used) {
            if ($mode === 'explain') {
                $analysis = "AI is unavailable. Manually inspect the stacktrace or error message. Focus on the first PHP fatal error or uncaught exception and check the referenced file and line number.";
                $recommendations = [
                    "Enable DEV_MODE in a safe environment to see full error details",
                    "Log variable values at key points using error_log() or var_dump()",
                    "Isolate the failing function and test it independently",
                    "Check recent code changes that might have introduced the issue"
                ];
            } elseif ($mode === 'fix') {
                $analysis = "AI is unavailable. No automatic fix was generated.";
                $recommendations = [
                    "Write a small reproducible example to isolate the problem",
                    "Add var_dump() or error_log() statements to trace execution",
                    "Test your assumptions about variable types and values",
                    "Check recent changes in version control",
                    "Review similar working code in your codebase"
                ];
            } elseif ($mode === 'patch') {
                $analysis = "AI is unavailable. No unified diff patch could be generated.";
                $recommendations = [
                    "Craft a manual patch using diff -u or your editor",
                    "Run the patch through your code review process",
                    "Test the patch in a development environment first",
                    "Consider using Claude or another AI assistant for patch generation"
                ];
                $patch = '';
            } elseif ($mode === 'refactor') {
                $analysis = "AI is unavailable. No refactor plan generated.";
                $recommendations = [
                    "Break large functions into smaller, single-purpose helpers",
                    "Extract repeated code into reusable functions",
                    "Avoid deep nesting by using early returns",
                    "Centralize database access via core/Database::connection()",
                    "Remove dead code and unused variables",
                    "Add type hints to function parameters and return types"
                ];
                $patch = '';
            }
        }
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="container" style="max-width:1200px;margin:20px auto;padding:0 20px;">
    <h1>AI Developer Assistant</h1>
    <p style="color:#666;margin-bottom:20px;">
        Paste code, stacktraces, error messages, logs, or JSON to get structured analysis and recommendations.
        This tool is READ-ONLY and never modifies any files.
    </p>

    <?php if (!empty($error_message)): ?>
        <div style="background:#fee;border:1px solid #c33;color:#c33;padding:12px;margin-bottom:20px;border-radius:4px;">
            <strong>Error:</strong> <?= esc($error_message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="background:#f9f9f9;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:30px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom:15px;">
            <label for="input_text" style="display:block;margin-bottom:5px;font-weight:bold;">
                Input Text <span style="color:#c33;">*</span>
            </label>
            <textarea
                name="input_text"
                id="input_text"
                rows="12"
                required
                style="width:100%;font-family:monospace;font-size:13px;padding:8px;border:1px solid #ccc;border-radius:3px;"
            ><?= esc($input_text) ?></textarea>
            <small style="color:#666;">Maximum 20,000 characters. Required.</small>
        </div>

        <div style="margin-bottom:15px;">
            <label for="mode" style="display:block;margin-bottom:5px;font-weight:bold;">
                Analysis Mode
            </label>
            <select
                name="mode"
                id="mode"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;"
            >
                <option value="explain" <?= $mode === 'explain' ? 'selected' : '' ?>>Explain (understand error or code)</option>
                <option value="fix" <?= $mode === 'fix' ? 'selected' : '' ?>>Fix (suggest a solution)</option>
                <option value="patch" <?= $mode === 'patch' ? 'selected' : '' ?>>Patch (generate unified diff)</option>
                <option value="refactor" <?= $mode === 'refactor' ? 'selected' : '' ?>>Refactor (improve code quality)</option>
            </select>
        </div>

        <div style="margin-bottom:15px;">
            <label for="description" style="display:block;margin-bottom:5px;font-weight:bold;">
                Description / Context (optional)
            </label>
            <textarea
                name="description"
                id="description"
                rows="4"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;"
            ><?= esc($description) ?></textarea>
            <small style="color:#666;">Maximum 2,000 characters. Optional context for better analysis.</small>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:flex;align-items:center;gap:8px;">
                <input
                    type="checkbox"
                    name="use_ai"
                    <?= $use_ai ? 'checked' : '' ?>
                >
                <span>Use Hugging Face AI for analysis (if configured)</span>
            </label>
        </div>

        <button
            type="submit"
            style="background:#0066cc;color:#fff;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;"
        >
            Analyze
        </button>
    </form>

    <?php if ($show_results): ?>
        <?php if ($ai_used): ?>
            <div style="background:#e8f5e9;border:1px solid #4caf50;color:#2e7d32;padding:12px;margin-bottom:20px;border-radius:4px;">
                <strong>AI Used:</strong> Hugging Face analysis applied.
            </div>
        <?php elseif ($fallback_used): ?>
            <div style="background:#fff8e1;border:1px solid #ffc107;color:#f57c00;padding:12px;margin-bottom:20px;border-radius:4px;">
                <strong>Fallback Used:</strong> No AI response. Showing static guidance.
            </div>
        <?php endif; ?>

        <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                Summary
            </h2>
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;width:200px;">Mode:</td>
                    <td style="padding:8px;border-bottom:1px solid #eee;"><?= esc(ucfirst($mode)) ?></td>
                </tr>
                <tr>
                    <td style="padding:8px;font-weight:bold;">Use AI:</td>
                    <td style="padding:8px;"><?= $use_ai ? 'Yes' : 'No' ?></td>
                </tr>
            </table>
        </div>

        <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                Analysis
            </h2>
            <div style="line-height:1.6;">
                <?php if (!empty($analysis)): ?>
                    <p><?= esc($analysis) ?></p>
                <?php else: ?>
                    <p style="color:#999;">No analysis available.</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                Recommendations
            </h2>
            <?php if (!empty($recommendations)): ?>
                <ul style="line-height:1.8;margin:0;padding-left:20px;">
                    <?php foreach ($recommendations as $rec): ?>
                        <li><?= esc($rec) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color:#999;font-size:13px;">No recommendations available.</p>
            <?php endif; ?>
        </div>

        <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                Suggested Patch (Unified Diff)
            </h2>
            <?php if (!empty($patch)): ?>
                <pre style="background:#f5f5f5;padding:12px;border:1px solid #ddd;border-radius:3px;overflow-x:auto;font-family:monospace;font-size:12px;line-height:1.4;"><?= esc($patch) ?></pre>
            <?php else: ?>
                <p style="color:#999;font-size:13px;">No patch generated.</p>
            <?php endif; ?>
        </div>

        <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;">
            <h2 style="margin-top:0;font-size:18px;border-bottom:2px solid #0066cc;padding-bottom:8px;margin-bottom:15px;">
                Original Input
            </h2>
            <pre style="background:#f5f5f5;padding:12px;border:1px solid #ddd;border-radius:3px;max-height:400px;overflow:auto;font-family:monospace;font-size:12px;line-height:1.4;"><?= esc($input_text) ?></pre>
        </div>
    <?php endif; ?>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
