<?php
/** Blog Index Template */
?><!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <?php if (!empty($posts)): ?>
        <div class="blog-posts">
            <?php foreach ($posts as $post): ?>
                <article>
                    <h2><?= htmlspecialchars($post['title']) ?></h2>
                    <div class="post-meta">
                        Published: <?= htmlspecialchars($post['published_at']) ?>
                    </div>
                    <div class="post-content">
                        <?= $post['content'] ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No blog posts found.</p>
    <?php endif; ?>
</body>
</html>
