<?php
/**
 * Page content template
 * @var array $content Page content data
 */
$this->extend('base.php');

$this->block('content', function($vars) {
    extract($vars);
?>    <article class="content-page">
        <h1><?= htmlspecialchars($content['title']) ?></h1>
        <div class="post-meta">
            <?php if (!empty($content['published_at'])): ?>
                <span class="post-date">
                    Published: <?= date('F j, Y', strtotime($content['published_at']))  ?>
                </span>
            <?php endif;  ?>
        </div>
        <div class="page-content">
            <?= $content['content']  ?>
        </div>
    </article>
<?php });
