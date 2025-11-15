<?php
// Verify admin access
require_once __DIR__ . '/../../../security/admin-check.php';
require_once __DIR__ . '/../../../../core/csrf.php';

// Get content type ID
$content_type_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$content_type_id) {
    header("Location: ../index.php");
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;

// Get database instance
require_once __DIR__ . '/../../../../core/database.php';
$db = [];
$contentTypes = ContentTypesFactory::getTypesInstance($db);
$contentFields = new ContentFields($db);

// Get content type info
$content_type = $contentTypes->getById($content_type_id);
if (!$content_type) {
    header("Location: ../index.php");
    exit;
}

// Field types configuration
$field_types = [
    'text' => 'Text',
    'textarea' => 'Text Area',
    'number' => 'Number',
    'email' => 'Email',
    'date' => 'Date',
    'boolean' => 'Boolean',
    'select' => 'Select List',
    'file' => 'File Upload'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token";
    }

    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $machine_name = trim($_POST['machine_name'] ?? '');
    $field_type = $_POST['field_type'] ?? '';
    $is_required = isset($_POST['is_required']);
    $weight = (int)($_POST['weight'] ?? 0);

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($machine_name)) {
        $errors[] = "Machine name is required";
    } elseif (!preg_match('/^[a-z0-9_]+$/', $machine_name)) {
        $errors[] = "Machine name can only contain lowercase letters, numbers and underscores";
    }

    if (!array_key_exists($field_type, $field_types)) {
        $errors[] = "Invalid field type";
    }

    // If no errors, create field
    if (empty($errors)) {
        $settings = [];
        // Handle field type specific settings
        switch ($field_type) {
            case 'select':
                $options = explode("\n", trim($_POST['options'] ?? ''));
                $options = array_map('trim', $options);
                $options = array_filter($options);
                $settings['options'] = $options;
                break;
            case 'file':
                $settings['extensions'] = explode(',', trim($_POST['extensions'] ?? ''));
                $settings['max_size'] = (int)($_POST['max_size'] ?? 0);
                break;
        }

        if ($contentFields->addField(
            $content_type_id,
            $name,
            $machine_name,
            $field_type,
            $settings,
            $is_required,
            $weight
        )) {
            $_SESSION['flash_message'] = "Field added successfully";
            header("Location: ../edit.php?id=$content_type_id");
            exit;
        } else {
            $errors[] = "Failed to add field";
        }
    }
}

$title = "Add Field to {$content_type['name']}";
ob_start();

?><h1>Add Field to <?= htmlspecialchars($content_type['name']) ?></h1>

<?php if (!empty($errors)): ?>
<div class="alert error">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required 
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">    </div>

    <div class="form-group">
        <label for="machine_name">Machine Name</label>
        <input type="text" id="machine_name" name="machine_name" required 
               value="<?= htmlspecialchars($_POST['machine_name'] ?? '') ?>">    </div>

    <div class="form-group">
        <label for="field_type">Field Type</label>
        <select id="field_type" name="field_type" required>
            <?php foreach ($field_types as $value => $label): ?>
            <option value="<?= $value ?>" <?= ($_POST['field_type'] ?? '') === $value ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_required" <?= isset($_POST['is_required']) ? 'checked' : '' ?>>
            Required field
        </label>
    </div>

    <div class="form-group">
        <label for="weight">Weight</label>
        <input type="number" id="weight" name="weight" 
               value="<?= htmlspecialchars($_POST['weight'] ?? 0) ?>">
        <small>Determines field order (lower numbers appear first)</small>
    </div>

    <!-- Field type specific settings (shown/hidden via JS) -->
    <div id="field-settings">
        <!-- Select options -->
        <div class="form-group field-setting" data-field-type="select">
            <label for="options">Options (one per line)</label>
            <textarea id="options" name="options"><?= 
                htmlspecialchars($_POST['options'] ?? '') 
            ?></textarea>
        </div>

        <!-- File upload settings -->
        <div class="form-group field-setting" data-field-type="file">
            <label for="extensions">Allowed Extensions (comma separated)</label>
            <input type="text" id="extensions" name="extensions" 
                   value="<?= htmlspecialchars($_POST['extensions'] ?? '') ?>">
            
            <label for="max_size">Max File Size (KB)</label>
            <input type="number" id="max_size" name="max_size" 
                   value="<?= htmlspecialchars($_POST['max_size'] ?? 0) ?>">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="button primary">Add Field</button>
        <a href="../edit.php?id=<?= $content_type_id ?>" class="button">Cancel</a>
    </div>
</form>

<script>
document.getElementById('field_type').addEventListener('change', function() {
    const selectedType = this.value;
    const settings = document.querySelectorAll('.field-setting');
    
    settings.forEach(setting => {
        setting.style.display = setting.dataset.fieldType === selectedType ? 
            'block' : 'none';
    });
});

<?php
// Trigger change event on load
document.getElementById('field_type').dispatchEvent(new Event('change'));
?></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../admin/layout.php';
