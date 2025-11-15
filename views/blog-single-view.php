<?php
require_once __DIR__ . '/../models/blogmanager.php';

$slug = $_GET['slug'] ?? '';
$blogManager = new BlogManager();
$post = $blogManager->getPost($slug);

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found";
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post->title) ?></title>
    <style>
        .post { max-width: 800px; margin: 0 auto; }
        .post-title { font-size: 2rem; margin-bottom: 1rem; }
        .post-date { color: #666; font-size: 0.9rem; margin-bottom: 2rem; }
        .post-body { line-height: 1.6; }
        .post-tags { margin-top: 2rem; }
        .tag { display: inline-block; background: #f0f0f0; padding: 0.3rem 0.6rem; margin-right: 0.5rem; border-radius: 3px; }
        .back-link { display: block; margin-top: 2rem; }
    </style>
</head>
<body>
    <div class="post">
        <h1 class="post-title"><?= htmlspecialchars($post->title) ?></h1>
        <div class="post-date"><?= date('F j, Y', strtotime($post->date)) ?></div>
        <div class="post-body"><?= nl2br(htmlspecialchars($post->body)) ?></div>
        
        <?php if (!empty($post->tags)): ?>
            <div class="post-tags">
                Tags: 
                <?php foreach ($post->tags as $tag): ?>
                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach;  ?>
            </div>
        <?php endif;  ?>
        <a href="/blog" class="back-link">← Back to all posts</a>
    </div>
</body>
</html>
