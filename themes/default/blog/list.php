<?php
/**
 * Blog listing template
 */
?><h1>Blog Posts</h1>

<?php if (empty($posts)): ?>
    <p>No posts found.</p>
<?php else: ?>
    <div class="blog-list">
        <?php foreach ($posts as $post): ?>
            <article class="blog-post">
                <h2><a href="/blog/<?= htmlspecialchars($post['slug']) ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a></h2>
                <time datetime="<?= $post['published_at'] ?>">
                    <?= date('F j, Y', strtotime($post['published_at'])) ?>
                </time>
                <p><?= htmlspecialchars($post['excerpt']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/blog?page=<?= $i ?>" <?= $i == $currentPage ? 'class="active"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?><?php endif;
