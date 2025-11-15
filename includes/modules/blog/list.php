<?php
/**
 * Blog List View
 * Displays paginated list of blog posts
 */

require_once __DIR__.'/../shared/heading.php';
require_once __DIR__.'/../shared/pagination.php';

// Sample data - in production this would come from a database
$posts = [
    ['id' => 1, 'title' => 'First Post', 'excerpt' => 'This is the first blog post'],
    ['id' => 2, 'title' => 'Second Post', 'excerpt' => 'Another interesting post'],
    ['id' => 3, 'title' => 'Third Post', 'excerpt' => 'More content for our blog']
];

$currentPage = $_GET['page'] ?? 1;
$totalPages = 3; // Would be calculated from total posts in production

?><main class="container">
    <?= render_heading('Blog Posts', 1) 
?>    <div class="post-list">
        <?php foreach ($posts as $post): ?>
            <article class="post">
                <h2><?= htmlspecialchars($post['title']) ?></h2>
                <p><?= htmlspecialchars($post['excerpt']) ?></p>
                <a href="?action=blog&view=<?= $post['id'] ?>">Read More</a>
            </article>
        <?php endforeach; ?>
    </div>

    <?= render_pagination($currentPage, $totalPages) 
?></main>
