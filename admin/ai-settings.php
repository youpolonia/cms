<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/aiintegrationcontroller.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin permissions
if (!hasPermission('manage_ai_settings')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

csrf_boot('admin');

// Load config and initialize
$config = require_once __DIR__ . '/../config/ai.php';
AIIntegrationController::init();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_settings':
            // Validate and save settings
            $newConfig = [
                'default_provider' => $_POST['default_provider'] ?? 'openai',
                'providers' => [
                    'openai' => [
                        'api_key' => trim($_POST['openai_api_key'] ?? ''),
                        'enabled' => isset($_POST['openai_enabled'])
                    ],
                    'huggingface' => [
                        'api_key' => trim($_POST['huggingface_api_key'] ?? ''),
                        'enabled' => isset($_POST['huggingface_enabled'])
                    ]
                ]
            ];
            
            // Save to config file
            file_put_contents(
                __DIR__ . '/../config/ai.php',
                '<?php return ' . var_export($newConfig, true) . ';'
            );
            
            $message = 'Settings saved successfully';
            $config = $newConfig;
            break;
            
        case 'test_connection':
            $provider = $_POST['provider'] ?? 'openai';
            try {
                $params = AIIntegrationController::getTemplateAIParameters('connection_test');
                $message = "Connection successful";
            } catch (Exception $e) {
                $message = "Connection failed: " . htmlspecialchars($e->getMessage());
            }
            break;
            
        case 'generate_test_content':
            $template = $_POST['template'] ?? 'blog_post';
            $variables = json_decode($_POST['variables'] ?? '[]', true) ?: [];
            $provider = $_POST['provider'] ?? null;
            
            try {
                $result = AIIntegrationController::renderTemplate($template, $variables);
                $message = "Generation successful";
                $generatedContent = $result;
            } catch (Exception $e) {
                $message = "Generation failed: " . htmlspecialchars($e->getMessage());
            }
            break;
    }
}

// Get available templates
$templates = array_keys(AIIntegrationController::getTemplates());
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Settings</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <script src="/admin/js/ai-integration.js" defer></script>
</head>
<body>
    <h1>AI Integration Settings</h1>
    
    <?php if (!empty($message)): ?>
        <div class="notice"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field(); 
?>        <input type="hidden" name="action" value="save_settings">
        
        <h2>General Settings</h2>
        <div class="form-group">
            <label>Default Provider:</label>
            <select name="default_provider">
                <option value="openai" <?= $config['default_provider'] === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                <option value="huggingface" <?= $config['default_provider'] === 'huggingface' ? 'selected' : '' ?>>Hugging Face</option>
            </select>
        </div>
        
        <h2>Provider Settings</h2>
        
        <div class="provider-settings">
            <h3>OpenAI</h3>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="openai_enabled" <?= $config['providers']['openai']['enabled'] ? 'checked' : '' ?>>
                    Enable OpenAI
                </label>
            </div>
            <div class="form-group">
                <label>API Key:</label>
                <input type="password" name="openai_api_key" value="<?= htmlspecialchars($config['providers']['openai']['api_key']) ?>">
            </div>
        </div>
        
        <div class="provider-settings">
            <h3>Hugging Face</h3>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="huggingface_enabled" <?= $config['providers']['huggingface']['enabled'] ? 'checked' : '' ?>>
                    Enable Hugging Face
                </label>
            </div>
            <div class="form-group">
                <label>API Key:</label>
                <input type="password" name="huggingface_api_key" value="<?= htmlspecialchars($config['providers']['huggingface']['api_key']) ?>">
            </div>
        </div>
        
        <button type="submit">Save Settings</button>
    </form>
    
    <h2>Test Connection</h2>
    <form method="post" class="test-form">
        <?= csrf_field(); 
?>        <input type="hidden" name="action" value="test_connection">
        <div class="form-group">
            <label>Provider:</label>
            <select name="provider">
                <option value="openai">OpenAI</option>
                <option value="huggingface">Hugging Face</option>
            </select>
        </div>
        <button type="submit">Test Connection</button>
    </form>
    
    <h2>Test Content Generation</h2>
    <form method="post" class="test-form" id="content-generation-form">
        <?= csrf_field(); 
?>        <input type="hidden" name="action" value="generate_test_content">
        <div class="form-group">
            <label>Template:</label>
            <select name="template" id="template-select">
                <?php foreach ($templates as $template): ?>                    <option value="<?= htmlspecialchars($template) ?>"><?= htmlspecialchars($template) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Provider (optional):</label>
            <select name="provider">
                <option value="">Use Default</option>
                <option value="openai">OpenAI</option>
                <option value="huggingface">Hugging Face</option>
            </select>
        </div>
        <div id="template-variables">
            <!-- Variables will be populated by JavaScript -->
        </div>
        <button type="submit">Generate Content</button>
    </form>
    
    <?php if (!empty($generatedContent)): ?>
        <div class="generated-content">
            <h3>Generated Content</h3>
            <pre><?= htmlspecialchars($generatedContent) ?></pre>
        </div>
    <?php endif; ?>
</body>
</html>
