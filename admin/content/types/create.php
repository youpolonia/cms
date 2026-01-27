<?php
// Verify admin access
require_once __DIR__ . '/../../security/admin-check.php';
require_once __DIR__ . '/../../../core/csrf.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;

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
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($machine_name)) {
        $errors[] = "Machine name is required";
    } elseif (!preg_match('/^[a-z0-9_]+$/', $machine_name)) {
        $errors[] = "Machine name can only contain lowercase letters, numbers and underscores";
    }

    // If no errors, create content type
    if (empty($errors)) {
        require_once __DIR__ . '/../../../core/database.php';
        $contentTypes = ContentTypesFactory::getTypesInstance($db);
        
        if ($contentTypes->create($name, $machine_name, $description)) {
            $success = true;
            $_SESSION['flash_message'] = "Content type created successfully";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to create content type";
        }
    }
}

$title = "Create Content Type";
ob_start();

?><h1>Create Content Type</h1>

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
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= 
            htmlspecialchars($_POST['description'] ?? '') 
        ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="button primary">Create</button>
        <a href="index.php" class="button">Cancel</a>
    </div>
</form>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../admin/layout.php';
