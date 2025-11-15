<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../../modules/builderengine/blockrenderer.php';
require_once __DIR__ . '/../../plugins/pluginregistry.php';
require_once __DIR__ . '/../../security/OutputSanitizer.php';

$pluginRegistry = new CMS\Plugins\PluginRegistry(new CMS\Plugins\HookManager());
$sanitizer = new CMS\Security\OutputSanitizer();
$renderer = new CMS\DeveloperTools\BlockSandbox\BlockRenderer($pluginRegistry, $sanitizer);
$renderer->loadAvailableBlocks();
$blocks = $renderer->getLoadedBlocks();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Block Renderer Sandbox</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { display: flex; gap: 20px; }
        .block-list { width: 250px; }
        .preview-area { flex: 1; border: 1px solid #ddd; padding: 15px; }
        select, button { padding: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Block Renderer Sandbox</h1>
    <div class="container">
        <div class="block-list">
            <select id="block-selector">
                <?php foreach ($blocks as $id => $block): ?>                    <option value="<?= htmlspecialchars($id) ?>">
                        <?= htmlspecialchars($block['name']) 
?>                    </option>
                <?php endforeach; ?>
            </select>
            <button id="render-btn">Render Block</button>
            <div id="block-info"></div>
        </div>
        <div class="preview-area" id="preview">
            <p>Select a block and click "Render Block"</p>
        </div>
    </div>

    <script>
        document.getElementById('render-btn').addEventListener('click', async () => {
            const blockId = document.getElementById('block-selector').value;
            const preview = document.getElementById('preview');
            
            try {
                const response = await fetch('render.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ blockId })
                });
                
                if (!response.ok) throw new Error('Rendering failed');
                
                const result = await response.text();
                preview.innerHTML = result;
            } catch (error) {
                preview.innerHTML = `
<div class="error">${error.message}</div>`;
            }
        });
?>    </script>
</body>
</html>
