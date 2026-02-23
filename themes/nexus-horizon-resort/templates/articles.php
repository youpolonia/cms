<?php
/**
 * Articles List Template — AI Generated
 * 2-column layout: article grid + sidebar
 */
?>
<style>
.atpl-hero{padding:calc(var(--section-spacing,.5) * .6) 0;background:var(--surface,#1a1a2e);text-align:center}
.atpl-hero h1{font-family:var(--heading-font,inherit);font-size:clamp(1.8rem,4vw,2.8rem);margin:0 0 8px;color:var(--text,#fff)}
.atpl-breadcrumb{display:flex;align-items:center;justify-content:center;gap:8px;font-size:.85rem;color:var(--text-muted,#999)}
.atpl-breadcrumb a{color:var(--primary,#6c63ff);text-decoration:none}
.atpl-wrap{display:grid;grid-template-columns:1fr 300px;gap:40px;max-width:var(--container-width,1200px);margin:0 auto;padding:calc(var(--section-spacing,.5) * .8) 20px}
.atpl-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:24px}
.atpl-card{background:var(--surface,#1e1e2e);border-radius:var(--border-radius,12px);overflow:hidden;transition:transform .25s,box-shadow .25s;text-decoration:none;color:inherit;display:flex;flex-direction:column;border:1px solid var(--border,#2a2a3a)}
.atpl-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.2)}
.atpl-card-img{position:relative;aspect-ratio:16/9;overflow:hidden;background:var(--background,#111)}
.atpl-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s}
.atpl-card:hover .atpl-card-img img{transform:scale(1.05)}
.atpl-card-img .atpl-placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:2rem;color:var(--text-muted,#666)}
.atpl-tag{position:absolute;top:12px;left:12px;background:var(--primary,#6c63ff);color:#fff;font-size:.7rem;padding:4px 10px;border-radius:20px;font-weight:600;text-transform:uppercase;letter-spacing:.05em}
.atpl-card-body{padding:20px;flex:1;display:flex;flex-direction:column}
.atpl-date{font-size:.78rem;color:var(--text-muted,#888);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.atpl-card-body h3{font-family:var(--heading-font,inherit);font-size:1.05rem;margin:0 0 8px;line-height:1.4;color:var(--text,#fff)}
.atpl-card-body p{font-size:.88rem;color:var(--text-muted,#aaa);line-height:1.6;margin:0;flex:1}
.atpl-sidebar{display:flex;flex-direction:column;gap:24px}
.atpl-widget{background:var(--surface,#1e1e2e);border-radius:var(--border-radius,12px);padding:24px;border:1px solid var(--border,#2a2a3a)}
.atpl-widget-title{font-size:.9rem;font-weight:700;margin:0 0 16px;display:flex;align-items:center;gap:8px;color:var(--text,#fff);text-transform:uppercase;letter-spacing:.05em;font-family:var(--heading-font,inherit)}
.atpl-cat-link{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border,#2a2a3a);text-decoration:none;color:var(--text-muted,#aaa);font-size:.9rem;transition:color .2s}
.atpl-cat-link:last-child{border-bottom:0}
.atpl-cat-link:hover{color:var(--primary,#6c63ff)}
.atpl-cat-count{background:var(--background,#111);font-size:.75rem;padding:2px 8px;border-radius:10px;min-width:24px;text-align:center}
.atpl-search-form{display:flex;gap:8px}
.atpl-search-input{flex:1;padding:10px 14px;border:1px solid var(--border,#2a2a3a);border-radius:var(--border-radius,8px);background:var(--background,#111);color:var(--text,#fff);font-size:.9rem}
.atpl-search-btn{padding:10px 16px;background:var(--primary,#6c63ff);color:#fff;border:0;border-radius:var(--border-radius,8px);cursor:pointer;transition:opacity .2s}
.atpl-search-btn:hover{opacity:.85}
.atpl-pagination{display:flex;align-items:center;justify-content:center;gap:16px;padding:40px 0 0;grid-column:1/-1}
.atpl-pagination a{padding:10px 20px;border:1px solid var(--border,#2a2a3a);border-radius:var(--border-radius,8px);color:var(--text,#fff);text-decoration:none;font-size:.9rem;transition:border-color .2s,background .2s}
.atpl-pagination a:hover{border-color:var(--primary,#6c63ff);background:var(--surface,#1e1e2e)}
.atpl-pagination span{font-size:.85rem;color:var(--text-muted,#888)}
.atpl-empty{text-align:center;padding:80px 20px;grid-column:1/-1}
.atpl-empty i{font-size:3rem;color:var(--text-muted,#555);margin-bottom:16px}
@media(max-width:1024px){.atpl-wrap{grid-template-columns:1fr;}.atpl-sidebar{order:2}}
@media(max-width:640px){.atpl-grid{grid-template-columns:1fr}}
</style>

<section class="atpl-hero">
    <div class="container">
        <h1>Articles</h1>
        <div class="atpl-breadcrumb">
            <a href="/">Home</a>
            <span><i class="fas fa-chevron-right" style="font-size:.6rem"></i></span>
            <span>Articles</span>
        </div>
    </div>
</section>

<div class="atpl-wrap">
    <div>
        <?php if (!empty($articles)): ?>
        <div class="atpl-grid">
            <?php foreach ($articles as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="atpl-card" data-animate>
                <div class="atpl-card-img">
                    <?php if (!empty($a['featured_image'])): ?>
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy" decoding="async">
                    <?php else: ?>
                    <div class="atpl-placeholder"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="atpl-tag"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="atpl-card-body">
                    <span class="atpl-date"><i class="far fa-calendar-alt"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                    <h3><?= esc($a['title']) ?></h3>
                    <p><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 130, '...')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="atpl-pagination">
            <?php if ($currentPage > 1): ?>
            <a href="/articles?page=<?= $currentPage - 1 ?>"><i class="fas fa-chevron-left"></i> Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
            <a href="/articles?page=<?= $currentPage + 1 ?>">Next <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="atpl-empty">
            <i class="fas fa-newspaper"></i>
            <h2 style="font-weight:400;margin:0 0 8px">No articles yet</h2>
            <p style="color:var(--text-muted,#888)">Check back soon for new content.</p>
        </div>
        <?php endif; ?>
    </div>

    <aside class="atpl-sidebar">
    <!-- Categories Widget -->
    <div class="atpl-widget">
        <h4 class="atpl-widget-title">
            <i class="fas fa-tags"></i>
            Categories
        </h4>
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
                <a href="/articles?category=<?= esc($cat['slug']) ?>" class="atpl-cat-link">
                    <span><?= esc($cat['name']) ?></span>
                    <span class="atpl-cat-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--text-muted,#888);font-size:.9rem">No categories yet.</p>
        <?php endif; ?>
    </div>

    <!-- Search Box -->
    <div class="atpl-widget">
        <h4 class="atpl-widget-title">
            <i class="fas fa-search"></i>
            Search Articles
        </h4>
        <form class="atpl-search-form" action="/articles" method="GET">
            <input type="text" name="q" placeholder="Type keywords..." class="atpl-search-input">
            <button type="submit" class="atpl-search-btn" aria-label="Search">
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <!-- About This Blog -->
    <div class="atpl-widget">
        <h4 class="atpl-widget-title">
            <i class="fas fa-info-circle"></i>
            About This Blog
        </h4>
        <p style="color:var(--text-muted,#aaa);font-size:.9rem;line-height:1.6" data-ts="sidebar.about">
            <?= esc(theme_get('sidebar.about', 'Insights from the intersection of design, technology, and hospitality. Explore the future of urban resorts.')) ?>
        </p>
    </div>

    <!-- Newsletter Signup -->
    <div class="atpl-widget">
        <h4 class="atpl-widget-title">
            <i class="fas fa-paper-plane"></i>
            Stay Updated
        </h4>
        <p style="color:var(--text-muted,#aaa);font-size:.88rem;line-height:1.5;margin:0 0 12px">Get the latest on smart rooms, art exhibitions, and rooftop events.</p>
        <form class="atpl-search-form">
            <input type="email" placeholder="Your email address" class="atpl-search-input" required>
            <button type="button" class="atpl-search-btn">Subscribe</button>
        </form>
        <p style="color:var(--text-muted,#666);font-size:.78rem;margin:8px 0 0">No spam. Unsubscribe anytime.</p>
    </div>

    <!-- Social Follow -->
    <div class="atpl-widget">
        <h4 class="atpl-widget-title">
            <i class="fas fa-share-alt"></i>
            Follow Us
        </h4>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <?php if (theme_get('footer.facebook')): ?>
                <a href="<?= esc(theme_get('footer.facebook')) ?>" target="_blank" style="color:var(--primary,#6c63ff);font-size:1.25rem"><i class="fab fa-facebook-f"></i></a>
            <?php endif; ?>
            <?php if (theme_get('footer.instagram')): ?>
                <a href="<?= esc(theme_get('footer.instagram')) ?>" target="_blank" style="color:var(--primary,#6c63ff);font-size:1.25rem"><i class="fab fa-instagram"></i></a>
            <?php endif; ?>
            <?php if (theme_get('footer.twitter')): ?>
                <a href="<?= esc(theme_get('footer.twitter')) ?>" target="_blank" style="color:var(--primary,#6c63ff);font-size:1.25rem"><i class="fab fa-twitter"></i></a>
            <?php endif; ?>
            <?php if (theme_get('footer.linkedin')): ?>
                <a href="<?= esc(theme_get('footer.linkedin')) ?>" target="_blank" style="color:var(--primary,#6c63ff);font-size:1.25rem"><i class="fab fa-linkedin-in"></i></a>
            <?php endif; ?>
        </div>
    </div>
</aside>
</div>