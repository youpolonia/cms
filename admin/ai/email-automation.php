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

function esc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ai_email_automation_config_path() {
    return CMS_ROOT . '/config/ai_email_automation.json';
}

function ai_email_automation_load() {
    $path = ai_email_automation_config_path();
    if (!file_exists($path)) {
        return ['sequences' => []];
    }
    $content = @file_get_contents($path);
    if ($content === false) {
        error_log('Failed to read email automation config');
        return ['sequences' => []];
    }
    $decoded = @json_decode($content, true);
    if (!is_array($decoded) || !isset($decoded['sequences'])) {
        error_log('Invalid email automation config JSON');
        return ['sequences' => []];
    }
    return $decoded;
}

function ai_email_automation_save(array $config) {
    $path = ai_email_automation_config_path();
    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        error_log('Failed to encode email automation config to JSON');
        return false;
    }
    $result = @file_put_contents($path, $json . "\n", LOCK_EX);
    if ($result === false) {
        error_log('Failed to write email automation config');
        return false;
    }
    @chmod($path, 0644);
    return true;
}

function ai_email_automation_generate_fallback_steps($eventKey, $language, $tone) {
    $steps = [];

    if ($eventKey === 'user.registered') {
        if ($language === 'pl') {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Witamy w naszym CMS',
                'preview' => 'Zacznij korzystać z CMS w kilka minut.',
                'html' => '<p>Witamy! Dziękujemy za rejestrację w naszym systemie CMS.</p><p>Możesz teraz zacząć tworzyć i zarządzać swoimi treściami.</p>',
                'text' => 'Witamy! Dziękujemy za rejestrację w naszym systemie CMS. Możesz teraz zacząć tworzyć i zarządzać swoimi treściami.',
                'cta_label' => 'Przejdź do panelu',
                'cta_url' => '/admin'
            ];
            $steps[] = [
                'offset_days' => 2,
                'subject' => 'Odkryj kluczowe funkcje',
                'preview' => 'Poznaj najważniejsze możliwości naszego CMS.',
                'html' => '<p>Czy wiesz, że nasz CMS oferuje wiele przydatnych funkcji?</p><p>Sprawdź edytor AI, zarządzanie mediami i wiele więcej.</p>',
                'text' => 'Czy wiesz, że nasz CMS oferuje wiele przydatnych funkcji? Sprawdź edytor AI, zarządzanie mediami i wiele więcej.',
                'cta_label' => 'Dowiedz się więcej',
                'cta_url' => '/admin/help'
            ];
        } else if ($language === 'de') {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Willkommen bei unserem CMS',
                'preview' => 'Starten Sie mit Ihrem neuen CMS in Minuten.',
                'html' => '<p>Willkommen! Vielen Dank für Ihre Registrierung in unserem CMS.</p><p>Sie können jetzt beginnen, Ihre Inhalte zu erstellen und zu verwalten.</p>',
                'text' => 'Willkommen! Vielen Dank für Ihre Registrierung in unserem CMS. Sie können jetzt beginnen, Ihre Inhalte zu erstellen und zu verwalten.',
                'cta_label' => 'Zum Dashboard',
                'cta_url' => '/admin'
            ];
            $steps[] = [
                'offset_days' => 2,
                'subject' => 'Entdecken Sie die wichtigsten Funktionen',
                'preview' => 'Erfahren Sie mehr über die Möglichkeiten unseres CMS.',
                'html' => '<p>Wussten Sie, dass unser CMS viele nützliche Funktionen bietet?</p><p>Entdecken Sie den KI-Editor, die Medienverwaltung und vieles mehr.</p>',
                'text' => 'Wussten Sie, dass unser CMS viele nützliche Funktionen bietet? Entdecken Sie den KI-Editor, die Medienverwaltung und vieles mehr.',
                'cta_label' => 'Mehr erfahren',
                'cta_url' => '/admin/help'
            ];
        } else if ($language === 'fr') {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Bienvenue sur notre CMS',
                'preview' => 'Commencez à utiliser votre nouveau CMS en quelques minutes.',
                'html' => '<p>Bienvenue ! Merci de vous être inscrit sur notre CMS.</p><p>Vous pouvez maintenant commencer à créer et gérer votre contenu.</p>',
                'text' => 'Bienvenue ! Merci de vous être inscrit sur notre CMS. Vous pouvez maintenant commencer à créer et gérer votre contenu.',
                'cta_label' => 'Accéder au tableau de bord',
                'cta_url' => '/admin'
            ];
            $steps[] = [
                'offset_days' => 2,
                'subject' => 'Découvrez les fonctionnalités clés',
                'preview' => 'Apprenez-en plus sur les capacités de notre CMS.',
                'html' => '<p>Saviez-vous que notre CMS offre de nombreuses fonctionnalités utiles ?</p><p>Découvrez l\'éditeur IA, la gestion des médias et bien plus encore.</p>',
                'text' => 'Saviez-vous que notre CMS offre de nombreuses fonctionnalités utiles ? Découvrez l\'éditeur IA, la gestion des médias et bien plus encore.',
                'cta_label' => 'En savoir plus',
                'cta_url' => '/admin/help'
            ];
        } else {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Welcome to Our CMS',
                'preview' => 'Start using your new CMS in minutes.',
                'html' => '<p>Welcome! Thank you for registering with our CMS.</p><p>You can now start creating and managing your content.</p>',
                'text' => 'Welcome! Thank you for registering with our CMS. You can now start creating and managing your content.',
                'cta_label' => 'Go to Dashboard',
                'cta_url' => '/admin'
            ];
            $steps[] = [
                'offset_days' => 2,
                'subject' => 'Discover Key Features',
                'preview' => 'Learn about the capabilities of our CMS.',
                'html' => '<p>Did you know our CMS offers many useful features?</p><p>Explore the AI editor, media management, and more.</p>',
                'text' => 'Did you know our CMS offers many useful features? Explore the AI editor, media management, and more.',
                'cta_label' => 'Learn More',
                'cta_url' => '/admin/help'
            ];
        }
    } else if ($eventKey === 'lead.captured') {
        if ($language === 'pl') {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Dziękujemy za zainteresowanie',
                'preview' => 'Poznaj nasze rozwiązania.',
                'html' => '<p>Dziękujemy za wyrażenie zainteresowania naszymi rozwiązaniami.</p><p>Skontaktujemy się z Tobą wkrótce.</p>',
                'text' => 'Dziękujemy za wyrażenie zainteresowania naszymi rozwiązaniami. Skontaktujemy się z Tobą wkrótce.',
                'cta_label' => 'Zobacz więcej',
                'cta_url' => '/solutions'
            ];
        } else {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Thank You for Your Interest',
                'preview' => 'Discover our solutions.',
                'html' => '<p>Thank you for expressing interest in our solutions.</p><p>We will contact you shortly.</p>',
                'text' => 'Thank you for expressing interest in our solutions. We will contact you shortly.',
                'cta_label' => 'Learn More',
                'cta_url' => '/solutions'
            ];
        }
    } else {
        if ($language === 'pl') {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Witamy',
                'preview' => 'Dziękujemy za dołączenie do nas.',
                'html' => '<p>Witamy! Dziękujemy za dołączenie do nas.</p>',
                'text' => 'Witamy! Dziękujemy za dołączenie do nas.',
                'cta_label' => 'Rozpocznij',
                'cta_url' => '/start'
            ];
        } else {
            $steps[] = [
                'offset_days' => 0,
                'subject' => 'Welcome',
                'preview' => 'Thank you for joining us.',
                'html' => '<p>Welcome! Thank you for joining us.</p>',
                'text' => 'Welcome! Thank you for joining us.',
                'cta_label' => 'Get Started',
                'cta_url' => '/start'
            ];
        }
    }

    return $steps;
}

$errors = [];
$success = false;
$ai_used = false;
$ai_error = null;
$generated_steps_json = '';

$inputs = [
    'id' => '',
    'name' => '',
    'event_key' => '',
    'goal' => '',
    'audience' => '',
    'language' => 'en',
    'tone' => 'neutral',
    'steps_json' => ''
];

$hf_config = ai_hf_config_load();
$hf_configured = ai_hf_is_configured($hf_config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = trim((string)($_POST['action'] ?? ''));

    $inputs['id'] = trim((string)($_POST['id'] ?? ''));
    $inputs['name'] = trim((string)($_POST['name'] ?? ''));
    $inputs['event_key'] = trim((string)($_POST['event_key'] ?? ''));
    $inputs['goal'] = trim((string)($_POST['goal'] ?? ''));
    $inputs['audience'] = trim((string)($_POST['audience'] ?? ''));
    $inputs['language'] = trim((string)($_POST['language'] ?? 'en'));
    $inputs['tone'] = trim((string)($_POST['tone'] ?? 'neutral'));
    $inputs['steps_json'] = trim((string)($_POST['steps_json'] ?? ''));

    if (strlen($inputs['id']) === 0) {
        $errors[] = 'Sequence ID is required';
    } else if (strlen($inputs['id']) > 64) {
        $errors[] = 'Sequence ID is too long (maximum 64 characters)';
    } else if (!preg_match('/^[a-z0-9._-]+$/', $inputs['id'])) {
        $errors[] = 'Sequence ID must contain only lowercase letters, numbers, dots, underscores, and hyphens';
    }

    if (strlen($inputs['name']) === 0) {
        $errors[] = 'Sequence name is required';
    } else if (strlen($inputs['name']) > 255) {
        $errors[] = 'Sequence name is too long (maximum 255 characters)';
    }

    if (strlen($inputs['event_key']) === 0) {
        $errors[] = 'Event key is required';
    } else if (strlen($inputs['event_key']) > 255) {
        $errors[] = 'Event key is too long (maximum 255 characters)';
    } else if (!preg_match('/^[a-z0-9._-]+$/', $inputs['event_key'])) {
        $errors[] = 'Event key must contain only lowercase letters, numbers, dots, underscores, and hyphens';
    }

    if (strlen($inputs['goal']) === 0) {
        $errors[] = 'Goal is required';
    } else if (strlen($inputs['goal']) > 2000) {
        $errors[] = 'Goal is too long (maximum 2000 characters)';
    }

    if (strlen($inputs['audience']) > 255) {
        $errors[] = 'Audience is too long (maximum 255 characters)';
    }

    $validLanguages = ['en', 'pl', 'de', 'fr'];
    if (!in_array($inputs['language'], $validLanguages, true)) {
        $errors[] = 'Invalid language selected';
    }

    $validTones = ['neutral', 'friendly', 'professional', 'persuasive'];
    if (!in_array($inputs['tone'], $validTones, true)) {
        $errors[] = 'Invalid tone selected';
    }

    if ($action === 'save') {
        if (strlen($inputs['steps_json']) === 0) {
            $errors[] = 'Steps JSON is required';
        } else if (strlen($inputs['steps_json']) > 20000) {
            $errors[] = 'Steps JSON is too long (maximum 20000 characters)';
        } else {
            $decodedSteps = @json_decode($inputs['steps_json'], true);
            if (!is_array($decodedSteps)) {
                $errors[] = 'Steps JSON is invalid';
            } else {
                foreach ($decodedSteps as $idx => $step) {
                    if (!is_array($step)) {
                        $errors[] = 'Step ' . ($idx + 1) . ' is not a valid object';
                        break;
                    }
                    if (!isset($step['offset_days']) || !is_numeric($step['offset_days']) || intval($step['offset_days']) < 0) {
                        $errors[] = 'Step ' . ($idx + 1) . ' has invalid offset_days (must be integer >= 0)';
                        break;
                    }
                    if (!isset($step['subject']) || trim((string)$step['subject']) === '') {
                        $errors[] = 'Step ' . ($idx + 1) . ' is missing subject';
                        break;
                    }
                    if (strlen((string)$step['subject']) > 255) {
                        $errors[] = 'Step ' . ($idx + 1) . ' subject is too long (maximum 255 characters)';
                        break;
                    }
                    if (isset($step['preview']) && strlen((string)$step['preview']) > 255) {
                        $errors[] = 'Step ' . ($idx + 1) . ' preview is too long (maximum 255 characters)';
                        break;
                    }
                    if (isset($step['cta_label']) && strlen((string)$step['cta_label']) > 255) {
                        $errors[] = 'Step ' . ($idx + 1) . ' CTA label is too long (maximum 255 characters)';
                        break;
                    }
                    if (isset($step['cta_url']) && strlen((string)$step['cta_url']) > 255) {
                        $errors[] = 'Step ' . ($idx + 1) . ' CTA URL is too long (maximum 255 characters)';
                        break;
                    }
                }

                if (empty($errors)) {
                    $config = ai_email_automation_load();
                    foreach ($config['sequences'] as $existing) {
                        if ($existing['id'] === $inputs['id']) {
                            $errors[] = 'A sequence with this ID already exists';
                            break;
                        }
                    }

                    if (empty($errors)) {
                        $normalizedSteps = [];
                        foreach ($decodedSteps as $step) {
                            $normalizedSteps[] = [
                                'offset_days' => intval($step['offset_days']),
                                'subject' => trim((string)$step['subject']),
                                'preview' => isset($step['preview']) ? trim((string)$step['preview']) : '',
                                'html' => isset($step['html']) ? (string)$step['html'] : '',
                                'text' => isset($step['text']) ? (string)$step['text'] : '',
                                'cta_label' => isset($step['cta_label']) ? trim((string)$step['cta_label']) : '',
                                'cta_url' => isset($step['cta_url']) ? trim((string)$step['cta_url']) : ''
                            ];
                        }

                        $newSequence = [
                            'id' => $inputs['id'],
                            'name' => $inputs['name'],
                            'event_key' => $inputs['event_key'],
                            'goal' => $inputs['goal'],
                            'audience' => $inputs['audience'],
                            'language' => $inputs['language'],
                            'tone' => $inputs['tone'],
                            'steps' => $normalizedSteps
                        ];

                        $config['sequences'][] = $newSequence;

                        if (ai_email_automation_save($config)) {
                            $success = true;
                            $inputs = [
                                'id' => '',
                                'name' => '',
                                'event_key' => '',
                                'goal' => '',
                                'audience' => '',
                                'language' => 'en',
                                'tone' => 'neutral',
                                'steps_json' => ''
                            ];
                        } else {
                            $errors[] = 'Failed to save sequence configuration';
                        }
                    }
                }
            }
        }
    } else if ($action === 'generate') {
        if (empty($errors)) {
            if (!$hf_configured) {
                $ai_error = 'Hugging Face is not configured. Using placeholder steps.';
                $fallbackSteps = ai_email_automation_generate_fallback_steps($inputs['event_key'], $inputs['language'], $inputs['tone']);
                $inputs['steps_json'] = json_encode($fallbackSteps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $promptParts = ['You are an expert email marketing automation designer. Generate a sequence of automated emails based on the following parameters:'];
                $promptParts[] = 'Event: ' . $inputs['event_key'];
                $promptParts[] = 'Goal: ' . $inputs['goal'];
                if ($inputs['audience'] !== '') {
                    $promptParts[] = 'Audience: ' . $inputs['audience'];
                }
                $promptParts[] = 'Language: ' . $inputs['language'];
                $promptParts[] = 'Tone: ' . $inputs['tone'];
                $promptParts[] = 'Generate 3-5 email steps with different offset_days (between 0 and 30). Respond ONLY with valid JSON in this exact schema:';
                $promptParts[] = '{"steps":[{"offset_days":0,"subject":"...","preview":"...","html":"<p>...</p>","text":"...","cta_label":"...","cta_url":"https://..."}]}';
                $promptParts[] = 'Requirements: offset_days must be integers 0-30. Subject max 60 chars. Preview max 120 chars. HTML should be simple email-safe markup. Text should be plain text version. CTA label and URL are optional. No explanations, no markdown, no extra text outside JSON.';

                $prompt = implode("\n\n", $promptParts);

                $options = [
                    'max_new_tokens' => 512,
                    'temperature' => 0.7,
                    'top_p' => 0.9
                ];

                $result = ai_hf_infer($hf_config, $prompt, $options);

                if (!$result['ok']) {
                    error_log('AI email automation generation failed: ' . ($result['error'] ?? 'Unknown error'));
                    $ai_error = 'AI generation failed. Using placeholder steps.';
                    $fallbackSteps = ai_email_automation_generate_fallback_steps($inputs['event_key'], $inputs['language'], $inputs['tone']);
                    $inputs['steps_json'] = json_encode($fallbackSteps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

                    if ($aiData === null || !is_array($aiData) || !isset($aiData['steps']) || !is_array($aiData['steps'])) {
                        error_log('AI email automation returned invalid JSON');
                        $ai_error = 'AI returned invalid data. Using placeholder steps.';
                        $fallbackSteps = ai_email_automation_generate_fallback_steps($inputs['event_key'], $inputs['language'], $inputs['tone']);
                        $inputs['steps_json'] = json_encode($fallbackSteps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } else {
                        $normalizedSteps = [];
                        foreach ($aiData['steps'] as $step) {
                            if (!is_array($step)) {
                                continue;
                            }
                            $offsetDays = isset($step['offset_days']) && is_numeric($step['offset_days']) ? intval($step['offset_days']) : 0;
                            if ($offsetDays < 0) {
                                $offsetDays = 0;
                            }
                            if ($offsetDays > 60) {
                                $offsetDays = 60;
                            }

                            $subject = isset($step['subject']) && trim((string)$step['subject']) !== ''
                                ? trim(substr((string)$step['subject'], 0, 255))
                                : 'Email subject';

                            $preview = isset($step['preview']) ? trim(substr((string)$step['preview'], 0, 255)) : '';
                            $html = isset($step['html']) ? (string)$step['html'] : '';
                            $text = isset($step['text']) ? (string)$step['text'] : '';
                            $ctaLabel = isset($step['cta_label']) ? trim(substr((string)$step['cta_label'], 0, 255)) : '';
                            $ctaUrl = isset($step['cta_url']) ? trim(substr((string)$step['cta_url'], 0, 255)) : '';

                            $normalizedSteps[] = [
                                'offset_days' => $offsetDays,
                                'subject' => $subject,
                                'preview' => $preview,
                                'html' => $html,
                                'text' => $text,
                                'cta_label' => $ctaLabel,
                                'cta_url' => $ctaUrl
                            ];
                        }

                        if (count($normalizedSteps) === 0) {
                            $ai_error = 'AI returned no valid steps. Using placeholder steps.';
                            $fallbackSteps = ai_email_automation_generate_fallback_steps($inputs['event_key'], $inputs['language'], $inputs['tone']);
                            $inputs['steps_json'] = json_encode($fallbackSteps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        } else {
                            $inputs['steps_json'] = json_encode($normalizedSteps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            $ai_used = true;
                        }
                    }
                }
            }
        }
    }
}

$sequences = ai_email_automation_load()['sequences'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="container">
    <h1>AI Email Automation</h1>

    <?php if (!$hf_configured): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>⚠ AI Not Configured</strong> — Hugging Face is not configured. Fallback templates will be used for generating sequences.
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ Success</strong> — Sequence saved successfully.
        </div>
    <?php endif; ?>

    <?php if ($ai_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Used</strong> — Steps generated by Hugging Face.
        </div>
    <?php endif; ?>

    <?php if ($ai_error !== null): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>⚠ AI Warning</strong> — <?= esc($ai_error) ?>
        </div>
    <?php endif; ?>

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

    <div style="margin-bottom: 3rem;">
        <h2>Existing Sequences</h2>
        <?php if (count($sequences) === 0): ?>
            <p style="color: #6c757d;">No sequences configured yet.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 0.75rem; text-align: left;">ID</th>
                        <th style="padding: 0.75rem; text-align: left;">Name</th>
                        <th style="padding: 0.75rem; text-align: left;">Event Key</th>
                        <th style="padding: 0.75rem; text-align: left;">Language</th>
                        <th style="padding: 0.75rem; text-align: left;">Tone</th>
                        <th style="padding: 0.75rem; text-align: left;">Steps</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sequences as $seq): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 0.75rem;"><?= esc($seq['id']) ?></td>
                            <td style="padding: 0.75rem;"><?= esc($seq['name']) ?></td>
                            <td style="padding: 0.75rem;"><?= esc($seq['event_key']) ?></td>
                            <td style="padding: 0.75rem;"><?= esc($seq['language']) ?></td>
                            <td style="padding: 0.75rem;"><?= esc($seq['tone']) ?></td>
                            <td style="padding: 0.75rem;"><?= count($seq['steps']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div>
        <h2>Create New Sequence</h2>
        <form method="post" style="max-width: 800px;">
            <?php csrf_field(); ?>

            <div style="margin-bottom: 1rem;">
                <label for="id"><strong>Sequence ID</strong> (required)</label>
                <input type="text" id="id" name="id" value="<?= esc($inputs['id']) ?>" style="width: 100%;" required maxlength="64" pattern="[a-z0-9._-]+">
                <small>Unique identifier (lowercase, numbers, dots, underscores, hyphens only)</small>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="name"><strong>Sequence Name</strong> (required)</label>
                <input type="text" id="name" name="name" value="<?= esc($inputs['name']) ?>" style="width: 100%;" required maxlength="255">
                <small>Descriptive name for this sequence</small>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="event_key"><strong>Event Key</strong> (required)</label>
                <input type="text" id="event_key" name="event_key" value="<?= esc($inputs['event_key']) ?>" style="width: 100%;" required maxlength="255" pattern="[a-z0-9._-]+">
                <small>Event that triggers this sequence (e.g., user.registered, lead.captured)</small>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="goal"><strong>Goal</strong> (required)</label>
                <textarea id="goal" name="goal" rows="4" style="width: 100%;" required maxlength="2000"><?= esc($inputs['goal']) ?></textarea>
                <small>What should this sequence achieve? (max 2000 characters)</small>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="audience"><strong>Audience</strong> (optional)</label>
                <input type="text" id="audience" name="audience" value="<?= esc($inputs['audience']) ?>" style="width: 100%;" maxlength="255">
                <small>Target audience for this sequence (max 255 characters)</small>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="language"><strong>Language</strong></label>
                <select id="language" name="language" style="width: 100%;">
                    <option value="en"<?= $inputs['language'] === 'en' ? ' selected' : '' ?>>English</option>
                    <option value="pl"<?= $inputs['language'] === 'pl' ? ' selected' : '' ?>>Polish</option>
                    <option value="de"<?= $inputs['language'] === 'de' ? ' selected' : '' ?>>German</option>
                    <option value="fr"<?= $inputs['language'] === 'fr' ? ' selected' : '' ?>>French</option>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="tone"><strong>Tone</strong></label>
                <select id="tone" name="tone" style="width: 100%;">
                    <option value="neutral"<?= $inputs['tone'] === 'neutral' ? ' selected' : '' ?>>Neutral</option>
                    <option value="friendly"<?= $inputs['tone'] === 'friendly' ? ' selected' : '' ?>>Friendly</option>
                    <option value="professional"<?= $inputs['tone'] === 'professional' ? ' selected' : '' ?>>Professional</option>
                    <option value="persuasive"<?= $inputs['tone'] === 'persuasive' ? ' selected' : '' ?>>Persuasive</option>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="steps_json"><strong>Steps (JSON)</strong></label>
                <textarea id="steps_json" name="steps_json" rows="12" style="width: 100%; font-family: monospace; font-size: 0.9rem;"><?= esc($inputs['steps_json']) ?></textarea>
                <small>JSON array of email steps. Generate with AI or edit manually.</small>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="action" value="generate" style="padding: 0.75rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                    Generate Steps with AI
                </button>
                <button type="submit" name="action" value="save" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                    Save Sequence
                </button>
            </div>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
