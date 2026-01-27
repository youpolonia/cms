<?php
// Security check
if (!defined('CMS_ADMIN')) {
    exit('Direct access not allowed');
}

// Get existing languages from database
$languages = [];
$default_lang = 'en';
try {
    $stmt = $db->query("SELECT * FROM languages ORDER BY is_default DESC, name ASC");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find default language
    foreach ($languages as $lang) {
        if ($lang['is_default']) {
            $default_lang = $lang['code'];
            break;
        }
    }
} catch (PDOException $e) {
    $error = "Failed to load languages: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        // Handle add/edit language
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            $code = strtolower(trim($_POST['code'] ?? ''));
            $name = trim($_POST['name'] ?? '');
            
            // Validate inputs
            if (empty($code) || empty($name)) {
                $error = "Language code and name are required";
            } elseif (!preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $code)) {
                $error = "Invalid language code format (e.g. en, en_US)";
            } else {
                try {
                    if ($action === 'add') {
                        // Add new language
                        $stmt = $db->prepare("INSERT INTO languages (code, name, is_default) VALUES (?, ?, 0)");
                        $stmt->execute([$code, $name]);
                        $success = "Language added successfully";
                    } elseif ($action === 'edit') {
                        // Update existing language
                        $stmt = $db->prepare("UPDATE languages SET name = ? WHERE code = ?");
                        $stmt->execute([$name, $code]);
                        $success = "Language updated successfully";
                    } elseif ($action === 'set_default') {
                        // Set default language
                        $db->beginTransaction();
                        $stmt = $db->prepare("UPDATE languages SET is_default = 0");
                        $stmt->execute();
                        $stmt = $db->prepare("UPDATE languages SET is_default = 1 WHERE code = ?");
                        $stmt->execute([$code]);
                        $db->commit();
                        $default_lang = $code;
                        $success = "Default language set successfully";
                    }
                    
                    // Refresh languages list
                    $stmt = $db->query("SELECT * FROM languages ORDER BY is_default DESC, name ASC");
                    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?><div class="admin-container">
    <h2>Language Management</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif;  ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Available Languages</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($languages as $lang): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lang['code']); ?></td>
                                    <td><?php echo htmlspecialchars($lang['name']); ?></td>
                                    <td>
                                        <?php if ($lang['is_default']): ?>
                                            <span class="badge bg-success">Default</span>
                                        <?php else: ?>
                                            <form method="post" style="display:inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="set_default">
                                                <input type="hidden" name="code" value="<?php echo $lang['code']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Set Default</button>
                                            </form>
                                        <?php endif;  ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary edit-language" 
                                            data-code="<?php echo $lang['code']; ?>" 
                                            data-name="<?php echo htmlspecialchars($lang['name']); ?>">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach;  ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3><span id="form-title">Add New Language</span></h3>
                </div>
                <div class="card-body">
                    <form method="post" id="language-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" id="form-action" value="add">
                        <input type="hidden" name="original_code" id="original-code">
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Language Code</label>
                            <input type="text" class="form-control" id="code" name="code" 
                                   pattern="[a-z]{2,3}(_[A-Z]{2})?" 
                                   title="2-3 lowercase letters, optional underscore and 2 uppercase letters (e.g. en, en_US)" 
                                   required>
                            <small class="form-text text-muted">ISO 639-1 code (e.g. en, fr, es)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Language Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="submit-btn">Add Language</button>
                        <button type="button" class="btn btn-outline-secondary" id="cancel-edit" style="display:none">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button clicks
    document.querySelectorAll('.edit-language').forEach(btn => {
        btn.addEventListener('click', function() {
            const code = this.dataset.code;
            const name = this.dataset.name;
            
            document.getElementById('form-title').textContent = 'Edit Language';
            document.getElementById('form-action').value = 'edit';
            document.getElementById('original-code').value = code;
            document.getElementById('code').value = code;
            document.getElementById('code').readOnly = true;
            document.getElementById('name').value = name;
            document.getElementById('submit-btn').textContent = 'Update Language';
            document.getElementById('cancel-edit').style.display = 'inline-block';
        });
    });
    
    // Handle cancel edit
    document.getElementById('cancel-edit').addEventListener('click', function() {
        document.getElementById('form-title').textContent = 'Add New Language';
        document.getElementById('form-action').value = 'add';
        document.getElementById('original-code').value = '';
        document.getElementById('code').value = '';
        document.getElementById('code').readOnly = false;
        document.getElementById('name').value = '';
        document.getElementById('submit-btn').textContent = 'Add Language';
        this.style.display = 'none';
    });
});
</script>
