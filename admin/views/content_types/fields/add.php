<?php
/**
 * Add Field Form View
 */
require_once __DIR__ . '/../../../core/csrf.php';
$fieldTypes = ContentTypesFactory::getFieldsInstance()->getFieldTypeOptions();

?><div class="content-field-form">
  <h3>Add New Field</h3>
  
  <form method="post" action="?action=save_field&type_id=<?= $typeId ?>">
    <?= csrf_field();  ?>
    <div class="form-group">
      <label for="field_label">Label:</label>
      <input type="text" id="field_label" name="field[label]"
 required>
?>    </div>

    <div class="form-group">
      <label for="field_machine_name">Machine Name:</label>
      <input type="text" id="field_machine_name" name="field[machine_name]" 
             pattern="[a-z0-9_]+" title="Lowercase letters, numbers and underscores only"
 required>
?>    </div>

    <div class="form-group">
      <label for="field_type">Field Type:</label>
      <select id="field_type" name="field[type]"
 required>
        <?php foreach ($fieldTypes as $type => $label): ?>        <option value="<?= $type ?>"><?= $label ?></option>
        <?php endforeach;  ?>
      </select>
    </div>

    <div class="form-group">
      <label for="field_description">Description:</label>
      <textarea id="field_description" name="field[description]"></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn">Save Field</button>
      <a href="?action=list_fields&type_id=<?= $typeId ?>" class="btn">Cancel</a>
    </div>
  </form>
</div>
