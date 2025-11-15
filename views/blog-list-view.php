<?php
require_once __DIR__ . '/../models/blogmanager.php';

$blogManager = new BlogManager();
$posts = $blogManager->getAllPosts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog Posts</title>
    <style>
        .post { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .post-title { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .post-date { color: #666; font-size: 0.9rem; }
        .post-excerpt { margin: 1rem 0; }
        .post-tags { margin-top: 0.5rem; }
        .tag { display: inline-block; background: #f0f0f0; padding: 0.2rem 0.5rem; margin-right: 0.5rem; border-radius: 3px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <h1>Blog Posts</h1>
    
    <?php if (empty($posts)): ?>
<p>No blog posts found.</p>
    <?php else: ?>        <?php foreach ($posts as $post): ?>
<div class="post">
                <h2 class="post-title">
                    <a href="/blog/<?= htmlspecialchars($post->slug) ?>">
                        <?= htmlspecialchars($post->title) ?>
                    </a>
                </h2>
<div class="post-date"><?= date('F j, Y', strtotime($post->date)) ?></div>
                <div class="post-excerpt"><?= htmlspecialchars($post->getExcerpt()) ?></div>
                <?php if (!empty($post->tags)): ?>
<div class="post-tags">
                        <?php foreach ($post->tags as $tag): ?>
<span class="tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>    <?php endif; ?>
</body>
</html>
