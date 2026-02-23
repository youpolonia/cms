<?php
$articlesLabel = theme_get('articles.label', 'INDUSTRY INSIGHTS');
$articlesTitle = theme_get('articles.title', 'Latest from Our Construction Blog');
$articlesDesc = theme_get('articles.description', 'Expert advice, project spotlights, and industry trends for property managers, developers, and construction professionals.');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');
?>
<section class="section articles-section" id="articles">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="articles-grid">
            <?php if (!empty($articles)): ?>
                <?php foreach (array_slice($articles, 0, 3) as $a): ?>
                    <article class="article-card" data-animate>
                        <?php if (!empty($a['featured_image'])): ?>
                            <a href="/article/<?= esc($a['slug']) ?>" class="article-image">
                                <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                            </a>
                        <?php endif; ?>
                        <div class="article-content">
                            <div class="article-meta">
                                <?php if (!empty($a['category_name'])): ?>
                                    <span class="article-category"><?= esc($a['category_name']) ?></span>
                                <?php endif; ?>
                                <span class="article-date"><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            </div>
                            <h3 class="article-title"><a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a></h3>
                            <p class="article-excerpt"><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 150, '...')) ?></p>
                            <a href="/article/<?= esc($a['slug']) ?>" class="article-link">
                                Read More <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback demo articles -->
                <?php
                $demoArticles = [
                    [
                        'title' => 'Choosing Between Asphalt & Concrete for Commercial Lots',
                        'category' => 'Materials',
                        'excerpt' => 'A detailed comparison of durability, cost, maintenance, and lifespan for property developers.',
                        'date' => 'Apr 15, 2024'
                    ],
                    [
                        'title' => 'Preventing Pavement Failure: Drainage Design Best Practices',
                        'category' => 'Engineering',
                        'excerpt' => 'How proper slope, subsurface drainage, and permeable solutions extend pavement life by decades.',
                        'date' => 'Mar 28, 2024'
                    ],
                    [
                        'title' => 'The Impact of Weather on Groundwork Timelines',
                        'category' => 'Project Management',
                        'excerpt' => 'Planning construction phases around seasonal factors to avoid delays and cost overruns.',
                        'date' => 'Mar 10, 2024'
                    ],
                ];
                ?>
                <?php foreach ($demoArticles as $article): ?>
                    <article class="article-card" data-animate>
                        <div class="article-image">
                            <div class="article-image-placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        </div>
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-category"><?= $article['category'] ?></span>
                                <span class="article-date"><i class="far fa-calendar"></i> <?= $article['date'] ?></span>
                            </div>
                            <h3 class="article-title"><?= $article['title'] ?></h3>
                            <p class="article-excerpt"><?= $article['excerpt'] ?></p>
                            <a href="#" class="article-link">
                                Read More <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="section-footer" data-animate>
            <a href="<?= esc($articlesBtnLink) ?>" class="btn btn-outline" data-ts="articles.btn_text" data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
