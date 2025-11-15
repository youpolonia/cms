<?php
require_once __DIR__ . '/../../includes/auth/jwt.php';
require_once __DIR__ . '/../../services/WorkflowAutomation.php';
require_once __DIR__ . '/../../services/notificationservice.php';
require_once __DIR__ . '/../../core/csrf.php';

$jwt = new JWT();
if (!$jwt->validateToken()) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

require_once __DIR__ . '/../../app/Services/AI/AIService.php';

$notificationService = new NotificationService();
$aiService = new AIService();
$workflowAutomation = new WorkflowAutomation($notificationService, $aiService);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_rule':
            $ruleData = [
                'name' => $_POST['name'],
                'trigger_type' => $_POST['trigger_type'],
                'trigger_condition' => $_POST['trigger_condition'],
                'actions' => json_decode($_POST['actions'], true)
            ];
            // TODO: Save rule to database (will pass to db-support)
            break;
            
        case 'update_rule':
            // Similar to create_rule
            break;
            
        case 'delete_rule':
            // Handle deletion
            break;
    }
}

// Get existing rules (will fetch from database via db-support)
$rules = []; // Placeholder for now

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Workflow Automation</title>
    <link rel="stylesheet" href="/admin/assets/css/main.css">
    <script src="/admin/assets/js/workflow-automation.js"></script>
</head>
<body>
    <div class="container">
        <h1>Workflow Automation Rules</h1>
        
        <div class="rule-list">
            <?php foreach ($rules as $rule): ?>
                <div class="rule-card">
                    <h3><?php echo htmlspecialchars($rule['name']); ?></h3>
                    <p>Trigger: <?php echo htmlspecialchars($rule['trigger_type']); ?></p>
                    <div class="actions">
                        <button class="edit-btn" data-rule-id="<?php echo $rule['id']; ?>">Edit</button>
                        <button class="delete-btn" data-rule-id="<?php echo $rule['id']; ?>">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button id="add-rule-btn" class="primary-btn">Add New Rule</button>
        
        <div id="rule-form-modal" class="modal" style="display:none;">
            <form id="rule-form" method="POST">
                <input type="hidden" name="action" value="create_rule">
                
                <div class="form-group">
                    <label for="name">Rule Name</label>
                    <input type="text" id="name" name="name"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="trigger_type">Trigger Type</label>
                    <select id="trigger_type" name="trigger_type"
                            required>
                        <option value="database">Database Change</option>
                        <option value="time">Time-based</option>
                        <option value="manual">Manual</option>
                        <option value="ai_content_generation">AI Content Generation</option>
                        <option value="ai_classification">AI Classification</option>
                        <option value="ai_moderation">AI Moderation</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="trigger_condition">Trigger Condition</label>
                    <textarea id="trigger_condition" name="trigger_condition"
                              required></textarea>
                    
                    <div id="ai-config" style="display:none;">
                        <div class="form-group">
                            <label for="ai_provider">AI Provider</label>
                            <select id="ai_provider" name="ai_provider">
                                <?php foreach ($aiService->getProviders() as $provider): ?>
                                    <option value="<?php echo $provider; ?>"><?php echo $provider; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ai_prompt">Prompt/Input</label>
                            <textarea id="ai_prompt" name="ai_prompt"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Actions</label>
                    <div id="actions-container">
                        <!-- Actions will be added here via JS -->
                    </div>
                    <button type="button" id="add-action-btn">Add Action</button>
                </div>
                
                <button type="submit" class="primary-btn">Save Rule</button>
            </form>
        </div>
    </div>

    <script src="/admin/assets/js/workflow-automation.js"></script>
</body>
</html>