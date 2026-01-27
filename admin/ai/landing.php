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
$ai_used = false;
$ai_error = null;
$inputs = [
    'goal' => '',
    'audience' => '',
    'tone' => '',
    'language' => '',
    'keyword' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $inputs['goal'] = trim((string)($_POST['goal'] ?? ''));
    $inputs['audience'] = trim((string)($_POST['audience'] ?? ''));
    $inputs['tone'] = trim((string)($_POST['tone'] ?? ''));
    $inputs['language'] = trim((string)($_POST['language'] ?? ''));
    $inputs['keyword'] = trim((string)($_POST['keyword'] ?? ''));

    if (strlen($inputs['goal']) > 2000) {
        $inputs['goal'] = substr($inputs['goal'], 0, 2000);
    }
    if (strlen($inputs['audience']) > 255) {
        $inputs['audience'] = substr($inputs['audience'], 0, 255);
    }
    if (strlen($inputs['keyword']) > 255) {
        $inputs['keyword'] = substr($inputs['keyword'], 0, 255);
    }

    if ($inputs['goal'] === '') {
        $errors[] = 'Goal is required';
    }

    if (empty($errors)) {
        $config = ai_hf_config_load();

        if (!ai_hf_is_configured($config)) {
            $ai_error = 'Hugging Face is not configured. Using placeholder draft instead.';
            $audienceText = $inputs['audience'] !== '' ? ' targeting ' . $inputs['audience'] : '';
            $toneText = $inputs['tone'] !== '' ? ' in a ' . $inputs['tone'] . ' tone' : '';
            $keywordText = $inputs['keyword'] !== '' ? ' featuring ' . $inputs['keyword'] : '';

            $generated = [
                'headline' => 'Compelling headline for: ' . substr($inputs['goal'], 0, 80) . $audienceText,
                'subheadline' => 'Engaging subheadline' . $toneText . $keywordText,
                'sections' => [
                    'hero' => 'Hero section content addressing the goal: ' . $inputs['goal'],
                    'features' => 'Features section highlighting key benefits' . $audienceText,
                    'cta' => 'Call-to-action encouraging visitors to take the next step' . $toneText
                ]
            ];
        } else {
            $promptParts = ['You are an expert marketing copywriter. Generate landing page copy based on the following information:'];
            $promptParts[] = 'Goal: ' . $inputs['goal'];

            if ($inputs['audience'] !== '') {
                $promptParts[] = 'Target Audience: ' . $inputs['audience'];
            }
            if ($inputs['tone'] !== '') {
                $promptParts[] = 'Tone of Voice: ' . $inputs['tone'];
            }
            if ($inputs['language'] !== '') {
                $promptParts[] = 'Language: ' . $inputs['language'];
            }
            if ($inputs['keyword'] !== '') {
                $promptParts[] = 'Primary Keyword: ' . $inputs['keyword'];
            }

            $promptParts[] = 'Respond ONLY with valid minified JSON in this exact schema:';
            $promptParts[] = '{"headline":"...","subheadline":"...","hero":"...","features":["...","...","..."],"cta":"..."}';
            $promptParts[] = 'Requirements: headline and subheadline should be concise and conversion-focused. Features must be an array with at least 3 items. No explanations, no markdown, no extra text.';

            $prompt = implode("\n\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.7,
                'top_p' => 0.9
            ];

            $result = ai_hf_infer($config, $prompt, $options);

            if (!$result['ok']) {
                error_log('AI landing page generation failed: ' . ($result['error'] ?? 'Unknown error'));
                $ai_error = 'AI generation failed. Using placeholder draft instead.';

                $audienceText = $inputs['audience'] !== '' ? ' targeting ' . $inputs['audience'] : '';
                $toneText = $inputs['tone'] !== '' ? ' in a ' . $inputs['tone'] . ' tone' : '';
                $keywordText = $inputs['keyword'] !== '' ? ' featuring ' . $inputs['keyword'] : '';

                $generated = [
                    'headline' => 'Compelling headline for: ' . substr($inputs['goal'], 0, 80) . $audienceText,
                    'subheadline' => 'Engaging subheadline' . $toneText . $keywordText,
                    'sections' => [
                        'hero' => 'Hero section content addressing the goal: ' . $inputs['goal'],
                        'features' => 'Features section highlighting key benefits' . $audienceText,
                        'cta' => 'Call-to-action encouraging visitors to take the next step' . $toneText
                    ]
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
                    error_log('AI landing page generation returned invalid JSON');
                    $ai_error = 'AI response was invalid. Using placeholder draft instead.';

                    $audienceText = $inputs['audience'] !== '' ? ' targeting ' . $inputs['audience'] : '';
                    $toneText = $inputs['tone'] !== '' ? ' in a ' . $inputs['tone'] . ' tone' : '';
                    $keywordText = $inputs['keyword'] !== '' ? ' featuring ' . $inputs['keyword'] : '';

                    $generated = [
                        'headline' => 'Compelling headline for: ' . substr($inputs['goal'], 0, 80) . $audienceText,
                        'subheadline' => 'Engaging subheadline' . $toneText . $keywordText,
                        'sections' => [
                            'hero' => 'Hero section content addressing the goal: ' . $inputs['goal'],
                            'features' => 'Features section highlighting key benefits' . $audienceText,
                            'cta' => 'Call-to-action encouraging visitors to take the next step' . $toneText
                        ]
                    ];
                } else {
                    $headline = isset($aiData['headline']) && trim((string)$aiData['headline']) !== ''
                        ? trim(substr((string)$aiData['headline'], 0, 200))
                        : substr($inputs['goal'], 0, 80);

                    $subheadline = isset($aiData['subheadline']) && trim((string)$aiData['subheadline']) !== ''
                        ? trim(substr((string)$aiData['subheadline'], 0, 300))
                        : ($inputs['audience'] !== '' ? 'For ' . $inputs['audience'] : substr($inputs['goal'], 0, 100));

                    $hero = isset($aiData['hero']) && trim((string)$aiData['hero']) !== ''
                        ? trim(substr((string)$aiData['hero'], 0, 1000))
                        : $inputs['goal'];

                    $featuresArray = [];
                    if (isset($aiData['features']) && is_array($aiData['features'])) {
                        foreach ($aiData['features'] as $feature) {
                            $featureStr = trim((string)$feature);
                            if ($featureStr !== '') {
                                $featuresArray[] = substr($featureStr, 0, 500);
                            }
                        }
                    }
                    if (count($featuresArray) < 3) {
                        $featuresArray = [
                            'Key benefit aligned with your goal',
                            'Compelling value proposition',
                            'Unique selling point'
                        ];
                    }

                    $cta = isset($aiData['cta']) && trim((string)$aiData['cta']) !== ''
                        ? trim(substr((string)$aiData['cta'], 0, 200))
                        : 'Get Started Today';

                    $generated = [
                        'headline' => $headline,
                        'subheadline' => $subheadline,
                        'sections' => [
                            'hero' => $hero,
                            'features' => implode("\n\n", $featuresArray),
                            'cta' => $cta
                        ]
                    ];

                    $ai_used = true;
                }
            }
        }
    }
}

?>
<main class="container">
    <h1>AI Landing Page Generator</h1>

    <?php if (!empty($errors)): ?>
        <div class="notice error">
            <strong>Error:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($ai_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Generated</strong> — This draft was generated by Hugging Face AI.
        </div>
    <?php elseif ($ai_error !== null): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> <?= esc($ai_error) ?>
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 800px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom: 1rem;">
            <label for="goal"><strong>Goal</strong> (required)</label>
            <textarea id="goal" name="goal" rows="4" style="width: 100%;" required><?= esc($inputs['goal']) ?></textarea>
            <small>Describe what this landing page should achieve</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="audience"><strong>Target Audience</strong></label>
            <input type="text" id="audience" name="audience" value="<?= esc($inputs['audience']) ?>" style="width: 100%;">
            <small>Who is this landing page for?</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="tone"><strong>Tone of Voice</strong></label>
            <select id="tone" name="tone" style="width: 100%;">
                <option value="">Select tone</option>
                <option value="neutral"<?= $inputs['tone'] === 'neutral' ? ' selected' : '' ?>>Neutral</option>
                <option value="friendly"<?= $inputs['tone'] === 'friendly' ? ' selected' : '' ?>>Friendly</option>
                <option value="professional"<?= $inputs['tone'] === 'professional' ? ' selected' : '' ?>>Professional</option>
                <option value="playful"<?= $inputs['tone'] === 'playful' ? ' selected' : '' ?>>Playful</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="language"><strong>Language</strong></label>
            <select id="language" name="language" style="width: 100%;">
                <option value="">Auto</option>
                <option value="en"<?= $inputs['language'] === 'en' ? ' selected' : '' ?>>English</option>
                <option value="pl"<?= $inputs['language'] === 'pl' ? ' selected' : '' ?>>Polish</option>
                <option value="de"<?= $inputs['language'] === 'de' ? ' selected' : '' ?>>German</option>
                <option value="fr"<?= $inputs['language'] === 'fr' ? ' selected' : '' ?>>French</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="keyword"><strong>Primary Keyword</strong></label>
            <input type="text" id="keyword" name="keyword" value="<?= esc($inputs['keyword']) ?>" style="width: 100%;">
            <small>Main keyword or phrase to focus on</small>
        </div>

        <button type="submit" style="padding: 0.5rem 1.5rem;">Generate Draft</button>
    </form>

    <?php if ($generated !== null): ?>
        <div style="margin-top: 2rem; padding: 1.5rem; border: 1px solid #ccc; background: #f9f9f9;">
            <h2>Generated Draft</h2>

            <div style="margin-top: 1.5rem;">
                <h3>Headline</h3>
                <p style="font-size: 1.2rem; font-weight: bold;"><?= esc($generated['headline']) ?></p>
            </div>

            <div style="margin-top: 1rem;">
                <h3>Subheadline</h3>
                <p style="font-size: 1rem; color: #555;"><?= esc($generated['subheadline']) ?></p>
            </div>

            <div style="margin-top: 1.5rem;">
                <h3>Hero Section</h3>
                <p><?= esc($generated['sections']['hero']) ?></p>
            </div>

            <div style="margin-top: 1.5rem;">
                <h3>Features Section</h3>
                <p><?= esc($generated['sections']['features']) ?></p>
            </div>

            <div style="margin-top: 1.5rem;">
                <h3>Call to Action</h3>
                <p><?= esc($generated['sections']['cta']) ?></p>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
