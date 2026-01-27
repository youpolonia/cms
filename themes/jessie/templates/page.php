<?php
/**
 * Jessie Theme - Default Page Template
 * Standard page with centered content
 *
 * @var array $page Page data array
 * @var string $content Raw content (fallback)
 */
?>
<section class="page-content">
    <div class="container">
        <article class="page-article">
            <?php if (!empty($page['title'])): ?>
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <?php endif; ?>

            <div class="content-body">
                <?= $page['content'] ?? $content ?? '' ?>
            </div>
        </article>
    </div>
</section>
