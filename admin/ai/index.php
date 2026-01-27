<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';

cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/n8n_client.php';
require_once CMS_ROOT . '/core/n8n_events.php';
require_once CMS_ROOT . '/core/sites_context.php';

$currentSite = sites_bootstrap_current_site();

$hfConfig = ai_hf_config_load();
$hfConfigured = ai_hf_is_configured($hfConfig);
$hfStatusLabel = $hfConfigured ? 'Configured' : 'Not configured';
$hfOk = $hfConfigured;

$n8nConfig = n8n_config_load();
$n8nConfigured = n8n_is_configured($n8nConfig);
$n8nStatusLabel = $n8nConfigured ? 'Configured' : 'Not configured';
$n8nWorkflowCount = null;
if ($n8nConfigured) {
    try {
        $result = n8n_list_workflows(20);
        if (isset($result['ok']) && $result['ok'] === true && isset($result['workflows']) && is_array($result['workflows'])) {
            $n8nWorkflowCount = count($result['workflows']);
        }
    } catch (Exception $e) {
    }
}
$n8nOk = $n8nConfigured;

$bindingsData = n8n_bindings_load();
$totalBindings = 0;
$activeBindings = 0;
if (is_array($bindingsData) && isset($bindingsData['bindings']) && is_array($bindingsData['bindings'])) {
    $totalBindings = count($bindingsData['bindings']);
    foreach ($bindingsData['bindings'] as $binding) {
        if (isset($binding['active']) && $binding['active'] === true) {
            $activeBindings++;
        }
    }
}
$bindingsOk = ($totalBindings > 0);

$aiEmailSequences = 0;
$aiEmailConfigPath = CMS_ROOT . '/config/ai_email_automation.json';
if (is_file($aiEmailConfigPath) && is_readable($aiEmailConfigPath)) {
    $aiEmailJson = @file_get_contents($aiEmailConfigPath);
    if ($aiEmailJson !== false) {
        $aiEmailData = @json_decode($aiEmailJson, true);
        if (is_array($aiEmailData) && isset($aiEmailData['sequences']) && is_array($aiEmailData['sequences'])) {
            $aiEmailSequences = count($aiEmailData['sequences']);
        }
    }
}
$aiEmailOk = ($aiEmailSequences > 0);

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>
<main class="container">
  <h1>AI &amp; Automation Center</h1>
  <p style="margin-bottom: 2rem; color: #6c757d;">
    This is the central dashboard for all AI and automation tools in the CMS. All tools require DEV_MODE and admin access.
  </p>

  <div class="dashboard-section">
    <h2>Current Site</h2>
    <div class="card">
      <div style="padding: 1rem;">
        <?php if ($currentSite !== null && is_array($currentSite)): ?>
          <div style="margin-bottom: 0.5rem;">
            <strong>Site:</strong> <?= esc($currentSite['id'] ?? '') ?> &mdash; <?= esc($currentSite['name'] ?? '') ?>
          </div>
          <div style="color: #6c757d; font-size: 0.9rem;">
            <strong>Domain:</strong> <?php
              $domain = $currentSite['domain'] ?? '';
              echo $domain === '' || $domain === '*' ? '* (catch-all)' : esc($domain);
            ?> |
            <strong>Locale:</strong> <?php
              $locale = $currentSite['locale'] ?? '';
              echo $locale === '' ? 'n/a' : esc($locale);
            ?>
          </div>
        <?php else: ?>
          <p style="margin: 0; color: #6c757d;">Site: (none resolved / single-site mode)</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="dashboard-section">
    <h2>System Status</h2>
    <div class="card">
      <div style="padding: 1rem;">
        <table style="width: 100%; border-collapse: collapse;">
          <tbody>
            <tr style="border-bottom: 1px solid #e0e0e0;">
              <td style="padding: 0.75rem 0; font-weight: bold;">Hugging Face AI</td>
              <td style="padding: 0.75rem 0; text-align: right;">
                <span style="color: <?= $hfOk ? '#28a745' : '#6c757d' ?>;"><?= esc($hfStatusLabel) ?></span>
              </td>
            </tr>
            <tr style="border-bottom: 1px solid #e0e0e0;">
              <td style="padding: 0.75rem 0; font-weight: bold;">n8n</td>
              <td style="padding: 0.75rem 0; text-align: right;">
                <?php if ($n8nOk && $n8nWorkflowCount !== null): ?>
                  <span style="color: #28a745;">Configured (<?= esc($n8nWorkflowCount) ?> workflows)</span>
                <?php elseif ($n8nOk): ?>
                  <span style="color: #28a745;">Configured (workflows unknown)</span>
                <?php else: ?>
                  <span style="color: #6c757d;">Not configured</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr style="border-bottom: 1px solid #e0e0e0;">
              <td style="padding: 0.75rem 0; font-weight: bold;">n8n Bindings</td>
              <td style="padding: 0.75rem 0; text-align: right;">
                <?php if ($totalBindings === 0): ?>
                  <span style="color: #6c757d;">None defined</span>
                <?php else: ?>
                  <span style="color: <?= $bindingsOk ? '#28a745' : '#6c757d' ?>;"><?= esc($totalBindings) ?> total (<?= esc($activeBindings) ?> active)</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <td style="padding: 0.75rem 0; font-weight: bold;">AI Email Sequences</td>
              <td style="padding: 0.75rem 0; text-align: right;">
                <?php if ($aiEmailSequences === 0): ?>
                  <span style="color: #6c757d;">None defined</span>
                <?php else: ?>
                  <span style="color: <?= $aiEmailOk ? '#28a745' : '#6c757d' ?>;"><?= esc($aiEmailSequences) ?> sequences</span>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="dashboard-section" style="margin-top: 2rem;">
    <h2>AI Tools</h2>
    <p style="margin-bottom: 1rem; color: #6c757d;">Available AI-powered content generation and analysis tools</p>
    <div class="card">
      <div style="padding: 1rem;">
        <ul style="list-style: none; padding: 0; margin: 0;">
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/landing.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Landing Pages</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Generate landing page copy for marketing pages</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/forms.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Forms</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Suggest form structures and fields</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/content-insights.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Content Insights</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Analyze URLs or content for SEO/quality issues</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/email-builder.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Email Builder</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Generate email subject, preview, HTML and text</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/component-builder.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Component Builder</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Create reusable page components (hero, pricing, FAQs, etc.)</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/translation.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Translation</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Translate content between supported languages</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/optimizer.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Website Optimizer</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Analyze pages for SEO, UX, performance and conversion</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/email-automation.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Email Automation</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Configure multi-step email sequences for events</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/email-automation-preview.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Email Preview</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Preview scheduled emails and bodies for a sequence</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/security-insights.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Security Insights</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Analyze security headers and basic performance metrics for any URL, optionally with AI-generated explanations and recommendations.</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/ai/log-explainer.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Log Explainer</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Summarize and explain log excerpts</p>
          </li>
          <li style="padding: 0.75rem 0;">
            <a href="/admin/ai/dev-assistant.php" style="font-weight: bold; color: #007bff; text-decoration: none;">AI Developer Assistant</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Help analyze code snippets and suggest patches</p>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="dashboard-section" style="margin-top: 2rem;">
    <h2>Automation Tools</h2>
    <p style="margin-bottom: 1rem; color: #6c757d;">n8n workflow automation and integration tools</p>
    <div class="card">
      <div style="padding: 1rem;">
        <ul style="list-style: none; padding: 0; margin: 0;">
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/n8n-settings.php" style="font-weight: bold; color: #007bff; text-decoration: none;">n8n Settings</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Configure n8n connection and API credentials</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/n8n-workflows.php" style="font-weight: bold; color: #007bff; text-decoration: none;">n8n Workflows</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Browse and manage n8n workflows</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/n8n-workflow-bindings.php" style="font-weight: bold; color: #007bff; text-decoration: none;">n8n Bindings</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Map CMS events to n8n workflow triggers</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/n8n-workflow-builder.php" style="font-weight: bold; color: #007bff; text-decoration: none;">n8n Workflow Builder</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Create and edit workflow definitions</p>
          </li>
          <li style="padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0;">
            <a href="/admin/n8n-event-tester.php" style="font-weight: bold; color: #007bff; text-decoration: none;">n8n Event Tester</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Test event firing and workflow execution</p>
          </li>
          <li style="padding: 0.75rem 0;">
            <a href="/admin/tools/deploy-export.php" style="font-weight: bold; color: #007bff; text-decoration: none;">One-Click Deploy</a>
            <p style="margin: 0.25rem 0 0 0; color: #6c757d; font-size: 0.9rem;">Export CMS configuration for deployment</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</main>
<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
