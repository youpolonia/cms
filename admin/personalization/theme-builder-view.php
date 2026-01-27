<?php require_once __DIR__ . '/../../admin/header.php'; 
?><div class="container">
    <h1>Theme Builder</h1>
    
    <div class="theme-selector">
        <h2>Select Theme</h2>
        <select id="themeSelector" class="form-control">
            <?php foreach ($availableThemes as $key => $name): ?>                <option value="<?= $key ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="theme-preview">
        <h2>Preview</h2>
        <div id="themePreview">
            <!-- Preview will be loaded here via JavaScript -->
        </div>
    </div>
</div>

<script>
document.getElementById('themeSelector').addEventListener('change', function() {
    const theme = this.value;
    fetch(`/api/theme/load?name=${theme}`)
        .then(response => response.json())
        .then(data => {
            // Update preview with theme data
            document.getElementById('themePreview').innerHTML = `
                <div style="background: ${data.colors.background}; padding: 20px;">
                    <h3 style="color: ${data.colors.text}">${data.name}</h3>
                    <p style="color: ${data.colors.text}">${data.description}</p>
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <div style="background: ${data.colors.primary}; width: 50px; height: 50px;"></div>
                        <div style="background: ${data.colors.secondary}; width: 50px; height: 50px;"></div>
                        <div style="background: ${data.colors.accent}; width: 50px; height: 50px;"></div>
                    </div>
                </div>
            `;
        });
});
</script>

<?php require_once __DIR__ . '/../../admin/footer.php';
