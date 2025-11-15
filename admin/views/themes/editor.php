<?php
$theme = ThemeRegistry::get($themeName);
$themeJson = json_encode($theme, JSON_PRETTY_PRINT);
?><div class="admin-container">
    <h1>Edit Theme: <?= $theme['meta']['name'] ?></h1>
    <form method="POST" action="/admin/themes/save/<?= $themeName ?>">
        <div class="form-group">
            <label for="theme-json">Theme Configuration (JSON)</label>
            <textarea id="theme-json" name="theme_json" class="code-editor"><?= htmlspecialchars($themeJson) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="button primary">Save Changes</button>
            <a href="/admin/themes" class="button">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('theme-json');
    const saveButton = document.querySelector('button[type="submit"]');
    
    saveButton.addEventListener('click', function(e) {
        try {
            JSON.parse(editor.value);
        } catch (err) {
            e.preventDefault();
            alert('Invalid JSON: ' + err.message);
        }
    });
});
</script>
