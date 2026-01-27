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
$result = null;
$inputs = [
    'goal' => '',
    'context' => '',
    'audience' => '',
    'tone' => '',
    'language' => 'en',
    'seed_fields' => ''
];

$hf_config = ai_hf_config_load();
$hf_configured = ai_hf_is_configured($hf_config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $inputs['goal'] = trim((string)($_POST['goal'] ?? ''));
    $inputs['context'] = trim((string)($_POST['context'] ?? ''));
    $inputs['audience'] = trim((string)($_POST['audience'] ?? ''));
    $inputs['tone'] = trim((string)($_POST['tone'] ?? ''));
    $inputs['language'] = trim((string)($_POST['language'] ?? ''));
    $inputs['seed_fields'] = trim((string)($_POST['seed_fields'] ?? ''));

    if (strlen($inputs['goal']) > 2000) {
        $errors[] = 'Goal is too long (maximum 2000 characters)';
    }
    if (strlen($inputs['context']) > 255) {
        $errors[] = 'Context is too long (maximum 255 characters)';
    }
    if (strlen($inputs['audience']) > 255) {
        $errors[] = 'Audience is too long (maximum 255 characters)';
    }
    if (strlen($inputs['tone']) > 255) {
        $errors[] = 'Tone is too long (maximum 255 characters)';
    }
    if (strlen($inputs['language']) > 255) {
        $errors[] = 'Language is too long (maximum 255 characters)';
    }
    if (strlen($inputs['seed_fields']) > 255) {
        $errors[] = 'Seed fields is too long (maximum 255 characters)';
    }

    if ($inputs['goal'] === '') {
        $errors[] = 'Goal is required';
    }

    if (empty($errors)) {
        if (!$hf_configured) {
            $ai_error = 'Hugging Face is not configured. Using placeholder suggestion.';

            $seedFieldsList = [];
            if ($inputs['seed_fields'] !== '') {
                $rawSeeds = explode(',', $inputs['seed_fields']);
                foreach ($rawSeeds as $seed) {
                    $trimmed = trim($seed);
                    if ($trimmed !== '') {
                        $seedFieldsList[] = $trimmed;
                    }
                }
            }

            $fields = [];
            if (in_array('name', $seedFieldsList) || in_array('Name', $seedFieldsList)) {
                $fields[] = [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Your full name',
                    'help_text' => ''
                ];
            } else {
                $fields[] = [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Your full name',
                    'help_text' => ''
                ];
            }

            if (in_array('email', $seedFieldsList) || in_array('Email', $seedFieldsList)) {
                $fields[] = [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => 'your@email.com',
                    'help_text' => ''
                ];
            } else {
                $fields[] = [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => 'your@email.com',
                    'help_text' => ''
                ];
            }

            if (in_array('message', $seedFieldsList) || in_array('Message', $seedFieldsList)) {
                $fields[] = [
                    'name' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'placeholder' => 'Your message',
                    'help_text' => ''
                ];
            } else {
                $fields[] = [
                    'name' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'placeholder' => 'Your message',
                    'help_text' => ''
                ];
            }

            $result = [
                'title' => substr($inputs['goal'], 0, 80),
                'description' => $inputs['goal'],
                'fields' => $fields,
                'submit_label' => 'Submit'
            ];
        } else {
            $promptParts = ['You are an expert form designer. Generate a form structure based on the following requirements:'];
            $promptParts[] = 'Goal: ' . $inputs['goal'];

            if ($inputs['context'] !== '') {
                $promptParts[] = 'Context: ' . $inputs['context'];
            }
            if ($inputs['audience'] !== '') {
                $promptParts[] = 'Target Audience: ' . $inputs['audience'];
            }
            if ($inputs['tone'] !== '') {
                $promptParts[] = 'Tone: ' . $inputs['tone'];
            }
            if ($inputs['language'] !== '') {
                $promptParts[] = 'Language: ' . $inputs['language'];
            }
            if ($inputs['seed_fields'] !== '') {
                $promptParts[] = 'Required Fields: ' . $inputs['seed_fields'];
            }

            $promptParts[] = 'Respond ONLY with valid minified JSON in this exact schema:';
            $promptParts[] = '{"title":"Short human-readable form name","description":"1-2 sentence description","fields":[{"name":"field_name","label":"Field Label","type":"text|email|tel|textarea|select|checkbox|radio|number|date","required":true,"placeholder":"Optional placeholder","help_text":"Optional help text"}],"submit_label":"Submit button text"}';
            $promptParts[] = 'Requirements: Field names must be lowercase with underscores. Labels should be in the requested language. Include at least 3-5 relevant fields. No explanations, no markdown, no extra text.';

            $prompt = implode("\n\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.7,
                'top_p' => 0.9
            ];

            $response = ai_hf_infer($hf_config, $prompt, $options);

            if (!$response['ok']) {
                error_log('AI form generation failed: ' . ($response['error'] ?? 'Unknown error'));
                $ai_error = 'AI generation failed. Using placeholder suggestion.';

                $fields = [
                    [
                        'name' => 'name',
                        'label' => 'Name',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Your full name',
                        'help_text' => ''
                    ],
                    [
                        'name' => 'email',
                        'label' => 'Email',
                        'type' => 'email',
                        'required' => true,
                        'placeholder' => 'your@email.com',
                        'help_text' => ''
                    ],
                    [
                        'name' => 'message',
                        'label' => 'Message',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Your message',
                        'help_text' => ''
                    ]
                ];

                $result = [
                    'title' => substr($inputs['goal'], 0, 80),
                    'description' => $inputs['goal'],
                    'fields' => $fields,
                    'submit_label' => 'Submit'
                ];
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
                    error_log('AI form generation returned invalid JSON');
                    $ai_error = 'AI response was invalid. Using placeholder suggestion.';

                    $fields = [
                        [
                            'name' => 'name',
                            'label' => 'Name',
                            'type' => 'text',
                            'required' => true,
                            'placeholder' => 'Your full name',
                            'help_text' => ''
                        ],
                        [
                            'name' => 'email',
                            'label' => 'Email',
                            'type' => 'email',
                            'required' => true,
                            'placeholder' => 'your@email.com',
                            'help_text' => ''
                        ],
                        [
                            'name' => 'message',
                            'label' => 'Message',
                            'type' => 'textarea',
                            'required' => true,
                            'placeholder' => 'Your message',
                            'help_text' => ''
                        ]
                    ];

                    $result = [
                        'title' => substr($inputs['goal'], 0, 80),
                        'description' => $inputs['goal'],
                        'fields' => $fields,
                        'submit_label' => 'Submit'
                    ];
                } else {
                    $title = isset($aiData['title']) && trim((string)$aiData['title']) !== ''
                        ? trim(substr((string)$aiData['title'], 0, 200))
                        : substr($inputs['goal'], 0, 80);

                    $description = isset($aiData['description']) && trim((string)$aiData['description']) !== ''
                        ? trim(substr((string)$aiData['description'], 0, 500))
                        : $inputs['goal'];

                    $fieldsArray = [];
                    $allowedTypes = ['text', 'email', 'tel', 'textarea', 'select', 'checkbox', 'radio', 'number', 'date'];

                    if (isset($aiData['fields']) && is_array($aiData['fields'])) {
                        foreach ($aiData['fields'] as $field) {
                            if (!is_array($field)) {
                                continue;
                            }

                            $fieldName = isset($field['name']) ? trim((string)$field['name']) : '';
                            if ($fieldName === '') {
                                $fieldName = 'field_' . count($fieldsArray);
                            }
                            $fieldName = preg_replace('/[^a-z0-9_]/', '', strtolower($fieldName));
                            if ($fieldName === '') {
                                $fieldName = 'field_' . count($fieldsArray);
                            }

                            $fieldLabel = isset($field['label']) ? trim((string)$field['label']) : ucfirst($fieldName);
                            if ($fieldLabel === '') {
                                $fieldLabel = ucfirst($fieldName);
                            }

                            $fieldType = isset($field['type']) ? trim(strtolower((string)$field['type'])) : 'text';
                            if (!in_array($fieldType, $allowedTypes)) {
                                $fieldType = 'text';
                            }

                            $fieldRequired = isset($field['required']) ? (bool)$field['required'] : true;

                            $fieldPlaceholder = isset($field['placeholder']) ? trim((string)$field['placeholder']) : '';
                            $fieldHelpText = isset($field['help_text']) ? trim((string)$field['help_text']) : '';

                            $fieldsArray[] = [
                                'name' => $fieldName,
                                'label' => substr($fieldLabel, 0, 100),
                                'type' => $fieldType,
                                'required' => $fieldRequired,
                                'placeholder' => substr($fieldPlaceholder, 0, 100),
                                'help_text' => substr($fieldHelpText, 0, 200)
                            ];
                        }
                    }

                    if (count($fieldsArray) === 0) {
                        $fieldsArray = [
                            [
                                'name' => 'name',
                                'label' => 'Name',
                                'type' => 'text',
                                'required' => true,
                                'placeholder' => 'Your full name',
                                'help_text' => ''
                            ],
                            [
                                'name' => 'email',
                                'label' => 'Email',
                                'type' => 'email',
                                'required' => true,
                                'placeholder' => 'your@email.com',
                                'help_text' => ''
                            ],
                            [
                                'name' => 'message',
                                'label' => 'Message',
                                'type' => 'textarea',
                                'required' => true,
                                'placeholder' => 'Your message',
                                'help_text' => ''
                            ]
                        ];
                    }

                    $submitLabel = isset($aiData['submit_label']) && trim((string)$aiData['submit_label']) !== ''
                        ? trim(substr((string)$aiData['submit_label'], 0, 50))
                        : 'Submit';

                    $result = [
                        'title' => $title,
                        'description' => $description,
                        'fields' => $fieldsArray,
                        'submit_label' => $submitLabel
                    ];

                    $ai_used = true;
                }
            }
        }
    }
}

?>
<main class="container">
    <h1>AI Form Generator</h1>

    <?php if (!$hf_configured): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> Hugging Face is not configured. Placeholders will be used for generation. Visit <a href="/admin/hf-settings.php">Hugging Face Settings</a> to configure.
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

    <?php if ($ai_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Generated</strong> — This form was generated by Hugging Face AI.
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
            <textarea id="goal" name="goal" rows="4" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required><?= esc($inputs['goal']) ?></textarea>
            <small style="color: #666;">Describe what this form should achieve (e.g., "Contact form for customer support inquiries")</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="context"><strong>Context</strong></label>
            <input type="text" id="context" name="context" value="<?= esc($inputs['context']) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <small style="color: #666;">Where will this form be used? (e.g., "landing page", "contact page", "registration")</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="audience"><strong>Target Audience</strong></label>
            <input type="text" id="audience" name="audience" value="<?= esc($inputs['audience']) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <small style="color: #666;">Who will use this form? (e.g., "parents", "customers", "students")</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="tone"><strong>Tone</strong></label>
            <select id="tone" name="tone" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">Auto</option>
                <option value="formal"<?= $inputs['tone'] === 'formal' ? ' selected' : '' ?>>Formal</option>
                <option value="neutral"<?= $inputs['tone'] === 'neutral' ? ' selected' : '' ?>>Neutral</option>
                <option value="friendly"<?= $inputs['tone'] === 'friendly' ? ' selected' : '' ?>>Friendly</option>
            </select>
            <small style="color: #666;">How should the form labels and help text feel?</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="language"><strong>Language</strong></label>
            <select id="language" name="language" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <option value="en"<?= $inputs['language'] === 'en' ? ' selected' : '' ?>>English</option>
                <option value="pl"<?= $inputs['language'] === 'pl' ? ' selected' : '' ?>>Polish</option>
                <option value="de"<?= $inputs['language'] === 'de' ? ' selected' : '' ?>>German</option>
                <option value="fr"<?= $inputs['language'] === 'fr' ? ' selected' : '' ?>>French</option>
                <option value="es"<?= $inputs['language'] === 'es' ? ' selected' : '' ?>>Spanish</option>
            </select>
            <small style="color: #666;">Language for field labels and text</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="seed_fields"><strong>Seed Fields</strong></label>
            <input type="text" id="seed_fields" name="seed_fields" value="<?= esc($inputs['seed_fields']) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="name, email, phone, message">
            <small style="color: #666;">Comma-separated fields you know you need (e.g., "name, email, phone, message")</small>
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Generate Form</button>
    </form>

    <?php if ($result !== null): ?>
        <div style="margin-top: 2rem; padding: 1.5rem; border: 1px solid #ccc; background: #f9f9f9; border-radius: 4px;">
            <h2>Generated Form Structure</h2>

            <div style="margin-top: 1rem;">
                <h3 style="margin-bottom: 0.5rem;">Form Title</h3>
                <p style="font-size: 1.1rem; font-weight: bold; margin: 0;"><?= esc($result['title']) ?></p>
            </div>

            <div style="margin-top: 1rem;">
                <h3 style="margin-bottom: 0.5rem;">Description</h3>
                <p style="margin: 0;"><?= esc($result['description']) ?></p>
            </div>

            <div style="margin-top: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem;">Fields</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">
                    <thead>
                        <tr style="background: #e9ecef; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 0.75rem; text-align: left; border: 1px solid #dee2e6;">Name</th>
                            <th style="padding: 0.75rem; text-align: left; border: 1px solid #dee2e6;">Label</th>
                            <th style="padding: 0.75rem; text-align: left; border: 1px solid #dee2e6;">Type</th>
                            <th style="padding: 0.75rem; text-align: left; border: 1px solid #dee2e6;">Required</th>
                            <th style="padding: 0.75rem; text-align: left; border: 1px solid #dee2e6;">Placeholder</th>
                            <th style="padding: 0.75rem; text-align: left; border: 1px solid #dee2e6;">Help Text</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['fields'] as $field): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 0.75rem; border: 1px solid #dee2e6;"><code><?= esc($field['name']) ?></code></td>
                                <td style="padding: 0.75rem; border: 1px solid #dee2e6;"><?= esc($field['label']) ?></td>
                                <td style="padding: 0.75rem; border: 1px solid #dee2e6;"><code><?= esc($field['type']) ?></code></td>
                                <td style="padding: 0.75rem; border: 1px solid #dee2e6;"><?= $field['required'] ? 'Yes' : 'No' ?></td>
                                <td style="padding: 0.75rem; border: 1px solid #dee2e6;"><?= esc($field['placeholder']) ?></td>
                                <td style="padding: 0.75rem; border: 1px solid #dee2e6;"><?= esc($field['help_text']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                <h3 style="margin-bottom: 0.5rem;">Submit Button</h3>
                <p style="margin: 0;"><strong><?= esc($result['submit_label']) ?></strong></p>
            </div>

            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 0.5rem;">Raw JSON (Copy for Use)</h3>
                <pre style="background: #263238; color: #aed581; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.9rem; line-height: 1.5;"><?= esc(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
                <small style="color: #666;">Copy this JSON to use in your application or save it for future reference.</small>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
