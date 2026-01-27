<?php
/**
 * Blank Canvas Theme - Home Template
 * For homepage built with Theme Builder
 * 
 * @var array $page Page data (optional)
 * @var string $content Content (optional)
 */

$pageContent = $content ?? ($page['content'] ?? '');

// Detect Theme Builder content
$isThemeBuilder = (
    strpos($pageContent, 'tb-section') !== false || 
    strpos($pageContent, 'tb-row') !== false ||
    strpos($pageContent, 'class="tb-') !== false
);
?>

<?php if ($isThemeBuilder): ?>
    <!-- Theme Builder Homepage - Full Control -->
    <div class="tb-page-wrapper tb-blank-canvas tb-home">
        <?= $pageContent ?>
    </div>
<?php elseif (!empty($pageContent)): ?>
    <!-- Standard Homepage Content -->
    <div class="blank-content-wrapper blank-home">
        <?= $pageContent ?>
    </div>
<?php else: ?>
    <!-- Empty Homepage - Build with Theme Builder -->
    <div class="blank-empty-home">
        <div class="blank-empty-message">
            <h1>Welcome to Blank Canvas</h1>
            <p>This is a blank theme ready for Theme Builder.</p>
            <p>Create your homepage in Theme Builder to see content here.</p>
        </div>
    </div>
<?php endif; ?>
