<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/thememanager.php';
require_once __DIR__.'/../../services/tenant/stylecompiler.php';

$tenantId = $_GET['tenant_id'] ?? '';
if (empty($tenantId)) {
    header('Location: /admin/tenants');
    exit;
}

$themeConfig = \includes\ThemeManager::getActiveTheme($tenantId);
$cssPath = StyleCompiler::compile($tenantId, "themes/{$tenantId}/styles/main.scss");
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branding Dashboard</title>
    <link rel="stylesheet" href="<?= $cssPath ?>">
    <style>
        .branding-preview {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
        }
        .theme-variables {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .variable-control {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Branding Dashboard</h1>
    
    <div class="branding-preview" id="previewArea">
        <h2>Theme Preview</h2>
        <button class="btn">Sample Button</button>
        <p>Sample text with current theme styles</p>
    </div>

    <form id="brandingForm">
        <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenantId) ?>">
        <h2>Theme Variables</h2>
        <div class="theme-variables">
            <?php foreach ($themeConfig['variables'] ?? [] as $var => $value): ?>
                <div class="variable-control">
                    <label><?= htmlspecialchars($var) ?></label>
                    <input type="text" name="variables[<?= htmlspecialchars($var) ?>]" 
                           value="<?= htmlspecialchars($value) ?>" 
                           onchange="updatePreview()">
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Logo Upload</h2>
        <input type="file" name="logo" accept="image/*">

        <button type="submit">Save Changes</button>
    </form>

    <script>
        async
 function updatePreview() {
            const form = document.getElementById('brandingForm');
            const formData = new FormData(form);
            const response = await fetch('/api/tenant/branding', {
                method: 'POST',
                body: formData
            });
            location.reload();
        }

        document.getElementById('brandingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await updatePreview();
        });
    </script>
</body>
</html>
