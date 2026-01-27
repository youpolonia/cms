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

$hf_config = ai_hf_config_load();
$hf_configured = ai_hf_is_configured($hf_config);

$inputs = [
    'component_type' => '',
    'layout' => '',
    'theme' => '',
    'language' => 'en',
    'tone' => '',
    'instructions' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $inputs['component_type'] = trim((string)($_POST['component_type'] ?? ''));
    $inputs['layout'] = trim((string)($_POST['layout'] ?? ''));
    $inputs['theme'] = trim((string)($_POST['theme'] ?? ''));
    $inputs['language'] = trim((string)($_POST['language'] ?? ''));
    $inputs['tone'] = trim((string)($_POST['tone'] ?? ''));
    $inputs['instructions'] = trim((string)($_POST['instructions'] ?? ''));

    $allowed_component_types = ['hero', 'pricing_table', 'testimonial', 'feature_list', 'faq', 'call_to_action'];
    $allowed_layouts = ['single_column', 'two_column', 'three_column'];
    $allowed_languages = ['en', 'pl', 'de', 'fr'];
    $allowed_tones = ['', 'neutral', 'friendly', 'professional', 'bold'];

    if ($inputs['component_type'] === '') {
        $errors[] = 'Component type is required';
    } elseif (!in_array($inputs['component_type'], $allowed_component_types)) {
        $errors[] = 'Invalid component type';
    }

    if ($inputs['layout'] === '') {
        $errors[] = 'Layout is required';
    } elseif (!in_array($inputs['layout'], $allowed_layouts)) {
        $errors[] = 'Invalid layout';
    }

    if ($inputs['language'] === '') {
        $errors[] = 'Language is required';
    } elseif (!in_array($inputs['language'], $allowed_languages)) {
        $errors[] = 'Invalid language';
    }

    if (!in_array($inputs['tone'], $allowed_tones)) {
        $errors[] = 'Invalid tone';
    }

    if ($inputs['instructions'] === '') {
        $errors[] = 'Instructions are required';
    } elseif (strlen($inputs['instructions']) > 2000) {
        $errors[] = 'Instructions are too long (maximum 2000 characters)';
    }

    if (strlen($inputs['theme']) > 255) {
        $errors[] = 'Theme is too long (maximum 255 characters)';
    }

    if (empty($errors)) {
        if ($hf_configured) {
            $promptParts = ['You are an expert HTML component designer. Generate a reusable HTML component based on the following requirements:'];
            $promptParts[] = 'Component Type: ' . $inputs['component_type'];
            $promptParts[] = 'Layout: ' . $inputs['layout'];
            $promptParts[] = 'Language: ' . $inputs['language'];

            if ($inputs['tone'] !== '') {
                $promptParts[] = 'Tone: ' . $inputs['tone'];
            }
            if ($inputs['theme'] !== '') {
                $promptParts[] = 'Theme: ' . $inputs['theme'];
            }

            $promptParts[] = 'Instructions: ' . $inputs['instructions'];
            $promptParts[] = 'Respond ONLY with valid minified JSON in this exact schema:';
            $promptParts[] = '{"title":"Short internal name for the component","description":"1-2 sentences describing what this component is designed to do","html":"<section>...</section>","notes":["Short note 1","Short note 2"]}';
            $promptParts[] = 'Requirements: The html field must be a clean HTML fragment with no <html>, <head> or <body> tags. Use simple self-contained classes, no external frameworks. No explanations, no markdown, no extra text outside the JSON.';

            $prompt = implode("\n\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.7,
                'top_p' => 0.9
            ];

            $response = ai_hf_infer($hf_config, $prompt, $options);

            if (!$response['ok']) {
                error_log('AI component generation failed: ' . ($response['error'] ?? 'Unknown error'));
                $ai_error = 'AI generation failed, using fallback component.';
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
                    error_log('AI component generation returned invalid JSON');
                    $ai_error = 'AI response was invalid, using fallback component.';
                } else {
                    $title = isset($aiData['title']) && trim((string)$aiData['title']) !== ''
                        ? trim(substr((string)$aiData['title'], 0, 200))
                        : ucfirst(str_replace('_', ' ', $inputs['component_type'])) . ' component';

                    $description = isset($aiData['description']) && trim((string)$aiData['description']) !== ''
                        ? trim(substr((string)$aiData['description'], 0, 500))
                        : '';

                    $html = isset($aiData['html']) && trim((string)$aiData['html']) !== ''
                        ? trim((string)$aiData['html'])
                        : '';

                    if ($html !== '' && (strpos($html, '<') !== false)) {
                        $notesArray = [];
                        if (isset($aiData['notes']) && is_array($aiData['notes'])) {
                            foreach ($aiData['notes'] as $note) {
                                $noteStr = trim((string)$note);
                                if ($noteStr !== '') {
                                    $notesArray[] = substr($noteStr, 0, 200);
                                }
                            }
                        }

                        $result = [
                            'title' => $title,
                            'description' => $description,
                            'html' => $html,
                            'notes' => $notesArray
                        ];

                        $ai_used = true;
                    } else {
                        $ai_error = 'AI response did not contain valid HTML, using fallback component.';
                    }
                }
            }
        }

        if (!$hf_configured || $result === null) {
            $component_type = $inputs['component_type'];
            $layout = $inputs['layout'];
            $theme = $inputs['theme'] !== '' ? $inputs['theme'] : 'modern';
            $instructions = $inputs['instructions'];

            $layoutClass = str_replace('_', '-', $layout);

            switch ($component_type) {
                case 'hero':
                    $html = '<section class="ai-component hero ' . esc($layoutClass) . '">';
                    $html .= '<div class="hero-content">';
                    $html .= '<h1>Welcome to Our ' . esc(ucfirst($theme)) . ' Platform</h1>';
                    $html .= '<p>' . esc(substr($instructions, 0, 150)) . '</p>';
                    $html .= '<button class="cta-button">Get Started</button>';
                    $html .= '</div>';
                    $html .= '</section>';
                    break;

                case 'pricing_table':
                    $html = '<section class="ai-component pricing-table ' . esc($layoutClass) . '">';
                    $html .= '<h2>Pricing Plans</h2>';
                    $html .= '<div class="pricing-columns">';
                    $html .= '<div class="pricing-column">';
                    $html .= '<h3>Basic</h3>';
                    $html .= '<p class="price">$9/mo</p>';
                    $html .= '<ul><li>Feature 1</li><li>Feature 2</li></ul>';
                    $html .= '<button>Choose Plan</button>';
                    $html .= '</div>';
                    $html .= '<div class="pricing-column featured">';
                    $html .= '<h3>Pro</h3>';
                    $html .= '<p class="price">$29/mo</p>';
                    $html .= '<ul><li>All Basic features</li><li>Feature 3</li><li>Feature 4</li></ul>';
                    $html .= '<button>Choose Plan</button>';
                    $html .= '</div>';
                    $html .= '<div class="pricing-column">';
                    $html .= '<h3>Enterprise</h3>';
                    $html .= '<p class="price">Custom</p>';
                    $html .= '<ul><li>All Pro features</li><li>Custom solutions</li></ul>';
                    $html .= '<button>Contact Us</button>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</section>';
                    break;

                case 'testimonial':
                    $html = '<section class="ai-component testimonial ' . esc($layoutClass) . '">';
                    $html .= '<blockquote>';
                    $html .= '<p>"' . esc(substr($instructions, 0, 200)) . '"</p>';
                    $html .= '<cite>';
                    $html .= '<strong>John Doe</strong>';
                    $html .= '<span>CEO, Example Company</span>';
                    $html .= '</cite>';
                    $html .= '</blockquote>';
                    $html .= '</section>';
                    break;

                case 'feature_list':
                    $html = '<section class="ai-component feature-list ' . esc($layoutClass) . '">';
                    $html .= '<h2>Key Features</h2>';
                    $html .= '<ul class="features">';
                    $html .= '<li><strong>Feature One:</strong> ' . esc(substr($instructions, 0, 60)) . '</li>';
                    $html .= '<li><strong>Feature Two:</strong> Advanced capabilities for your needs</li>';
                    $html .= '<li><strong>Feature Three:</strong> Built with ' . esc($theme) . ' design in mind</li>';
                    $html .= '<li><strong>Feature Four:</strong> Easy to integrate and customize</li>';
                    $html .= '</ul>';
                    $html .= '</section>';
                    break;

                case 'faq':
                    $html = '<section class="ai-component faq ' . esc($layoutClass) . '">';
                    $html .= '<h2>Frequently Asked Questions</h2>';
                    $html .= '<dl class="faq-list">';
                    $html .= '<dt>What is this component for?</dt>';
                    $html .= '<dd>' . esc(substr($instructions, 0, 100)) . '</dd>';
                    $html .= '<dt>How do I customize it?</dt>';
                    $html .= '<dd>You can modify the HTML and CSS to match your ' . esc($theme) . ' theme.</dd>';
                    $html .= '<dt>Is it responsive?</dt>';
                    $html .= '<dd>Yes, this component is designed to work on all screen sizes.</dd>';
                    $html .= '</dl>';
                    $html .= '</section>';
                    break;

                case 'call_to_action':
                    $html = '<section class="ai-component cta ' . esc($layoutClass) . '">';
                    $html .= '<div class="cta-content">';
                    $html .= '<h2>Ready to Get Started?</h2>';
                    $html .= '<p>' . esc(substr($instructions, 0, 120)) . '</p>';
                    $html .= '<button class="cta-button">Take Action Now</button>';
                    $html .= '</div>';
                    $html .= '</section>';
                    break;

                default:
                    $html = '<section class="ai-component generic ' . esc($layoutClass) . '">';
                    $html .= '<p>Component: ' . esc($component_type) . '</p>';
                    $html .= '</section>';
            }

            $result = [
                'title' => ucfirst(str_replace('_', ' ', $component_type)),
                'description' => 'Fallback ' . str_replace('_', ' ', $component_type) . ' component.',
                'html' => $html,
                'notes' => ['Generated with fallback (no AI).']
            ];
        }
    }
}

?>
<main class="container">
    <h1>AI Component Builder</h1>

    <?php if (!$hf_configured): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> Hugging Face is not configured. Fallback components will be used. Visit <a href="/admin/hf-settings.php">Hugging Face Settings</a> to configure.
        </div>
    <?php endif; ?>

    <?php if ($ai_used === true): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Used</strong> — This component was generated by Hugging Face AI.
        </div>
    <?php elseif ($ai_used === false && $result !== null): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> Fallback Used (no AI or AI error).
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
            <label for="component_type"><strong>Component Type</strong> (required)</label>
            <select id="component_type" name="component_type" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                <option value="">Select component type</option>
                <option value="hero"<?= $inputs['component_type'] === 'hero' ? ' selected' : '' ?>>Hero</option>
                <option value="pricing_table"<?= $inputs['component_type'] === 'pricing_table' ? ' selected' : '' ?>>Pricing Table</option>
                <option value="testimonial"<?= $inputs['component_type'] === 'testimonial' ? ' selected' : '' ?>>Testimonial</option>
                <option value="feature_list"<?= $inputs['component_type'] === 'feature_list' ? ' selected' : '' ?>>Feature List</option>
                <option value="faq"<?= $inputs['component_type'] === 'faq' ? ' selected' : '' ?>>FAQ</option>
                <option value="call_to_action"<?= $inputs['component_type'] === 'call_to_action' ? ' selected' : '' ?>>Call to Action</option>
            </select>
            <small style="color: #666;">Choose the type of component to generate</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="layout"><strong>Layout</strong> (required)</label>
            <select id="layout" name="layout" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                <option value="">Select layout</option>
                <option value="single_column"<?= $inputs['layout'] === 'single_column' ? ' selected' : '' ?>>Single Column</option>
                <option value="two_column"<?= $inputs['layout'] === 'two_column' ? ' selected' : '' ?>>Two Column</option>
                <option value="three_column"<?= $inputs['layout'] === 'three_column' ? ' selected' : '' ?>>Three Column</option>
            </select>
            <small style="color: #666;">Select the layout structure</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="theme"><strong>Theme</strong></label>
            <input type="text" id="theme" name="theme" value="<?= esc($inputs['theme']) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" maxlength="255">
            <small style="color: #666;">e.g., "school website", "SaaS landing", "portfolio"</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="language"><strong>Language</strong> (required)</label>
            <select id="language" name="language" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                <option value="en"<?= $inputs['language'] === 'en' ? ' selected' : '' ?>>English</option>
                <option value="pl"<?= $inputs['language'] === 'pl' ? ' selected' : '' ?>>Polish</option>
                <option value="de"<?= $inputs['language'] === 'de' ? ' selected' : '' ?>>German</option>
                <option value="fr"<?= $inputs['language'] === 'fr' ? ' selected' : '' ?>>French</option>
            </select>
            <small style="color: #666;">Language for component text</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="tone"><strong>Tone</strong></label>
            <select id="tone" name="tone" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">Auto</option>
                <option value="neutral"<?= $inputs['tone'] === 'neutral' ? ' selected' : '' ?>>Neutral</option>
                <option value="friendly"<?= $inputs['tone'] === 'friendly' ? ' selected' : '' ?>>Friendly</option>
                <option value="professional"<?= $inputs['tone'] === 'professional' ? ' selected' : '' ?>>Professional</option>
                <option value="bold"<?= $inputs['tone'] === 'bold' ? ' selected' : '' ?>>Bold</option>
            </select>
            <small style="color: #666;">Tone for component messaging</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="instructions"><strong>Instructions</strong> (required)</label>
            <textarea id="instructions" name="instructions" rows="5" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" maxlength="2000" required><?= esc($inputs['instructions']) ?></textarea>
            <small style="color: #666;">Describe what this component should do or display (max 2000 characters)</small>
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">Generate Component</button>
    </form>

    <?php if ($result !== null): ?>
        <div style="margin-top: 2rem; padding: 1.5rem; border: 1px solid #ccc; background: #f9f9f9; border-radius: 4px;">
            <h2>Generated Component</h2>

            <div style="margin-top: 1rem;">
                <h3 style="margin-bottom: 0.5rem;">Title</h3>
                <p style="font-weight: bold; margin: 0;"><?= esc($result['title']) ?></p>
            </div>

            <?php if ($result['description'] !== ''): ?>
                <div style="margin-top: 1rem;">
                    <h3 style="margin-bottom: 0.5rem;">Description</h3>
                    <p style="margin: 0;"><?= esc($result['description']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($result['notes'])): ?>
                <div style="margin-top: 1rem;">
                    <h3 style="margin-bottom: 0.5rem;">Notes</h3>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <?php foreach ($result['notes'] as $note): ?>
                            <li><?= esc($note) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div style="margin-top: 1.5rem;">
                <h2>Component Preview</h2>
                <div class="ai-component-preview" style="padding: 1rem; border: 1px solid #dee2e6; background: #ffffff; border-radius: 4px; margin-top: 0.5rem;">
                    <?= $result['html'] ?>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <h2>HTML Code</h2>
                <textarea style="width: 100%; padding: 1rem; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 0.9rem; background: #f8f9fa; overflow-x: auto;" rows="15" readonly><?= esc($result['html']) ?></textarea>
                <small style="color: #666;">Copy this HTML to use in your project</small>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
