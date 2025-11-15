<?php
/**
 * Blog List View Template
 * Displays paginated list of blog posts
 */

use function includes\modules\shared\render_heading;
use function includes\modules\shared\render_pagination;
?>
<div class="blog-container">
    <?= render_heading('Blog Posts') ?>
    <div class="post-list">
        <?php foreach ($posts as $post): ?>
            <article class="post-summary">
                <h3>
                    <a href="?action=view&id=<?= $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h3>
                <p><?= htmlspecialchars($post['excerpt']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <?= render_pagination($page, $totalPages) ?>
</div>
