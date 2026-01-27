<?php
/**
 * Widget Layout Manager
 * Allows arranging widgets in regions and setting visibility rules
 */

// Get available regions from configuration
$regions = [
    'header' => 'Header',
    'sidebar' => 'Sidebar',
    'footer' => 'Footer',
    'content_top' => 'Content Top',
    'content_bottom' => 'Content Bottom'
];

// Get current widget configuration (placeholder - should come from DB)
$widgets = [
    ['id' => 1, 'title' => 'Search Box', 'region' => 'sidebar', 'position' => 1],
    ['id' => 2, 'title' => 'Recent Posts', 'region' => 'sidebar', 'position' => 2],
    ['id' => 3, 'title' => 'Newsletter', 'region' => 'footer', 'position' => 1]
];

// Get visibility rules (placeholder)
$visibilityRules = [
    'rules' => [
        'Search Box' => ['pages' => ['home', 'blog']],
        'Recent Posts' => ['logged_in' => true]
    ]
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Layout Manager</title>
    <link rel="stylesheet" href="/admin/assets/css/widgets/layout.css">
</head>
<body>
    <div class="widget-layout-container">
        <h1>Widget Layout Manager</h1>
        
        <div class="region-selector">
            <label for="region">Select Region:</label>
            <select id="region">
                <?php foreach ($regions as $value => $label): ?>                    <option value="<?= htmlspecialchars($value) ?>">
                        <?= htmlspecialchars($label)  ?>
                    </option>
                <?php endforeach;  ?>
            </select>
        </div>

        <div class="widget-list-container">
            <h2>Widgets in Selected Region</h2>
            <ul id="widget-list" class="widget-list">
                <?php foreach ($widgets as $widget): ?>
                    <li data-id="<?= $widget['id'] ?>" data-region="<?= $widget['region'] ?>">
                        <span class="widget-title"><?= htmlspecialchars($widget['title']) ?></span>
                        <div class="widget-controls">
                            <button class="move-up">↑</button>
                            <button class="move-down">↓</button>
                        </div>
                    </li>
                <?php endforeach;  ?>
            </ul>
        </div>

        <div class="visibility-rules">
            <h2>Visibility Rules (JSON)</h2>
            <textarea id="visibility-rules"><?= json_encode($visibilityRules, JSON_PRETTY_PRINT) ?></textarea>
        </div>

        <div class="actions">
            <button id="save-layout" class="btn-primary">Save Layout</button>
        </div>
    </div>

    <script src="/admin/assets/js/widgets/layout.js"></script>
</body>
</html>
