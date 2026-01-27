<?php
/**
 * AI Reports & Insights - Phase 1
 *
 * AI-powered SEO, UX and content strategy report generator.
 * Takes admin-provided context (site info, analytics summary, goals)
 * and generates structured recommendations via Hugging Face.
 *
 * Phase 1: Text-based input only, no automatic analytics integration.
 */

if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) {
        die('Cannot determine CMS_ROOT');
    }
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_insights.php';

cms_session_start('admin');
csrf_boot('admin');

// DEV_MODE gate
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

cms_require_admin_role();

// Helper function for escaping
function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// View-model variables
$form = [
    'site_name'         => '',
    'site_url'          => '',
    'audience'          => '',
    'primary_goal'      => '',
    'secondary_goals'   => '',
    'current_issues'    => '',
    'content_overview'  => '',
    'analytics_summary' => '',
    'timeframe'         => 'last 30 days',
    'language'          => 'en',
    'notes'             => '',
];

$report         = null;
$generatedJson  = '';
$generatorError = null;

// Multi-provider support: default to huggingface
$selectedProvider = $_POST['ai_provider'] ?? 'huggingface';
$selectedModel = $_POST['ai_model'] ?? '';

// Check if at least one provider is available
$hfConfig = function_exists('ai_hf_config_load') ? ai_hf_config_load() : [];
$hfConfigured   = function_exists('ai_hf_is_configured') ? ai_hf_is_configured($hfConfig) : false;
$anyProviderAvailable = $hfConfigured || !empty(ai_get_all_providers());

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_insights') {
    csrf_validate_or_403();

    // Populate form from POST
    foreach (array_keys($form) as $key) {
        $form[$key] = trim((string)($_POST[$key] ?? ''));
    }

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

    // Generate report
    $result = ai_insights_generate_report($form, $provider, $model);

    if ($result['ok']) {
        $report        = $result['report'];
        $generatedJson = $result['json'];
    } else {
        $generatorError = $result['error'];
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <h1 class="mt-4">AI Reports &amp; Insights</h1>
        <p class="text-muted">Generate SEO, content and UX recommendations using your Hugging Face model.<br>
        <strong>Phase 1:</strong> Text-based input + AI report — no automatic analytics integration yet.</p>

        <?php if (!$anyProviderAvailable): ?>
            <!-- No AI provider configured -->
            <div class="alert alert-warning" role="alert">
                <strong>No AI providers configured.</strong>
                Please configure at least one provider in <a href="ai-settings.php" class="alert-link">AI Settings</a> before generating reports.
            </div>
        <?php endif; ?>

        <?php if ($generatorError !== null): ?>
            <!-- Generation error -->
            <div class="alert alert-danger" role="alert">
                <strong>Error:</strong> <?= esc($generatorError) ?>
            </div>
        <?php endif; ?>

        <?php if ($report === null && $generatorError === null): ?>
            <!-- First load info -->
            <div class="alert alert-info" role="alert">
                <strong>How it works:</strong>
                <ul class="mb-0 mt-2">
                    <li>Fill in your site details, goals, and paste a short analytics summary</li>
                    <li>AI will analyze the information and suggest SEO, UX and content improvements</li>
                    <li>Phase 1 is text-only: no automatic connection to Analytics/GA4/Search Console yet</li>
                    <li>You'll receive a JSON report + human-readable recommendations</li>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Report Generation Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Site &amp; Analytics Context</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="generate_insights">

                    <!-- AI Provider & Model -->
                    <div class="mb-3">
                        <label class="form-label">AI Provider & Model</label>
                        <?= ai_render_dual_selector('ai_provider', 'ai_model', $selectedProvider, $selectedModel) ?>
                        <small class="form-text text-muted">Select AI provider. HuggingFace is default for insights generation.</small>
                    </div>

                    <!-- Site name -->
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="site_name" name="site_name"
                               value="<?= esc($form['site_name']) ?>" required
                               placeholder="e.g., Polish Saturday School CMS demo">
                    </div>

                    <!-- Site URL -->
                    <div class="mb-3">
                        <label for="site_url" class="form-label">Site URL</label>
                        <input type="text" class="form-control" id="site_url" name="site_url"
                               value="<?= esc($form['site_url']) ?>"
                               placeholder="https://example.com">
                    </div>

                    <!-- Target audience -->
                    <div class="mb-3">
                        <label for="audience" class="form-label">Target Audience</label>
                        <input type="text" class="form-control" id="audience" name="audience"
                               value="<?= esc($form['audience']) ?>"
                               placeholder="e.g., parents in UK, small businesses, tech professionals">
                    </div>

                    <!-- Primary goal -->
                    <div class="mb-3">
                        <label for="primary_goal" class="form-label">Primary Business Goal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="primary_goal" name="primary_goal"
                               value="<?= esc($form['primary_goal']) ?>" required
                               placeholder="e.g., get demo requests, increase sales, newsletter signups">
                    </div>

                    <!-- Secondary goals -->
                    <div class="mb-3">
                        <label for="secondary_goals" class="form-label">Secondary Goals</label>
                        <input type="text" class="form-control" id="secondary_goals" name="secondary_goals"
                               value="<?= esc($form['secondary_goals']) ?>"
                               placeholder="e.g., newsletter signups, blog reads, social shares">
                    </div>

                    <!-- Current issues -->
                    <div class="mb-3">
                        <label for="current_issues" class="form-label">Current Issues</label>
                        <textarea class="form-control" id="current_issues" name="current_issues" rows="3"
                                  placeholder="Describe any SEO, UX, speed or content problems you're experiencing..."><?= esc($form['current_issues']) ?></textarea>
                        <small class="form-text text-muted">Free-text description of problems (SEO/UX/speed/etc.)</small>
                    </div>

                    <!-- Content overview -->
                    <div class="mb-3">
                        <label for="content_overview" class="form-label">Content Overview</label>
                        <textarea class="form-control" id="content_overview" name="content_overview" rows="3"
                                  placeholder="e.g., 20 blog posts about education, 5 service pages, photo gallery..."><?= esc($form['content_overview']) ?></textarea>
                        <small class="form-text text-muted">Short overview of your current content (blog types, pages)</small>
                    </div>

                    <!-- Analytics summary -->
                    <div class="mb-3">
                        <label for="analytics_summary" class="form-label">Analytics Summary</label>
                        <textarea class="form-control" id="analytics_summary" name="analytics_summary" rows="4"
                                  placeholder="Paste key metrics: sessions, bounce rate, CTR, top pages, conversion rates, etc."><?= esc($form['analytics_summary']) ?></textarea>
                        <small class="form-text text-muted">Paste key metrics: sessions, bounce rate, CTR, top pages, etc.</small>
                    </div>

                    <!-- Timeframe -->
                    <div class="mb-3">
                        <label for="timeframe" class="form-label">Analytics Timeframe</label>
                        <input type="text" class="form-control" id="timeframe" name="timeframe"
                               value="<?= esc($form['timeframe']) ?>"
                               placeholder="e.g., last 30 days, Q4 2024">
                    </div>

                    <!-- Language -->
                    <div class="mb-3">
                        <label for="language" class="form-label">Report Language</label>
                        <select class="form-control" id="language" name="language">
                            <option value="en" <?php if ($form['language'] === 'en') echo 'selected'; ?>>English</option>
                            <option value="pl" <?php if ($form['language'] === 'pl') echo 'selected'; ?>>Polski</option>
                            <option value="de" <?php if ($form['language'] === 'de') echo 'selected'; ?>>Deutsch</option>
                            <option value="es" <?php if ($form['language'] === 'es') echo 'selected'; ?>>Español</option>
                            <option value="fr" <?php if ($form['language'] === 'fr') echo 'selected'; ?>>Français</option>
                        </select>
                    </div>

                    <!-- Additional notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="e.g., focus on organic SEO, mobile-first, EU GDPR compliance..."><?= esc($form['notes']) ?></textarea>
                        <small class="form-text text-muted">Extra constraints or focus areas</small>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary btn-lg"<?php if (!$anyProviderAvailable) echo ' disabled="disabled"'; ?>>
                        Generate Report
                    </button>
                </form>
            </div>
        </div>

        <?php if (is_array($report) && $generatedJson !== ''): ?>
            <!-- Report Results -->

            <!-- Success Summary -->
            <div class="alert alert-success" role="alert">
                <strong>✓ Report generated successfully</strong>
                <ul class="mb-0 mt-2">
                    <li>Site: <?= esc($form['site_name']) ?></li>
                    <li>Language: <?= esc($form['language']) ?></li>
                    <li>Recommendations: <?= count($report['recommendations'] ?? []) ?></li>
                    <li>Quick Wins: <?= count($report['quick_wins'] ?? []) ?></li>
                </ul>
            </div>

            <!-- Raw JSON Output -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Raw JSON Report</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">You can copy this JSON for future integrations (dashboards, scheduler-driven audits, etc.).</p>
                    <textarea class="form-control" rows="16" readonly style="font-family: monospace; font-size: 0.85rem;"><?= esc($generatedJson) ?></textarea>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(esc($report['summary'] ?? '')) ?></p>
                </div>
            </div>

            <!-- Strengths Section -->
            <?php if (isset($report['strengths']) && is_array($report['strengths']) && count($report['strengths']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Strengths</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <?php foreach ($report['strengths'] as $strength): ?>
                                <li><?= esc($strength) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Strengths</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No strengths reported.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Issues Section -->
            <?php if (isset($report['issues']) && is_array($report['issues']) && count($report['issues']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Key Issues</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    <th>Description</th>
                                    <th>Impact</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report['issues'] as $issue): ?>
                                    <tr>
                                        <td><?= esc($issue['area'] ?? '') ?></td>
                                        <td><?= esc($issue['description'] ?? '') ?></td>
                                        <td><?= esc($issue['impact'] ?? '') ?></td>
                                        <td><?= (int)($issue['priority'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Key Issues</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No issues identified.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recommendations Section -->
            <?php if (isset($report['recommendations']) && is_array($report['recommendations']) && count($report['recommendations']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    <th>Action</th>
                                    <th>Why</th>
                                    <th>Impact</th>
                                    <th>Difficulty</th>
                                    <th>Timeframe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report['recommendations'] as $rec): ?>
                                    <tr>
                                        <td><?= esc($rec['area'] ?? '') ?></td>
                                        <td><?= esc($rec['action'] ?? '') ?></td>
                                        <td><?= esc($rec['why'] ?? '') ?></td>
                                        <td><?= esc($rec['impact'] ?? '') ?></td>
                                        <td><?= esc($rec['difficulty'] ?? '') ?></td>
                                        <td><?= esc($rec['timeframe'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Wins Section -->
            <?php if (isset($report['quick_wins']) && is_array($report['quick_wins']) && count($report['quick_wins']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Quick Wins</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <?php foreach ($report['quick_wins'] as $win): ?>
                                <li><?= esc($win) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Content Ideas Section -->
            <?php if (isset($report['content_ideas']) && is_array($report['content_ideas']) && count($report['content_ideas']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Content Ideas</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <?php foreach ($report['content_ideas'] as $idea): ?>
                                <li><?= esc($idea) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Technical SEO TODOs Section -->
            <?php if (isset($report['technical_seo_todos']) && is_array($report['technical_seo_todos']) && count($report['technical_seo_todos']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Technical SEO Tasks</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <?php foreach ($report['technical_seo_todos'] as $todo): ?>
                                <li><?= esc($todo) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
