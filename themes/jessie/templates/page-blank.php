<?php
/**
 * Jessie Theme - Blank Page Template
 * Minimal wrapper, outputs content with no additional styling
 * Note: Still wrapped by layout.php (header/footer present)
 *
 * @var array $page Page data array
 * @var string $content Raw content (fallback)
 */
?>
<article class="blank-page">
    <div class="blank-page-content">
        <?= $page['content'] ?? $content ?? '' ?>
    </div>
</article>
