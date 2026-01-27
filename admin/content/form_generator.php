<?php
// session boot (admin)
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

// Start session for CSRF token
cms_session_start('admin');

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['csrf_token']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    csrf_validate_or_403();
    $errors = [];
    $data = [];
    
    foreach ($fields as $field) {
        $value = $_POST[$field['name']] ?? null;
        
        // Validate based on field type
        switch($field['field_type']) {
            case 'text':
                if (empty($value)) {
                    $errors[$field['name']] = 'This field is required';
                }
                break;
                
            case 'textarea':
                if (empty($value)) {
                    $errors[$field['name']] = 'This field is required';
                }
                break;
                
            case 'number':
                if (!is_numeric($value)) {
                    $errors[$field['name']] = 'Please enter a valid number';
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field['name']] = 'Please enter a valid email';
                }
                break;
                
            case 'date':
                if (!strtotime($value)) {
                    $errors[$field['name']] = 'Please enter a valid date';
                }
                break;
                
            case 'boolean':
                $value = isset($_POST[$field['name']]) ? 1 : 0;
                break;
                
            case 'select':
                if (!in_array($value, $field['settings']['options'] ?? [])) {
                    $errors[$field['name']] = 'Please select a valid option';
                }
                break;
                
            case 'file':
                // Handle file upload validation
                if (isset($_FILES[$field['name']])) {
                    // Basic file validation
                    $file = $_FILES[$field['name']];
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $errors[$field['name']] = match($file['error']) {
                            UPLOAD_ERR_INI_SIZE => 'File exceeds maximum size',
                            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                            UPLOAD_ERR_NO_FILE => 'No file was selected',
                            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
                            default => 'File upload failed'
                        };
                    } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                        $errors[$field['name']] = 'File exceeds 5MB limit';
                    }
                }
                break;
        }
        
        $data[$field['name']] = $value;
    }
    
    if (empty($errors)) {
        // Process valid data
        // TODO: Implement data processing/saving
        $success = true;
    }
}

// Add CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$title = "Form Generator: {$content_type['name']}";
ob_start();

?><h1>Form Generator: <?= htmlspecialchars($content_type['name']) ?></h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success">Form submitted successfully!</div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <?php foreach ($fields as $field): ?>
    <div class="form-group">
        <label for="<?= $field['name'] ?>"><?= htmlspecialchars($field['name']) ?></label>
        
        <?php if (isset($errors[$field['name']])): ?>
            <div class="error"><?= $errors[$field['name']] ?></div>
        <?php endif; ?>        <?php 
        switch($field['field_type']):
            case 'text': ?>
                <input type="text" name="<?= $field['name'] ?>" id="<?= $field['name'] ?>" 
                       value="<?= htmlspecialchars($_POST[$field['name']] ?? '') ?>">
            <?php break;
            
            case 'textarea': ?>
                <textarea name="<?= $field['name'] ?>" id="<?= $field['name'] ?>"><?= htmlspecialchars($_POST[$field['name']] ?? '') ?></textarea>
            <?php break;
            
            case 'number': ?>
                <input type="number" name="<?= $field['name'] ?>" id="<?= $field['name'] ?>" 
                       value="<?= htmlspecialchars($_POST[$field['name']] ?? '') ?>">
            <?php break;
            
            case 'email': ?>
                <input type="email" name="<?= $field['name'] ?>" id="<?= $field['name'] ?>" 
                       value="<?= htmlspecialchars($_POST[$field['name']] ?? '') ?>">
            <?php break;
            
            case 'date': ?>
                <input type="date" name="<?= $field['name'] ?>" id="<?= $field['name'] ?>" 
                       value="<?= htmlspecialchars($_POST[$field['name']] ?? '') ?>">
            <?php break;
            
            case 'boolean': ?>
                <input type="checkbox" name="<?= $field['name'] ?>" id="<?= $field['name'] ?>" 
                       <?= isset($_POST[$field['name']]) ? 'checked' : '' ?>>
            <?php break;
            
            case 'select': ?>
                <select name="<?= $field['name'] ?>" id="<?= $field['name'] ?>">
                    <option value="">Select an option</option>
                    <?php foreach ($field['settings']['options'] as $option): ?>
                    <option value="<?= htmlspecialchars($option) ?>" 
                            <?= ($_POST[$field['name']] ?? '') === $option ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            <?php break;
            
            case 'file': ?>
                <input type="file" name="<?= $field['name'] ?>" id="<?= $field['name'] ?>">
            <?php break;
        endswitch; ?>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="button">Submit Form</button>
</form>

<script>
// Client-side validation
document.querySelector('form').addEventListener('submit', function(e) {
    let isValid = true;
    
    <?php foreach ($fields as $field): ?>
        const <?= $field['name'] ?>Field = document.getElementById('<?= $field['name'] ?>');
        const <?= $field['name'] ?>Value = <?= $field['name'] ?>Field.value;
        
        <?php 
        switch($field['field_type']):
            case 'text': ?>
                if (!<?= $field['name'] ?>Value.trim()) {
                    alert('Please enter <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
            
            case 'textarea': ?>
                if (!<?= $field['name'] ?>Value.trim()) {
                    alert('Please enter <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
            
            case 'number': ?>
                if (isNaN(<?= $field['name'] ?>Value)) {
                    alert('Please enter a valid number for <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
            
            case 'email': ?>
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(<?= $field['name'] ?>Value)) {
                    alert('Please enter a valid email for <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
            
            case 'date': ?>
                if (!Date.parse(<?= $field['name'] ?>Value)) {
                    alert('Please enter a valid date for <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
            
            case 'select': ?>
                if (!<?= $field['name'] ?>Value) {
                    alert('Please select an option for <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
            
            case 'file': ?>
                if (!<?= $field['name'] ?>Field.files || !<?= $field['name'] ?>Field.files[0]) {
                    alert('Please select a file for <?= $field['name'] ?>');
                    isValid = false;
                }
            <?php break;
        endswitch; ?>
        if (!isValid) {
            e.preventDefault();
            return false;
        }
    <?php endforeach; ?>
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../admin/layout.php';
