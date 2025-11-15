<?php
/**
 * Blog Post View Template
 * Displays single blog post
 */

use function includes\modules\shared\render_heading;
use function includes\modules\shared\render_back_button;
?>
<div class="blog-container">
    <?= render_heading(htmlspecialchars($post['title'])) ?>
    <article class="post-content">
        <div class="post-meta">
            <span class="post-date"><?= date('F j, Y') ?></span>
        </div>
        <div class="post-body">
            <p><?= htmlspecialchars($post['content']) ?></p>
        </div>
    </article>

    <?= render_back_button('Back to Blog', '?action=list') ?>
</div>
