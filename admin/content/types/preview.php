<?php
// Verify admin access
require_once __DIR__ . '/../../security/admin-check.php';

// Get content type ID
$content_type_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$content_type_id) {
    header("Location: index.php");
    exit;
}

// Get database instance
require_once __DIR__ . '/../../core/database.php';
$contentTypes = ContentTypesFactory::getTypesInstance($db);
$contentFields = new ContentFields($db);

// Get content type and fields
$content_type = $contentTypes->getById($content_type_id);
if (!$content_type) {
    header("Location: index.php");
    exit;
}

$fields = $contentFields->getByContentType($content_type_id);

$title = "Preview: {$content_type['name']}";
ob_start();

?><h1>Preview: <?= htmlspecialchars($content_type['name']) ?></h1>
<div class="preview-container">
    <div class="preview-form">
        <?php foreach ($fields as $field): ?>
        <div class="form-group">
            <label><?= htmlspecialchars($field['name']) ?></label>
            <?php 
                switch($field['field_type']):
                case 'text': ?>
                    <input type="text" disabled>
                <?php break;
                case 'textarea': ?>
                    <textarea disabled></textarea>
                <?php break;
                case 'number': ?>
                    <input type="number" disabled>
                <?php break;
                case 'email': ?>
                    <input type="email" disabled>
                <?php break;
                case 'date': ?>
                    <input type="date" disabled>
                <?php break;
                case 'boolean': ?>
                    <input type="checkbox" disabled>
                <?php break;
                case 'select': ?>
                    <select disabled>
                        <?php foreach ($field['settings']['options'] as $option): ?>
                        <option><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php break;
                case 'file': ?>
                    <input type="file" disabled>
                <?php break;
            endswitch; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="preview-actions">
    <a href="edit.php?id=<?= $content_type_id ?>" class="button">Edit Content Type</a>
    <a href="index.php" class="button">Back to List</a>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../admin/layout.php';
