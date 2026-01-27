<?php
// Verify direct access
if (!defined('CMS_ADMIN')) {
    exit('Direct access not permitted');
}

// Get widget data and errors from parent scope
$widget = $widget ?? [];
$errors = $errors ?? [];

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;
} else {
    $csrf_token = $_SESSION['csrf_token'];
}

?><div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Widget Settings</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach;  ?>
                </ul>
            </div>
        <?php endif;  ?>
        <form method="post" action="?action=save">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="mb-3">
                <label for="widget_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="widget_title" name="title" 
                       value="<?php echo htmlspecialchars($widget['title'] ?? ''); ?>"
 required>
?>            </div>

            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="widget_status" name="status" 
                       <?php echo ($widget['status'] ?? true) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="widget_status">Active</label>
            </div>

            <div class="mb-3">
                <label for="widget_css" class="form-label">Custom CSS</label>
                <textarea class="form-control" id="widget_css" name="css" rows="5">
                    <?php echo htmlspecialchars($widget['css'] ?? ''); 
?>                </textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
