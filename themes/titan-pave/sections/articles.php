<?php
$articlesLabel = theme_get('articles.label', 'Latest News');
$articlesTitle = theme_get('articles.title', 'Tips, Updates & Insights');
$articlesDesc = theme_get('articles.description', 'Stay informed with the latest news from our team, helpful guides, and industry updates.');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');
?>
<section class="section articles-section" id="articles">
    <div class="container">
        <div class="articles-header" data-animate>
            <div class="section-header">
                <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
                <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
                <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
            </div>
            <a href="<?= esc($articlesBtnLink) ?>" class="btn btn-outline" data-ts="articles.btn_text" data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="articles-grid">
            <?php if (!empty($articles)): ?>
                <?php foreach (array_slice($articles, 0, 3) as $index => $a): ?>
                <article class="article-card" data-animate style="--delay: <?= $index * 0.1 ?>s">
                    <a href="/article/<?= esc($a['slug']) ?>" class="article-link">
                        <?php if (!empty($a['featured_image'])): ?>
                        <div class="article-image" style="background-image: url('<?= esc($a['featured_image']) ?>')"></div>
                        <?php else: ?>
                        <div class="article-image article-image--placeholder">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <?php endif; ?>
                        <div class="article-content">
                            <div class="article-meta">
                                <?php if (!empty($a['category_name'])): ?>
                                <span class="article-category"><?= esc($a['category_name']) ?></span>
                                <?php endif; ?>
                                <span class="article-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?>
                                </span>
                            </div>
                            <h3 class="article-title"><?= esc($a['title']) ?></h3>
                            <p class="article-excerpt"><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 120, '...')) ?></p>
                            <span class="article-read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <?php 
                $placeholderArticles = [
                    ['title' => 'How to Choose the Right Paving for Your Driveway', 'excerpt' => 'A comprehensive guide to selecting the perfect paving material for your home...', 'date' => 'Jan 15, 2024'],
                    ['title' => '5 Signs Your Driveway Needs Repair', 'excerpt' => 'Learn to identify early warning signs that your driveway may need attention...', 'date' => 'Jan 8, 2024'],
                    ['title' => 'Block Paving vs Tarmac: Which is Best?', 'excerpt' => 'Comparing the pros and cons of two popular driveway surfacing options...', 'date' => 'Dec 28, 2023']
                ];
                foreach ($placeholderArticles as $index => $article): ?>
                <article class="article-card" data-animate style="--delay: <?= $index * 0.1 ?>s">
                    <div class="article-link">
                        <div class="article-image article-image--placeholder">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-category">Tips</span>
                                <span class="article-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= $article['date'] ?>
                                </span>
                            </div>
                            <h3 class="article-title"><?= $article['title'] ?></h3>
                            <p class="article-excerpt"><?= $article['excerpt'] ?></p>
                            <span class="article-read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>