<?php
/**
 * Widget Editor
 */
require_once __DIR__ . '/../../includes/admin_auth.php';

$widgetId = $_GET['id'] ?? null;
$widget = WidgetSettingsController::getWidgetById($widgetId);

?><div class="widget-editor">
    <h1>Edit Widget: <?= htmlspecialchars($widget['name']) ?></h1>
    <form id="widget-form" method="post" action="/admin/api/widgets/update">
        <input type="hidden" name="id" value="<?= $widgetId ?>">
        <div class="form-group">
            <label for="widget-name">Name</label>
            <input type="text" id="widget-name" name="name" value="<?= htmlspecialchars($widget['name']) ?>"
 required>
?>        </div>
        
        <div class="form-group">
            <label for="widget-type">Type</label>
            <select id="widget-type" name="type"
 required>
                <?php foreach (WidgetSettingsController::getWidgetTypes() as $type): ?>                    <option value="<?= $type ?>" <?= $type === $widget['type'] ? 'selected' : '' ?>>
                        <?= ucfirst($type)  ?>
                    </option>
                <?php endforeach;  ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="widget-settings">Settings (JSON)</label>
            <textarea id="widget-settings" name="settings" rows="10"><?= json_encode($widget['settings'], JSON_PRETTY_PRINT) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-save">Save Changes</button>
            <button type="button" id="preview-btn" class="btn btn-preview" 
                    data-widget-type="<?= htmlspecialchars($widget['type']) ?>">
                Preview Widget
            </button>
            <a href="/admin/widgets" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
    
    <div id="preview-container" class="widget-preview-container hidden">
        <h3>Widget Preview</h3>
        <div class="preview-content"></div>
    </div>
</div>

<script>
document.getElementById('preview-btn').addEventListener('click', function() {
    const previewContainer = document.getElementById('preview-container');
    const previewContent = previewContainer.querySelector('.preview-content');
    const widgetType = this.dataset.widgetType;
    
    // Show loading state
    previewContent.innerHTML = '
<p>Loading preview...</p>';
    previewContainer.classList.remove('hidden');
    
    // Get form data
    const formData = new FormData(document.getElementById('widget-form'));
    
    // Call preview endpoint
    fetch('/admin/api/widgets/preview', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        previewContent.innerHTML = html;
    })
    .catch(error => {
        previewContent.innerHTML = `
<p class="error">Preview failed: ${error.message}</p>`;
    });
});
</script>
