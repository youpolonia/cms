<?php require_once __DIR__ . '/../includes/admin/nav.php'; 
?><div class="admin-container">
    <h1>Personalization Rules</h1>
    
    <div class="rule-editor-container">
        <div class="rule-list">
            <button id="new-rule-btn" class="btn btn-primary">New Rule</button>
            <ul id="rule-list">
                <?php foreach ($rules as $rule): ?>
                    <li data-id="<?= $rule['id'] ?>">
                        <span class="rule-name"><?= htmlspecialchars($rule['name']) ?></span>
                        <span class="rule-status"><?= $rule['is_active'] ? 'Active' : 'Inactive' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="rule-editor">
            <form id="rule-form">
                <input type="hidden" id="rule-id" name="id" value="">
                
                <div class="form-group">
                    <label for="rule-name">Rule Name</label>
                    <input type="text" id="rule-name" name="name" class="form-control"
 required>
?>                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="rule-active" name="is_active" value="1"> Active
                    </label>
                </div>
                
                <h3>Conditions</h3>
                <div id="conditions-container"></div>
                <button type="button" id="add-condition" class="btn btn-secondary">Add Condition</button>
                
                <h3>Actions</h3>
                <div id="actions-container"></div>
                <button type="button" id="add-action" class="btn btn-secondary">Add Action</button>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Rule</button>
                    <button type="button" id="test-rule" class="btn btn-info">Test Rule</button>
                    <button type="button" id="delete-rule" class="btn btn-danger">Delete Rule</button>
                </div>
            </form>
            
            <div class="test-panel">
                <h3>Test Data</h3>
                <textarea id="test-data" class="form-control" placeholder="Enter JSON test data..."></textarea>
                <div id="test-result"></div>
            </div>
        </div>
    </div>
</div>

<script src="/js/admin/personalization-rules.js"></script>
