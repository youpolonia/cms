<?php
/**
 * AI Email Campaign Generator - Admin Tool
 * Generate complete email campaign sequences using Hugging Face text models
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();


require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_email_campaign.php';

// Initialize view-model variables
$form = [
    'name'         => '',
    'audience'     => '',
    'offer'        => '',
    'goal'         => '',
    'emails_count' => '5',
    'tone'         => '',
    'language'     => 'en',
    'notes'        => '',
];

$campaign = null;
$generatedJson = '';
$generatorError = null;

// Multi-provider support: default to huggingface
$selectedProvider = $_POST['ai_provider'] ?? 'huggingface';
$selectedModel = $_POST['ai_model'] ?? '';

// Check if at least one provider is available
$config = ai_hf_config_load();
$hfConfigured = ai_hf_is_configured($config);
$anyProviderAvailable = $hfConfigured || !empty(ai_get_all_providers());

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_campaign') {
    csrf_validate_or_403();

    // Fill form from POST data
    $form['name'] = isset($_POST['name']) ? trim($_POST['name']) : '';
    $form['audience'] = isset($_POST['audience']) ? trim($_POST['audience']) : '';
    $form['offer'] = isset($_POST['offer']) ? trim($_POST['offer']) : '';
    $form['goal'] = isset($_POST['goal']) ? trim($_POST['goal']) : '';
    $form['emails_count'] = isset($_POST['emails_count']) ? trim($_POST['emails_count']) : '5';
    $form['tone'] = isset($_POST['tone']) ? trim($_POST['tone']) : '';
    $form['language'] = isset($_POST['language']) ? trim($_POST['language']) : 'en';
    $form['notes'] = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // Get provider/model from form
    $provider = trim($_POST['ai_provider'] ?? 'huggingface');
    $model = trim($_POST['ai_model'] ?? '');

    // Validate provider, fallback to huggingface
    if (!ai_is_valid_provider($provider)) {
        $provider = 'huggingface';
    }

    // Get default model if not specified
    if ($model === '' || !ai_is_valid_provider_model($provider, $model)) {
        $model = ai_get_provider_default_model($provider);
    }

    $selectedProvider = $provider;
    $selectedModel = $model;

    // Call generator with provider/model
    $result = ai_email_campaign_generate($form, $provider, $model);

    if ($result['ok']) {
        $campaign = $result['campaign'];
        $generatedJson = $result['json'];
    } else {
        $generatorError = $result['error'];
    }
}

// Helper function for escaping output
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>AI Email Campaign Generator</h1>

        <p style="margin: 8px 0 16px 0; color: #495057;">
            Generate a complete multi-email campaign (drip sequence) using your Hugging Face text model. Phase 1: generator + preview only.
        </p>

        <?php if (!$anyProviderAvailable): ?>
            <div class="alert alert-warning" style="padding: 12px; margin: 16px 0; border-radius: 4px; background-color: var(--bg3); border: 1px solid var(--warning); color: var(--warning);">
                <strong>Warning:</strong> No AI providers configured. Please configure at least one provider in AI Settings.
            </div>
        <?php endif; ?>

        <?php if ($generatorError !== null): ?>
            <div class="alert alert-error" style="padding: 12px; margin: 16px 0; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                <strong>Error:</strong> <?php echo esc($generatorError); ?>
            </div>
        <?php endif; ?>

        <div class="config-info" style="padding: 16px; margin: 16px 0; background-color: var(--bg3, #313244); border: 1px solid var(--border, #313244); border-radius: 4px;">
            <h3>Campaign Specification Form</h3>
            <p style="margin: 8px 0; color: #6c757d;">
                Provide details about the email campaign you want to generate. The more specific you are, the better the results.
            </p>
        </div>

        <form method="POST" action="" style="max-width: 800px;">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="generate_campaign">

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                    AI Provider & Model:
                </label>
                <?= ai_render_dual_selector('ai_provider', 'ai_model', $selectedProvider, $selectedModel) ?>
                <small style="color: #6c757d;">Select AI provider. HuggingFace is default for email campaigns.</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Campaign Name: <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo esc($form['name']); ?>"
                    placeholder="e.g. Onboarding sequence for CMS trial users"
                    required
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="audience" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Target Audience:
                </label>
                <input
                    type="text"
                    id="audience"
                    name="audience"
                    value="<?php echo esc($form['audience']); ?>"
                    placeholder="e.g. small business owners in the UK"
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="offer" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Offer / Product:
                </label>
                <input
                    type="text"
                    id="offer"
                    name="offer"
                    value="<?php echo esc($form['offer']); ?>"
                    placeholder="e.g. your pure-PHP CMS on shared hosting"
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="goal" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Main Goal: <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="goal"
                    name="goal"
                    value="<?php echo esc($form['goal']); ?>"
                    placeholder="e.g. convert free trials to paid plans"
                    required
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="emails_count" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Number of Emails:
                </label>
                <input
                    type="number"
                    id="emails_count"
                    name="emails_count"
                    min="1"
                    max="10"
                    value="<?php echo esc($form['emails_count']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
                <small style="color: #6c757d;">Enter a number between 1 and 10.</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="tone" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Tone of Voice:
                </label>
                <input
                    type="text"
                    id="tone"
                    name="tone"
                    value="<?php echo esc($form['tone']); ?>"
                    placeholder="e.g. friendly, professional"
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="language" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Language:
                </label>
                <select
                    id="language"
                    name="language"
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                >
                    <option value="en" <?php echo $form['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                    <option value="pl" <?php echo $form['language'] === 'pl' ? 'selected' : ''; ?>>Polski</option>
                    <option value="de" <?php echo $form['language'] === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                    <option value="es" <?php echo $form['language'] === 'es' ? 'selected' : ''; ?>>Español</option>
                    <option value="fr" <?php echo $form['language'] === 'fr' ? 'selected' : ''; ?>>Français</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="notes" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Additional Notes:
                </label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    placeholder="e.g. no hard-sell, focus on education"
                    style="width: 100%; padding: 8px; border: 1px solid var(--border, #313244); border-radius: 4px;"
                ><?php echo esc($form['notes']); ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <button
                    type="submit"
                    style="padding: 12px 24px; background-color: var(--accent); color: var(--bg); border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;<?php if (!$anyProviderAvailable) echo ' opacity: 0.5;'; ?>"
                    <?php if (!$anyProviderAvailable) echo ' disabled="disabled"'; ?>
                >
                    Generate Campaign
                </button>
            </div>
        </form>

        <?php if ($campaign !== null && is_array($campaign) && $generatedJson !== ''): ?>
            <hr style="margin: 32px 0; border: 0; border-top: 1px solid #dee2e6;">

            <div class="campaign-summary" style="padding: 16px; margin: 16px 0; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
                <h3 style="margin: 0 0 12px 0; color: #155724;">Campaign Generated Successfully</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold; width: 150px;">Campaign Name:</td>
                        <td style="padding: 4px 0;"><?php echo esc($campaign['campaign_name'] ?? $form['name']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold;">Language:</td>
                        <td style="padding: 4px 0;"><?php echo esc($campaign['language'] ?? $form['language']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold;">Goal:</td>
                        <td style="padding: 4px 0;"><?php echo esc($campaign['goal'] ?? $form['goal']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-weight: bold;">Number of Emails:</td>
                        <td style="padding: 4px 0;"><?php echo (int)count($campaign['emails']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="json-output" style="margin-top: 32px;">
                <h2>Campaign JSON</h2>
                <p style="margin: 8px 0; color: #6c757d;">
                    You can copy this JSON and store it for later integration with scheduler + email queue.
                </p>
                <textarea
                    readonly
                    rows="16"
                    style="width: 100%; padding: 12px; border: 1px solid var(--border, #313244); border-radius: 4px; font-family: monospace; font-size: 12px; background-color: var(--bg3, #313244);"
                    onclick="this.select();"
                ><?php echo esc($generatedJson); ?></textarea>
            </div>

            <div class="email-previews" style="margin-top: 32px;">
                <h2>Email Sequence Preview</h2>
                <p style="margin: 8px 0 16px 0; color: #6c757d;">
                    Below is a visual preview of each email in the campaign.
                </p>

                <?php foreach ($campaign['emails'] as $idx => $email): ?>
                    <div class="email-card" style="margin-bottom: 24px; padding: 20px; border: 1px solid var(--border); border-radius: 8px; background-color: var(--bg2);">
                        <h3 style="margin: 0 0 12px 0; color: #007bff;">
                            Email #<?php echo (int)($email['index'] ?? ($idx + 1)); ?>
                        </h3>

                        <div style="margin-bottom: 12px;">
                            <strong>Subject:</strong> <?php echo esc($email['subject'] ?? ''); ?>
                        </div>

                        <?php if (isset($email['preview_text']) && trim($email['preview_text']) !== ''): ?>
                            <div style="margin-bottom: 12px;">
                                <strong>Preview Text:</strong> <?php echo esc($email['preview_text']); ?>
                            </div>
                        <?php endif; ?>

                        <div style="margin-bottom: 16px;">
                            <strong>HTML Body Preview:</strong>
                            <div class="email-html-preview" style="margin-top: 8px; padding: 16px; border: 1px solid var(--border, #313244); border-radius: 4px; background-color: var(--bg3, #313244); overflow-x: auto;">
                                <?php
                                // Intentionally not escaped for HTML preview (trusted admin content)
                                $htmlBody = isset($email['html_body']) ? (string)$email['html_body'] : '';
                                echo $htmlBody;
                                ?>
                            </div>
                        </div>

                        <?php if (isset($email['text_body']) && trim($email['text_body']) !== ''): ?>
                            <div>
                                <strong>Plain Text Body:</strong>
                                <pre class="email-text-preview" style="margin-top: 8px; padding: 12px; border: 1px solid var(--border, #313244); border-radius: 4px; background-color: var(--bg3, #313244); white-space: pre-wrap; font-family: monospace; font-size: 12px; overflow-x: auto;"><?php echo esc($email['text_body']); ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($campaign === null && $generatedJson === '' && $generatorError === null): ?>
            <div class="info-panel" style="padding: 16px; margin: 16px 0; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; color: #0c5460;">
                <h3 style="margin: 0 0 8px 0;">How to Use</h3>
                <ol style="margin: 8px 0 0 20px; padding: 0;">
                    <li>Fill in the campaign specification form above with your campaign details.</li>
                    <li>Click "Generate Campaign" to create your email sequence.</li>
                    <li>Review the generated emails in the preview section.</li>
                    <li>Copy the JSON output to save or integrate with your email automation system.</li>
                </ol>
                <p style="margin: 12px 0 0 0;">
                    <strong>Note:</strong> This is Phase 1 - generator and preview only. Future phases will integrate with the scheduler and email queue.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
