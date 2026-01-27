<?php
/**
 * AI Toolkit - Admin UI
 * Generic text processing utility using AI
 * Non-destructive: no DB writes, no file writes
 */

// Define CMS_ROOT if needed
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

// Bootstrap
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_toolkit.php';

// Start session and initialize CSRF protection
cms_session_start('admin');
csrf_boot();

// Require admin role
cms_require_admin_role();

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Load AI configuration for display
$aiConfig = ai_config_load();
$aiProvider = !empty($aiConfig['provider']) ? htmlspecialchars($aiConfig['provider'], ENT_QUOTES, 'UTF-8') : '<em>not configured</em>';
$aiModel = !empty($aiConfig['model']) ? htmlspecialchars($aiConfig['model'], ENT_QUOTES, 'UTF-8') : '<em>not set</em>';

// Initialize request variables
$mode = isset($_POST['mode']) ? trim($_POST['mode']) : 'summarize';
$inputText = isset($_POST['input_text']) ? $_POST['input_text'] : '';
$targetLang = isset($_POST['target_language']) ? trim($_POST['target_language']) : '';
$result = null;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Build options array
    $options = [];
    if ($targetLang !== '') {
        $options['target_language'] = $targetLang;
    }

    // Run AI Toolkit operation
    $result = ai_toolkit_run($mode, $inputText, $options);
}

// Include header and navigation
require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Toolkit - Admin</title>
    <style>
        .ai-info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .ai-info-box strong {
            display: inline-block;
            min-width: 80px;
        }
        .toolkit-form {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
        }
        .form-group textarea {
            font-family: 'Courier New', Courier, monospace;
            resize: vertical;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .result-box {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 4px;
            padding: 20px;
            margin-top: 20px;
        }
        .result-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #004085;
        }
        .result-box textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #b3d7ff;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            background: #ffffff;
            resize: vertical;
        }
        .result-box .helper-text {
            margin-top: 10px;
            font-size: 13px;
            color: #666;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            color: #721c24;
        }
    </style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

    <main class="container" style="max-width: 900px; margin: 0 auto; padding: 20px;">
        <h1>AI Toolkit</h1>
        <p>Generic text processing utility. Run various AI operations on arbitrary input text.</p>

        <!-- AI Configuration Info -->
        <div class="ai-info-box">
            <div><strong>Provider:</strong> <?php echo $aiProvider; ?></div>
            <div><strong>Model:</strong> <?php echo $aiModel; ?></div>
        </div>

        <!-- Main Form -->
        <form method="post" action="" class="toolkit-form">
            <?php csrf_field(); ?>

            <!-- Mode Selection -->
            <div class="form-group">
                <label for="mode">Operation Mode:</label>
                <select name="mode" id="mode">
                    <option value="summarize" <?php echo ($mode === 'summarize') ? 'selected' : ''; ?>>Summarize text</option>
                    <option value="expand" <?php echo ($mode === 'expand') ? 'selected' : ''; ?>>Expand text</option>
                    <option value="rewrite" <?php echo ($mode === 'rewrite') ? 'selected' : ''; ?>>Rewrite text</option>
                    <option value="simplify" <?php echo ($mode === 'simplify') ? 'selected' : ''; ?>>Simplify text</option>
                    <option value="bullet_points" <?php echo ($mode === 'bullet_points') ? 'selected' : ''; ?>>Convert to bullet points</option>
                    <option value="title" <?php echo ($mode === 'title') ? 'selected' : ''; ?>>Generate title</option>
                    <option value="meta_description" <?php echo ($mode === 'meta_description') ? 'selected' : ''; ?>>Generate meta description</option>
                    <option value="translate_en" <?php echo ($mode === 'translate_en') ? 'selected' : ''; ?>>Translate to English</option>
                    <option value="translate_pl" <?php echo ($mode === 'translate_pl') ? 'selected' : ''; ?>>Translate to Polish</option>
                </select>
            </div>

            <!-- Target Language (optional, for translation modes) -->
            <div class="form-group">
                <label for="target_language">Target Language (optional):</label>
                <select name="target_language" id="target_language">
                    <option value="">-- Auto --</option>
                    <option value="en" <?php echo ($targetLang === 'en') ? 'selected' : ''; ?>>English</option>
                    <option value="pl" <?php echo ($targetLang === 'pl') ? 'selected' : ''; ?>>Polish</option>
                </select>
            </div>

            <!-- Input Text -->
            <div class="form-group">
                <label for="input_text">Input Text: <span style="font-weight: normal; color: #666;">(required)</span></label>
                <textarea name="input_text" id="input_text" rows="12" required><?php echo htmlspecialchars($inputText, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Run AI Toolkit</button>
        </form>

        <!-- Result Area -->
        <?php if ($result !== null): ?>
            <?php if (!$result['ok']): ?>
                <!-- Error Message -->
                <div class="error-box">
                    <strong>Error:</strong> <?php echo htmlspecialchars($result['error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php elseif (!empty($result['output'])): ?>
                <!-- Success - Show Output -->
                <div class="result-box">
                    <h3>Result</h3>
                    <textarea readonly rows="18"><?php echo htmlspecialchars($result['output'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <div class="helper-text">
                        ✓ Copy this result into your content or templates.
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
