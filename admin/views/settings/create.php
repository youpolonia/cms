<?php
/**
 * Settings Create View
 * Create a new setting
 *
 * Variables:
 *   $groups - array of existing group names
 *   $errors - array of validation errors (optional)
 *   $data - form data for repopulating on error (optional)
 */

// Escape helper
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Form data for repopulating on validation error
$formData = $data ?? ['key' => '', 'value' => '', 'group_name' => ''];
?>

<div class="content-header">
    <h1>Add New Setting</h1>
    <div class="header-actions">
        <a href="index.php" class="btn btn-secondary">Back to Settings</a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?php echo esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Setting Details</h3>
    </div>
    <div class="card-body">
        <form method="post" action="store.php">
            <?php csrf_field(); ?>

            <div class="form-group">
                <label for="key">Setting Key <span class="required">*</span></label>
                <input type="text" class="form-control" id="key" name="key"
                       value="<?php echo esc($formData['key']); ?>"
                       required
                       pattern="[a-zA-Z0-9_.-]+"
                       title="Only letters, numbers, underscores, dots, and hyphens allowed"
                       maxlength="100"
                       placeholder="e.g., site_name">
                <small class="form-text text-muted">
                    Unique identifier for this setting. Use lowercase with underscores.
                </small>
            </div>

            <div class="form-group">
                <label for="value">Value</label>
                <textarea class="form-control" id="value" name="value"
                          rows="4"
                          placeholder="Enter the setting value..."><?php echo esc($formData['value']); ?></textarea>
                <small class="form-text text-muted">
                    The setting value. Can be text, JSON, or any string data.
                </small>
            </div>

            <div class="form-group">
                <label for="group_name">Group</label>
                <input type="text" class="form-control" id="group_name" name="group_name"
                       value="<?php echo esc($formData['group_name']); ?>"
                       list="group-options"
                       maxlength="50"
                       placeholder="e.g., general, email, security">
                <datalist id="group-options">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo esc($group); ?>">
                    <?php endforeach; ?>
                </datalist>
                <small class="form-text text-muted">
                    Optional group for organizing settings. Select existing or type new.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Setting</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>Common Setting Examples</h3>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Description</th>
                    <th>Group</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>site_name</code></td>
                    <td>The name of your website</td>
                    <td>general</td>
                </tr>
                <tr>
                    <td><code>contact_email</code></td>
                    <td>Primary contact email address</td>
                    <td>general</td>
                </tr>
                <tr>
                    <td><code>smtp_host</code></td>
                    <td>SMTP server hostname</td>
                    <td>email</td>
                </tr>
                <tr>
                    <td><code>maintenance_mode</code></td>
                    <td>Enable/disable maintenance mode (1/0)</td>
                    <td>system</td>
                </tr>
                <tr>
                    <td><code>cache_ttl</code></td>
                    <td>Cache time-to-live in seconds</td>
                    <td>performance</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.form-group {
    margin-bottom: 1.25rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.form-control:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.2);
}
textarea.form-control {
    font-family: monospace;
    resize: vertical;
}
.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #666;
}
.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}
.required {
    color: #dc3545;
}
.error-list {
    margin: 0;
    padding-left: 1.25rem;
}
code {
    background: #f4f4f4;
    padding: 0.125rem 0.375rem;
    border-radius: 3px;
    font-size: 0.875rem;
}
.table-sm td, .table-sm th {
    padding: 0.5rem;
}
</style>
