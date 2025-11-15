<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
// AI Toolkit Panel - Admin Interface
require_once __DIR__.'/includes/admin_auth.php';

$pageTitle = "AI Toolkit";
require_once __DIR__ . '/includes/admin_header.php';

// Available AI providers from config
$providers = [
    'openai' => 'OpenAI',
    'huggingface' => 'HuggingFace'
];

?><div class="admin-container">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <div class="ai-toolkit-grid">
        <!-- Content Generation Panel -->
        <div class="ai-panel">
            <h2>Content Generation</h2>
            <form id="generateForm" class="ai-form">
                <div class="form-group">
                    <label for="aiProvider">AI Provider:</label>
                    <select id="aiProvider" name="provider" class="form-control">
                        <?php foreach ($providers as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prompt">Prompt:</label>
                    <textarea id="prompt" name="prompt" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="actionType">Action:</label>
                    <select id="actionType" name="action" class="form-control">
                        <option value="generate">Generate New Content</option>
                        <option value="rewrite">Rewrite Content</option>
                        <option value="summarize">Summarize Content</option>
                        <option value="translate">Translate Content</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Process</button>
            </form>
            
            <div id="resultContainer" class="result-area" style="display:none;">
                <h3>Result:</h3>
                <div id="aiResult" class="result-content"></div>
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById('generateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultContainer = document.getElementById('resultContainer');
    const resultContent = document.getElementById('aiResult');
    
    resultContainer.style.display = 'none';
    resultContent.innerHTML = 'Processing...';
    
    // Determine endpoint based on action
    let endpoint = '/api/ai/';
    switch(formData.get('action')) {
        case 'rewrite':
            endpoint += 'suggest-content.php';
            break;
        case 'summarize':
        case 'translate':
            endpoint += 'content.php?action=' + formData.get('action');
            break;
        default:
            endpoint += 'generate.php';
    }
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            resultContent.innerHTML = `Error: ${data.error}`;
        } else {
            resultContent.innerHTML = data.result || data.content || 'No content returned';
        }
        resultContainer.style.display = 'block';
    })
    .catch(error => {
        resultContent.innerHTML = `Request failed: ${error}`;
        resultContainer.style.display = 'block';
    });
});
</script>


<?php require_once __DIR__ . '/includes/admin_footer.php';
