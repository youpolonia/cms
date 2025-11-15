<?php require_once __DIR__ . '/../../includes/admin-header.php'; ?>
<div class="admin-container">
    <h1>Assign Custom Fields to Content Types</h1>
    
    <form method="POST" action="/admin/custom-fields/save-assignments">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label for="content_type_id">Content Type</label>
            <select id="content_type_id" name="content_type_id" class="form-control" required>
                <option value="">Select Content Type</option>
                <?php foreach ($contentTypes as $type): ?>
                    <option value="<?= $type['id'] ?>"
                        <?= isset($_GET['content_type_id']) && $_GET['content_type_id'] == $type['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if (!empty($_GET['content_type_id'])): ?>
            <div class="form-group">
                <label>Available Fields</label>
                <?php 
                $assignedFields = [];
                if (isset($_GET['content_type_id'])) {
                    $result = $this->db->query("SELECT field_id FROM content_type_fields WHERE content_type_id = ?",
                        [$_GET['content_type_id']]);
                    $assignedFields = array_column($result, 'field_id');
                }
                ?>
                <div class="field-checkboxes">
                    <?php foreach ($fields as $field): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" 
                                   id="field_<?= $field['id'] ?>" 
                                   name="field_ids[]" 
                                   value="<?= $field['id'] ?>"
                                   <?= in_array($field['id'], $assignedFields) ? 'checked' : '' ?>>
                            <label for="field_<?= $field['id'] ?>">
                                <?= htmlspecialchars($field['name']) ?> (<?= htmlspecialchars($field['type']) ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Assignments</button>
        <?php endif; ?>
    </form>
</div>

<script>
document.getElementById('content_type_id').addEventListener('change', function() {
    if (this.value) {
        window.location.href = '/admin/custom-fields/assign?content_type_id=' + this.value;
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php';
