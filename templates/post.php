<?php
/**
 * Blog post template
 * Extends layout.php
 */

declare(strict_types=1);

if (!isset($content)) {
    throw new RuntimeException('Content variable not set for post template');
}

ob_start();
?><article class="blog-post">
    <header>
        <h1><?= htmlspecialchars($postTitle ?? 'Untitled Post') ?></h1>
        <?php if (isset($postDate)): ?>            <time datetime="<?= date('Y-m-d', strtotime($postDate)) ?>">
                <?= date('F j, Y', strtotime($postDate)) ?>
            </time>
        <?php endif; ?>        <?php if (isset($author)): ?>
            <p class="author">By <?= htmlspecialchars($author) ?></p>
        <?php endif; ?>
    </header>

<div class="post-content">
        <?= $content ?>
    </div>

    <?php if (isset($tags)): ?>
<footer class="post-footer">
            <div class="post-tags">
                <?php foreach ($tags as $tag): ?>
<span class="tag"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        </footer>
    <?php endif; ?>
</article>
<?php
$content = ob_get_clean();
// Render within base layout template
require_once __DIR__ . '/layout.php';
