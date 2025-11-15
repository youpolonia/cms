<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Settings saved successfully</div>
<?php endif;  ?>
<form method="post" action="/admin/settings/save">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <?php foreach ($settings as $key => $value): ?>
        <div class="form-group">
            <label for="setting_<?= $key ?>"><?= ucfirst(str_replace('_', ' ', $key)) ?></label>
            <input type="text" class="form-control" id="setting_<?= $key ?>" 
                   name="setting_<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
        </div>
    <?php endforeach;  ?>
    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>
