<?php
ob_start();
?><div class="admin-container">
    <h1>Create New Custom Field</h1>
    
    <form method="POST" action="/admin/custom-fields/store">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label for="name">Field Name (machine-readable)</label>
            <input type="text" id="name" name="name" class="form-control" required pattern="[a-z0-9_]+" title="Lowercase letters, numbers and underscores only">
        </div>
        
        <div class="form-group">
            <label for="label">Field Label (human-readable)</label>
            <input type="text" id="label" name="label" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="type">Field Type</label>
            <select id="type" name="type" class="form-control" required>
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="textarea">Textarea</option>
                <option value="select">Select</option>
                <option value="checkbox">Checkbox</option>
                <option value="radio">Radio</option>
                <option value="date">Date</option>
            </select>
        </div>
        
        <div class="form-group" id="options-container">
            <label>Field Options (JSON)</label>
            <div id="jsoneditor"></div>
            <input type="hidden" name="options" id="options">
        </div>
        
        <button type="submit" class="btn btn-primary">Create Field</button>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../views/includes/layout.php';
render_layout('Create Custom Field', $content, true);