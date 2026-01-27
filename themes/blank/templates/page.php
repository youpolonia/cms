<?php
/**
 * Blank Canvas Theme - Page Template
 * Renders Theme Builder content with zero styling overhead
 * 
 * @var array $page Page data
 * @var bool $isPreview Preview mode
 */

$content = $page['content'] ?? '';

// Detect Theme Builder content
$isThemeBuilder = (
    strpos($content, 'tb-section') !== false || 
    strpos($content, 'tb-row') !== false ||
    strpos($content, 'class="tb-') !== false
);
?>

<?php if ($isThemeBuilder): ?>
    <!-- Theme Builder Content - Full Width -->
    <div class="tb-page-wrapper tb-blank-canvas">
        <?= $content ?>
    </div>
<?php else: ?>
    <!-- Standard Content with minimal wrapper -->
    <div class="blank-content-wrapper">
        <?= $content ?>
    </div>
<?php endif; ?>
