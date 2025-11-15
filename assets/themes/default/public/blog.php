<?php
/**
 * Blog listing template
 * @var array $posts Array of blog posts
 */
$this->extend('base.php');

$this->block('content', function($vars) {
    extract($vars);
?>    <div class="blog-container">
        <h1>Blog</h1>
        
        <?php if (empty($posts)): ?>
            <p>No blog posts found.</p>
        <?php else: ?>
            <div class="blog-posts">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-post">
                        <h2><a href="/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                        <div class="post-meta">
                            <span class="post-date"><?= date('F j, Y', strtotime($post['published_at'])) ?></span>
                        </div>
                        <div class="post-excerpt">
                            <?= $post['excerpt'] ?? '' ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php });
