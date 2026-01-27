<?php
/**
 * AI Configuration Panel
 * 
 * Handles API key management and AI service configuration
 */

// Verify admin access
require_once __DIR__ . '/security/admin-check.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot();


require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
// Load core configuration
require_once __DIR__ . '/../config/services.php';

$pageTitle = "AI Configuration";
require_once __DIR__ . '/security/admin-header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Validate and save configuration
    $config = [
        'ai_provider' => $_POST['provider'] ?? 'openai',
        'api_key' => trim($_POST['api_key'] ?? ''),
        'enabled_features' => $_POST['features'] ?? [],
        'content_length' => (int)($_POST['content_length'] ?? 500)
    ];

    // Save to config file
    file_put_contents(__DIR__ . '/../config/ai-config.json', json_encode($config));
    
    echo '<div class="alert alert-success">Configuration saved successfully</div>';
}

// Load current config
$configFile = __DIR__ . '/../config/ai-config.json';
$currentConfig = file_exists($configFile) 
    ? json_decode(file_get_contents($configFile), true) 
    : [
        'ai_provider' => 'openai',
        'api_key' => '',
        'enabled_features' => [],
        'content_length' => 500
    ];

?><div class="admin-container">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <form method="post" class="ai-config-form">
        <?= csrf_field(); ?> 
        <div class="form-group">
            <label for="provider">AI Provider:</label>
            <select id="provider" name="provider" class="form-control">
                <option value="openai" <?= $currentConfig['ai_provider'] === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                <option value="huggingface" <?= $currentConfig['ai_provider'] === 'huggingface' ? 'selected' : '' ?>>Hugging Face</option>
            </select>
        </div>

        <div class="form-group">
            <label for="api_key">API Key:</label>
            <input type="password" id="api_key" name="api_key" 
                   value="<?= htmlspecialchars($currentConfig['api_key']) ?>" 
                   class="form-control" placeholder="Enter your API key">        </div>

        <div class="form-group">
            <label>Enabled Features:</label>
            <div class="checkbox-group">
                <label><input type="checkbox" name="features[]" value="content_suggestions" 
                    <?= in_array('content_suggestions', $currentConfig['enabled_features']) ? 'checked' : '' ?>> Content Suggestions</label>
                <label><input type="checkbox" name="features[]" value="ai_editing" 
                    <?= in_array('ai_editing', $currentConfig['enabled_features']) ? 'checked' : '' ?>> AI-Assisted Editing</label>
                <label><input type="checkbox" name="features[]" value="auto_tagging" 
                    <?= in_array('auto_tagging', $currentConfig['enabled_features']) ? 'checked' : '' ?>> Auto Tagging</label>
            </div>
        </div>

        <div class="form-group">
            <label for="content_length">Max Content Length (chars):</label>
            <input type="number" id="content_length" name="content_length" 
                   value="<?= $currentConfig['content_length'] ?>" 
                   min="100" max="2000" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Save Configuration</button>
    </form>
</div>

<?php require_once __DIR__ . '/security/admin-footer.php';
