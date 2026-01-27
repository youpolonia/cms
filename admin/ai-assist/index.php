<?php
require_once __DIR__.'/../../includes/admin_header.php';
require_once __DIR__ . '/../../admin/seo/seosuggestionengine.php';
require_once __DIR__.'/../../includes/core/aifeedbacklogger.php';
require_once __DIR__.'/../../core/csrf.php';

csrf_boot();


// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
$providers = [
    'openai' => 'OpenAI',
    'huggingface' => 'Hugging Face'
];

$selectedProvider = $_POST['provider'] ?? 'openai';
$action = $_POST['action'] ?? 'generate';
$prompt = $_POST['prompt'] ?? '';
$params = $_POST['params'] ?? '{}';
$result = null;
$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    if ($action === 'seo_analyze') {
        if (!class_exists('SEOSuggestionEngine')) {
            http_response_code(503);
            error_log('SEOSuggestionEngine missing');
            exit;
        }
        $result = SEOSuggestionEngine::analyzeContent($prompt, $selectedProvider, $sessionId, $userId);
    } else {
        $url = '/api/ai/index.php?provider='.urlencode($selectedProvider).'&action='.urlencode($action);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'prompt' => $prompt,
            'params' => $params
        ]);
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);
    }
}

?><div class="admin-content">
    <h1>AI Assist</h1>
    
    <form method="post" class="ai-form">
        <?= csrf_field(); ?>
<div class="form-group">
            <label>Provider:</label>
            <select name="provider">
                <?php foreach ($providers as $value => $label): ?>                    <option value="<?= htmlspecialchars($value) ?>" <?= $selectedProvider === $value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

<div class="form-group">
            <label>Action:</label>
            <select name="action">
                <option value="generate" <?= $action === 'generate' ? 'selected' : '' ?>>Generate Content</option>
                <option value="validate" <?= $action === 'validate' ? 'selected' : '' ?>>Validate Content</option>
                <option value="seo_analyze" <?= $action === 'seo_analyze' ? 'selected' : '' ?>>SEO Analysis</option>
                <option value="models" <?= $action === 'models' ? 'selected' : '' ?>>List Models</option>
            </select>
        </div>

        <div class="form-group">
            <label>Prompt:</label>
            <textarea name="prompt" rows="5"><?= htmlspecialchars($prompt) ?></textarea>
        </div>

        <div class="form-group">
            <label>Parameters (JSON):</label>
            <textarea name="params" rows="3"><?= htmlspecialchars($params) ?></textarea>
        </div>

        <button type="submit">Submit</button>
    </form>

    <?php if ($result !== null): ?>
<div class="result-container">
            <h2>Result</h2>
            <?php if ($action === 'seo_analyze'): ?>
<div class="comparison-container">
                    <div class="original-content">
                        <h3>Original Content</h3>
                        <div class="content-box"><?= htmlspecialchars($prompt) ?></div>
                    </div>
                    <div class="suggested-content">
                        <h3>SEO Suggestions</h3>
                        <div class="content-box">
                            <h4>Meta Title</h4>
                            <p><?= htmlspecialchars($result['meta_title']) ?></p>
                            <h4>Meta Description</h4>
                            <p><?= htmlspecialchars($result['meta_description']) ?></p>
                            <h4>Keywords</h4>
                            <p><?= htmlspecialchars(implode(', ', $result['keywords'])) ?></p>
                            <h4>Score</h4>
                            <p><?= htmlspecialchars($result['score']) ?>/10</p>
                            <h4>Suggestions</h4>
                            <ul>
                                <?php foreach ($result['suggestions'] as $suggestion): ?>
<li><?= htmlspecialchars($suggestion) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
<div class="action-buttons">
                    <form method="post" action="/admin/ai-assist/log_action.php">
                        <?= csrf_field(); ?>
<input type="hidden" name="type" value="seo_approve">
                        <input type="hidden" name="data" value="<?= htmlspecialchars(json_encode($result)) ?>">
                        <button type="submit" class="btn-approve">Approve</button>
                    </form>
                    <form method="post" action="/admin/ai-assist/log_action.php">
                        <?= csrf_field(); ?>
<input type="hidden" name="type" value="seo_ignore">
                        <input type="hidden" name="data" value="<?= htmlspecialchars(json_encode($result)) ?>">
                        <button type="submit" class="btn-ignore">Ignore</button>
                    </form>
                </div>
            <?php else: ?>
                <pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__.'/../../includes/admin_footer.php';
