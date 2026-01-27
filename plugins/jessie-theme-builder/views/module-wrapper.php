<?php
/**
 * Module Wrapper View
 * Wrapper template for modules in editor
 *
 * @package JessieThemeBuilder
 *
 * Variables:
 * @var string $moduleType
 * @var string $moduleId
 * @var string $moduleName
 * @var string $moduleIcon
 * @var string $content
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

$esc = function($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
};

// Icon mapping
$icons = [
    'section' => '📦',
    'row' => '▤',
    'column' => '▥',
    'text' => '📝',
    'heading' => '🔤',
    'image' => '🖼️',
    'button' => '🔘'
];

$iconDisplay = $icons[$moduleType] ?? '📦';
?>
<div class="jtb-module-editor"
     data-id="<?php echo $esc($moduleId); ?>"
     data-type="<?php echo $esc($moduleType); ?>"
     draggable="true">

    <div class="jtb-module-toolbar">
        <span class="jtb-module-icon"><?php echo $iconDisplay; ?></span>
        <span class="jtb-module-name"><?php echo $esc($moduleName); ?></span>
        <div class="jtb-toolbar-actions">
            <button class="jtb-toolbar-btn" data-action="move" title="Move">↕️</button>
            <button class="jtb-toolbar-btn" data-action="settings" title="Settings">⚙️</button>
            <button class="jtb-toolbar-btn" data-action="duplicate" title="Duplicate">📋</button>
            <button class="jtb-toolbar-btn" data-action="delete" title="Delete">🗑️</button>
        </div>
    </div>

    <div class="jtb-module-preview">
        <?php echo $content; ?>
    </div>

</div>
