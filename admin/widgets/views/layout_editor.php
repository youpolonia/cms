defined('CMS_ADMIN') or die('Unauthorized access');
/** @var array $layout */
/** @var array $regions */

?><div class="widget-layout-editor">
    <div class="editor-header">
        <h2>Widget Layout Manager</h2>
        <div class="region-selector">
            <select id="region-select">
                <?php foreach ($regions as $region): ?>                <option value="<?= htmlspecialchars($region['id']) ?>">
                    <?= htmlspecialchars($region['name'])  ?>
                </option>
                <?php endforeach;  ?>
            </select>
        </div>
    </div>

    <div class="editor-body">
        <div class="widget-list-container">
            <h3>Available Widgets</h3>
            <ul id="widget-list" class="sortable-list">
                <?php foreach ($layout['widgets'] as $widget): ?>
                <li data-widget-id="<?= htmlspecialchars($widget['id']) ?>">
                    <span class="widget-name"><?= htmlspecialchars($widget['name']) ?></span>
                    <button class="visibility-toggle" data-widget-id="<?= htmlspecialchars($widget['id']) ?>">
                        <?= $widget['visible'] ? 'Hide' : 'Show' 
?>                    </button>
                </li>
                <?php endforeach;  ?>
            </ul>
        </div>

        <div class="visibility-editor">
            <h3>Visibility Rules</h3>
            <textarea id="visibility-rules" class="json-editor"><?php
                echo json_encode($layout['rules'], JSON_PRETTY_PRINT);
            ?></textarea>
            <div class="json-error"></div>
        </div>
    </div>

    <div class="editor-footer">
        <button id="save-layout" class="btn-primary">Save Layout</button>
    </div>
</div>
