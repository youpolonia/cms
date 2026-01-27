<?php
/**
 * Content Type Fields List View
 */
$fields = ContentTypesFactory::getFieldsInstance()->getFieldsForType($typeId);

?><div class="content-fields">
  <h3>Fields Management</h3>
  
  <div class="field-actions">
    <a href="?action=add_field&type_id=<?= $typeId ?>" class="btn">Add Field</a>
  </div>

  <table class="field-list">
    <thead>
      <tr>
        <th>Label</th>
        <th>Machine Name</th>
        <th>Type</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($fields as $field): ?>
      <tr>
        <td><?= htmlspecialchars($field['label']) ?></td>
        <td><?= htmlspecialchars($field['machine_name']) ?></td>
        <td><?= htmlspecialchars($field['type']) ?></td>
        <td>
          <a href="?action=edit_field&type_id=<?= $typeId ?>&field_id=<?= $field['id'] ?>">Edit</a>
          <a href="?action=delete_field&type_id=<?= $typeId ?>&field_id=<?= $field['id'] ?>" 
             onclick="
return confirm('Delete this field?')">Delete</a>
        </td>
      </tr>
      <?php endforeach;  ?>
    </tbody>
  </table>
</div>
