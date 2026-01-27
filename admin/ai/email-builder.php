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
$generated = null;
$fallback_used = false;
$ai_used = false;
$inputs = [
    'purpose' => '',
    'audience' => '',
    'tone' => '',
    'language' => '',
    'keyword' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $inputs['purpose'] = trim((string)($_POST['purpose'] ?? ''));
    $inputs['audience'] = trim((string)($_POST['audience'] ?? ''));
    $inputs['tone'] = trim((string)($_POST['tone'] ?? ''));
    $inputs['language'] = trim((string)($_POST['language'] ?? ''));
    $inputs['keyword'] = trim((string)($_POST['keyword'] ?? ''));

    if (strlen($inputs['purpose']) > 2000) {
        $inputs['purpose'] = substr($inputs['purpose'], 0, 2000);
    }
    if (strlen($inputs['audience']) > 255) {
        $inputs['audience'] = substr($inputs['audience'], 0, 255);
    }
    if (strlen($inputs['keyword']) > 255) {
        $inputs['keyword'] = substr($inputs['keyword'], 0, 255);
    }

    if ($inputs['purpose'] === '') {
        $errors[] = 'Purpose is required';
    }

    $validTones = ['neutral', 'friendly', 'professional', 'persuasive'];
    if ($inputs['tone'] !== '' && !in_array($inputs['tone'], $validTones, true)) {
        $errors[] = 'Invalid tone selected';
    }

    $validLanguages = ['en', 'pl', 'de', 'fr'];
    if ($inputs['language'] !== '' && !in_array($inputs['language'], $validLanguages, true)) {
        $errors[] = 'Invalid language selected';
    }

    if (empty($errors)) {
        $config = ai_hf_config_load();

        if (!ai_hf_is_configured($config)) {
            $fallback_used = true;
            $subject = substr($inputs['purpose'], 0, 60);
            $preview = substr($inputs['purpose'], 0, 120);
            $html = '<p>' . esc($inputs['purpose']) . '</p>';
            $text = esc($inputs['purpose']);
            $cta = 'Learn more';

            $generated = [
                'subject' => $subject,
                'preview' => $preview,
                'html' => $html,
                'text' => $text,
                'cta' => $cta
            ];
        } else {
            $promptParts = ['You are an expert email marketing copywriter. Generate email content based on the following information:'];
            $promptParts[] = 'Purpose: ' . $inputs['purpose'];

            if ($inputs['audience'] !== '') {
                $promptParts[] = 'Target Audience: ' . $inputs['audience'];
            }
            if ($inputs['tone'] !== '') {
                $promptParts[] = 'Tone: ' . $inputs['tone'];
            }
            if ($inputs['language'] !== '') {
                $promptParts[] = 'Language: ' . $inputs['language'];
            }
            if ($inputs['keyword'] !== '') {
                $promptParts[] = 'Keyword: ' . $inputs['keyword'];
            }

            $promptParts[] = 'Respond ONLY with valid minified JSON in this exact schema:';
            $promptParts[] = '{"subject":"...","preview":"...","html":"...","text":"...","cta":"..."}';
            $promptParts[] = 'Requirements: subject should be concise and compelling (max 60 chars). Preview should be a brief teaser (max 120 chars). HTML should be valid email-safe HTML. Text should be plain text version. CTA should be a clear call-to-action phrase. No explanations, no markdown, no extra text.';

            $prompt = implode("\n\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.7,
                'top_p' => 0.9
            ];

            $result = ai_hf_infer($config, $prompt, $options);

            if (!$result['ok']) {
                error_log('AI email generation failed: ' . ($result['error'] ?? 'Unknown error'));
                $fallback_used = true;

                $subject = substr($inputs['purpose'], 0, 60);
                $preview = substr($inputs['purpose'], 0, 120);
                $html = '<p>' . esc($inputs['purpose']) . '</p>';
                $text = esc($inputs['purpose']);
                $cta = 'Learn more';

                $generated = [
                    'subject' => $subject,
                    'preview' => $preview,
                    'html' => $html,
                    'text' => $text,
                    'cta' => $cta
                ];
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
                    error_log('AI email generation returned invalid JSON');
                    $fallback_used = true;

                    $subject = substr($inputs['purpose'], 0, 60);
                    $preview = substr($inputs['purpose'], 0, 120);
                    $html = '<p>' . esc($inputs['purpose']) . '</p>';
                    $text = esc($inputs['purpose']);
                    $cta = 'Learn more';

                    $generated = [
                        'subject' => $subject,
                        'preview' => $preview,
                        'html' => $html,
                        'text' => $text,
                        'cta' => $cta
                    ];
                } else {
                    $subject = isset($aiData['subject']) && trim((string)$aiData['subject']) !== ''
                        ? trim(substr((string)$aiData['subject'], 0, 60))
                        : substr($inputs['purpose'], 0, 60);

                    $preview = isset($aiData['preview']) && trim((string)$aiData['preview']) !== ''
                        ? trim(substr((string)$aiData['preview'], 0, 120))
                        : substr($inputs['purpose'], 0, 120);

                    $html = isset($aiData['html']) && trim((string)$aiData['html']) !== ''
                        ? trim((string)$aiData['html'])
                        : '<p>' . esc($inputs['purpose']) . '</p>';

                    $text = isset($aiData['text']) && trim((string)$aiData['text']) !== ''
                        ? trim((string)$aiData['text'])
                        : $inputs['purpose'];

                    $cta = isset($aiData['cta']) && trim((string)$aiData['cta']) !== ''
                        ? trim(substr((string)$aiData['cta'], 0, 100))
                        : 'Learn more';

                    $generated = [
                        'subject' => $subject,
                        'preview' => $preview,
                        'html' => $html,
                        'text' => $text,
                        'cta' => $cta
                    ];

                    $ai_used = true;
                }
            }
        }
    }
}

?>
<main class="container">
    <h1>AI Email Builder</h1>

    <?php if (!empty($errors)): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
            <strong>Error:</strong>
            <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($ai_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Used</strong> — This email was generated by Hugging Face AI.
        </div>
    <?php elseif ($fallback_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>⚠ Fallback Used</strong> — AI is not configured or failed. Using placeholder content.
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 800px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom: 1rem;">
            <label for="purpose"><strong>Purpose</strong> (required)</label>
            <textarea id="purpose" name="purpose" rows="4" style="width: 100%;" required maxlength="2000"><?= esc($inputs['purpose']) ?></textarea>
            <small>What is the purpose of this email? (max 2000 characters)</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="audience"><strong>Audience</strong></label>
            <input type="text" id="audience" name="audience" value="<?= esc($inputs['audience']) ?>" style="width: 100%;" maxlength="255">
            <small>Who is the target audience? (max 255 characters)</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="tone"><strong>Tone</strong></label>
            <select id="tone" name="tone" style="width: 100%;">
                <option value="">Select tone</option>
                <option value="neutral"<?= $inputs['tone'] === 'neutral' ? ' selected' : '' ?>>Neutral</option>
                <option value="friendly"<?= $inputs['tone'] === 'friendly' ? ' selected' : '' ?>>Friendly</option>
                <option value="professional"<?= $inputs['tone'] === 'professional' ? ' selected' : '' ?>>Professional</option>
                <option value="persuasive"<?= $inputs['tone'] === 'persuasive' ? ' selected' : '' ?>>Persuasive</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="language"><strong>Language</strong></label>
            <select id="language" name="language" style="width: 100%;">
                <option value="">Select language</option>
                <option value="en"<?= $inputs['language'] === 'en' ? ' selected' : '' ?>>English</option>
                <option value="pl"<?= $inputs['language'] === 'pl' ? ' selected' : '' ?>>Polish</option>
                <option value="de"<?= $inputs['language'] === 'de' ? ' selected' : '' ?>>German</option>
                <option value="fr"<?= $inputs['language'] === 'fr' ? ' selected' : '' ?>>French</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="keyword"><strong>Keyword</strong> (optional)</label>
            <input type="text" id="keyword" name="keyword" value="<?= esc($inputs['keyword']) ?>" style="width: 100%;" maxlength="255">
            <small>Optional keyword to include (max 255 characters)</small>
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
            Generate Email
        </button>
    </form>

    <?php if ($generated !== null): ?>
        <div style="margin-top: 3rem; padding: 2rem; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
            <h2 style="margin-top: 0;">Generated Email</h2>

            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Subject:</h3>
                <p style="font-size: 1.5rem; font-weight: bold; margin: 0;"><?= esc($generated['subject']) ?></p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Preview Line:</h3>
                <p style="font-size: 1rem; color: #6c757d; margin: 0;"><?= esc($generated['preview']) ?></p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Call-to-Action:</h3>
                <p style="margin: 0;"><strong><?= esc($generated['cta']) ?></strong></p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">HTML Body:</h3>
                <div class="ai-email-preview" style="padding: 1rem; background: white; border: 1px solid #ced4da; border-radius: 4px;">
                    <?= $generated['html'] ?>
                </div>
            </div>

            <div style="margin-bottom: 0;">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Plain Text Version:</h3>
                <pre style="padding: 1rem; background: white; border: 1px solid #ced4da; border-radius: 4px; white-space: pre-wrap; word-wrap: break-word; font-family: monospace;"><?= esc($generated['text']) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</main>
