<?php
/**
 * Blog Post View
 * Displays single blog post
 */

require_once __DIR__.'/../shared/heading.php';
require_once __DIR__.'/../shared/back_button.php';

// Sample data - in production this would come from a database
$post = [
    'id' => $_GET['view'],
    'title' => 'Sample Post',
    'content' => 'This is the full content of the blog post.',
    'date' => '2025-06-17'
];

?><main class="container">
    <?= render_back_button('Back to Blog', '?action=blog') ?>
    <?= render_heading(htmlspecialchars($post['title']), 1) ?>
    <article class="post">
        <p class="post-meta">Posted on <?= htmlspecialchars($post['date']) ?></p>
        <div class="post-content">
            <?= htmlspecialchars($post['content']) ?> 
?>        </div>
    </article>
</main>
