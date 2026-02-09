<?php
/**
 * Default Page Template — AppFlow
 * Available: $page (array), $content (string)
 */
?>
<section class="page-hero">
    <div class="container">
        <h1><?= esc($page['title'] ?? 'Page') ?></h1>
        <?php if (!empty($page['meta_description'])): ?>
        <p><?= esc($page['meta_description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-body">
            <?= $content ?>
        </div>
    </div>
</section>
