<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('AI Translation is only available in development mode');
}

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_translation.php';

$sourceText = '';
$result = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $sourceText = $_POST['text'] ?? '';
    $sourceLang = $_POST['source_language'] ?? 'Auto detect';
    $targetLang = $_POST['target_language'] ?? 'English';
    $tone = $_POST['tone'] ?? '';
    $context = $_POST['context'] ?? '';

    $result = ai_translation_translate([
        'text' => $sourceText,
        'source_language' => $sourceLang,
        'target_language' => $targetLang,
        'tone' => $tone,
        'context' => $context
    ]);

    if (!$result['ok']) {
        $errors[] = $result['message'] ?? 'Translation failed';
    }
}

$languages = ai_translation_supported_languages();

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="container" style="max-width:1200px;margin:24px auto;padding:0 16px">
    <h1>AI Translation</h1>
    <p style="color:#666;margin-bottom:24px">
        Translate text using AI-powered translation. Choose source and target languages, optionally specify tone and context.
    </p>

    <?php if (!empty($errors)): ?>
        <div style="padding:12px;background:#fee;border:1px solid #c33;color:#c33;margin-bottom:16px;border-radius:4px">
            <strong>Error:</strong>
            <ul style="margin:8px 0 0 20px">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" style="background:#f9f9f9;padding:20px;border:1px solid #ddd;border-radius:6px;margin-bottom:32px">
        <?php csrf_field(); ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:6px">Source Language</label>
                <select
                    name="source_language"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
                >
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?= esc($lang) ?>"
                            <?= (isset($_POST['source_language']) && $_POST['source_language'] === $lang) ? 'selected' : '' ?>>
                            <?= esc($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:6px">Target Language</label>
                <select
                    name="target_language"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
                >
                    <?php foreach ($languages as $lang): ?>
                        <?php if ($lang === 'Auto detect') continue; ?>
                        <option value="<?= esc($lang) ?>"
                            <?= (isset($_POST['target_language']) && $_POST['target_language'] === $lang) || (!isset($_POST['target_language']) && $lang === 'English') ? 'selected' : '' ?>>
                            <?= esc($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:6px">Tone (Optional)</label>
                <select
                    name="tone"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
                >
                    <option value="">Default</option>
                    <option value="Neutral" <?= (isset($_POST['tone']) && $_POST['tone'] === 'Neutral') ? 'selected' : '' ?>>Neutral</option>
                    <option value="Formal" <?= (isset($_POST['tone']) && $_POST['tone'] === 'Formal') ? 'selected' : '' ?>>Formal</option>
                    <option value="Casual" <?= (isset($_POST['tone']) && $_POST['tone'] === 'Casual') ? 'selected' : '' ?>>Casual</option>
                    <option value="Marketing" <?= (isset($_POST['tone']) && $_POST['tone'] === 'Marketing') ? 'selected' : '' ?>>Marketing</option>
                    <option value="Technical" <?= (isset($_POST['tone']) && $_POST['tone'] === 'Technical') ? 'selected' : '' ?>>Technical</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:6px">Context (Optional)</label>
                <input
                    type="text"
                    name="context"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
                    placeholder="e.g., Blog post for parents, Email campaign"
                    value="<?= esc($_POST['context'] ?? '') ?>"
                />
            </div>
        </div>

        <div style="margin-bottom:20px">
            <label style="display:block;font-weight:bold;margin-bottom:6px">Text to Translate</label>
            <textarea
                name="text"
                rows="10"
                required
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;font-family:inherit"
                placeholder="Paste or type the text you want to translate..."
            ><?= esc($sourceText) ?></textarea>
        </div>

        <button
            type="submit"
            style="padding:10px 20px;background:#0066cc;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold"
        >
            Translate with AI
        </button>
    </form>

    <?php if ($result && $result['ok']): ?>
        <div style="background:#fff;padding:24px;border:1px solid #ddd;border-radius:6px">
            <h2 style="margin-top:0;color:#28a745">Translation Result</h2>

            <div style="background:#e7f3ff;padding:12px;margin-bottom:20px;border-left:4px solid #0066cc;border-radius:4px">
                <p style="margin:0 0 8px 0">
                    <strong>From:</strong> <?= esc($result['source_language']) ?>
                    <strong style="margin-left:24px">To:</strong> <?= esc($result['target_language']) ?>
                </p>
                <?php if ($result['tone']): ?>
                    <p style="margin:0 0 8px 0"><strong>Tone:</strong> <?= esc($result['tone']) ?></p>
                <?php endif; ?>
                <?php if ($result['context']): ?>
                    <p style="margin:0"><strong>Context:</strong> <?= esc($result['context']) ?></p>
                <?php endif; ?>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div>
                    <h3 style="margin-top:0;color:#666">Source Text</h3>
                    <div style="background:#f8f9fa;padding:16px;border:1px solid #ddd;border-radius:4px;white-space:pre-wrap;font-family:inherit;line-height:1.6">
                        <?= esc($result['source_text']) ?>
                    </div>
                </div>
                <div>
                    <h3 style="margin-top:0;color:#28a745">Translated Text</h3>
                    <textarea
                        readonly
                        rows="15"
                        style="width:100%;padding:16px;border:1px solid #28a745;border-radius:4px;font-family:inherit;line-height:1.6;background:#fff"
                    ><?= esc($result['translated_text']) ?></textarea>
                    <small style="color:#666;display:block;margin-top:8px">
                        Tip: Click in the text area above and press Ctrl+A (or Cmd+A) to select all, then Ctrl+C (or Cmd+C) to copy.
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
