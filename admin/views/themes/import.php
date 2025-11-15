<?php
require_once __DIR__ . '/../../admin_header.php';

$message = $_SESSION['message'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['message']);
unset($_SESSION['error']);
?><div class="admin-container">
    <h1>Import Theme</h1>
    
    <?php if ($message): ?>
        <div class="alert success"><?= $message ?></div>
    <?php endif;  ?>    
    <?php if ($error): ?>
        <div class="alert danger"><?= $error ?></div>
    <?php endif;  ?>
    <form method="POST" action="/admin/themes/import" enctype="multipart/form-data">
        <div class="form-group">
            <label for="theme-file">Theme File (JSON)</label>
            <input type="file" id="theme-file" name="theme_file" accept=".json"
 required>
?>            <p class="help-text">Upload a theme JSON file exported from another installation</p>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="button primary">Import Theme</button>
            <a href="/admin/themes" class="button">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../admin_footer.php';
