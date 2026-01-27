<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/core/ai_hf.php';

require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';

function esc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$ai_used = false;
$ai_error = null;
$source_text = '';
$translated_text = null;

$source_lang = 'auto';
$target_lang = 'en';
$tone = '';

$hf_config = ai_hf_config_load();
$hf_configured = ai_hf_is_configured($hf_config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $source_text = trim((string)($_POST['source_text'] ?? ''));
    $source_lang = trim((string)($_POST['source_lang'] ?? 'auto'));
    $target_lang = trim((string)($_POST['target_lang'] ?? 'en'));
    $tone = trim((string)($_POST['tone'] ?? ''));

    $allowed_source_langs = ['auto', 'en', 'pl', 'de', 'fr'];
    $allowed_target_langs = ['en', 'pl', 'de', 'fr'];
    $allowed_tones = ['', 'neutral', 'friendly', 'professional'];

    if ($source_text === '') {
        $errors[] = 'Source text is required';
    } elseif (strlen($source_text) > 10000) {
        $errors[] = 'Source text is too long (maximum 10000 characters)';
    }

    if (!in_array($target_lang, $allowed_target_langs)) {
        $errors[] = 'Invalid target language';
    }

    if (!in_array($source_lang, $allowed_source_langs)) {
        $errors[] = 'Invalid source language';
    }

    if (!in_array($tone, $allowed_tones)) {
        $errors[] = 'Invalid tone';
    }

    if (empty($errors)) {
        if (!$hf_configured) {
            $ai_error = 'Hugging Face is not configured. Showing original text.';
            $translated_text = $source_text;
            $ai_used = false;
        } else {
            $promptParts = ['You are a professional translator. Translate the following text.'];

            if ($source_lang !== 'auto') {
                $promptParts[] = 'Source language: ' . $source_lang;
            }
            $promptParts[] = 'Target language: ' . $target_lang;

            if ($tone !== '') {
                $promptParts[] = 'Tone: ' . $tone;
            }

            $promptParts[] = 'Text to translate:';
            $promptParts[] = $source_text;
            $promptParts[] = '';
            $promptParts[] = 'IMPORTANT: Respond ONLY with valid JSON in this exact format:';
            $promptParts[] = '{"translation":"translated text here"}';
            $promptParts[] = 'Do not include any explanations, markdown, or extra text. Only the JSON object.';

            $prompt = implode("\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.7,
                'top_p' => 0.9
            ];

            $response = ai_hf_infer($hf_config, $prompt, $options);

            if (!$response['ok']) {
                $ai_error = 'AI translation failed. Showing original text.';
                $translated_text = $source_text;
                $ai_used = false;
            } else {
                $aiData = null;
                if (is_array($response['json'])) {
                    $aiData = $response['json'];
                } else {
                    $decoded = @json_decode($response['body'], true);
                    if (is_array($decoded)) {
                        $aiData = $decoded;
                    }
                }

                if ($aiData === null || !is_array($aiData)) {
                    $ai_error = 'AI translation failed (invalid JSON). Showing original text.';
                    $translated_text = $source_text;
                    $ai_used = false;
                } else {
                    $translation = isset($aiData['translation']) ? trim((string)$aiData['translation']) : '';

                    if ($translation === '') {
                        $ai_error = 'AI translation failed (empty result). Showing original text.';
                        $translated_text = $source_text;
                        $ai_used = false;
                    } else {
                        $translated_text = $translation;
                        $ai_used = true;
                    }
                }
            }
        }
    }
}

?>
<main class="container">
    <h1>AI Translation</h1>

    <?php if (!$hf_configured): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> Hugging Face is not configured. Translations will not work. Visit <a href="/admin/hf-settings.php">Hugging Face Settings</a> to configure.
        </div>
    <?php endif; ?>

    <?php if ($ai_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Used</strong> — Translation generated by Hugging Face AI.
        </div>
    <?php elseif (!$ai_used && $translated_text !== null): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Fallback Used</strong> — AI translation was not available.
        </div>
    <?php endif; ?>

    <?php if ($ai_error !== null): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Warning:</strong> <?= esc($ai_error) ?>
        </div>
    <?php endif; ?>

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

    <form method="post" style="max-width: 800px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom: 1rem;">
            <label for="source_text"><strong>Source Text</strong> (required)</label>
            <textarea id="source_text" name="source_text" rows="8" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required maxlength="10000"><?= esc($source_text) ?></textarea>
            <small style="color: #666;">Enter text to translate (maximum 10000 characters)</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="source_lang"><strong>Source Language</strong></label>
            <select id="source_lang" name="source_lang" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <option value="auto"<?= $source_lang === 'auto' ? ' selected' : '' ?>>Auto Detect</option>
                <option value="en"<?= $source_lang === 'en' ? ' selected' : '' ?>>English</option>
                <option value="pl"<?= $source_lang === 'pl' ? ' selected' : '' ?>>Polish</option>
                <option value="de"<?= $source_lang === 'de' ? ' selected' : '' ?>>German</option>
                <option value="fr"<?= $source_lang === 'fr' ? ' selected' : '' ?>>French</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="target_lang"><strong>Target Language</strong> (required)</label>
            <select id="target_lang" name="target_lang" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                <option value="en"<?= $target_lang === 'en' ? ' selected' : '' ?>>English</option>
                <option value="pl"<?= $target_lang === 'pl' ? ' selected' : '' ?>>Polish</option>
                <option value="de"<?= $target_lang === 'de' ? ' selected' : '' ?>>German</option>
                <option value="fr"<?= $target_lang === 'fr' ? ' selected' : '' ?>>French</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="tone"><strong>Tone</strong></label>
            <select id="tone" name="tone" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">Auto</option>
                <option value="neutral"<?= $tone === 'neutral' ? ' selected' : '' ?>>Neutral</option>
                <option value="friendly"<?= $tone === 'friendly' ? ' selected' : '' ?>>Friendly</option>
                <option value="professional"<?= $tone === 'professional' ? ' selected' : '' ?>>Professional</option>
            </select>
            <small style="color: #666;">Optional translation tone</small>
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Translate</button>
    </form>

    <?php if ($translated_text !== null): ?>
        <div style="margin-top: 2rem; padding: 1.5rem; border: 1px solid #ccc; background: #f9f9f9; border-radius: 4px;">
            <h2>Translation</h2>
            <div class="ai-translation-preview" style="background: white; padding: 1rem; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 1.5rem;">
                <p style="margin: 0; white-space: pre-wrap;"><?= nl2br(esc($translated_text)) ?></p>
            </div>

            <h2>Raw Text</h2>
            <textarea rows="10" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-family: monospace;" readonly><?= esc($translated_text) ?></textarea>
            <small style="color: #666;">Copy the translated text from above</small>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
