<?php
/**
 * Single article template — Jessie CMS Demo
 */
$art = $article ?? [];
$artTitle = $art['title'] ?? 'Article';
$artContent = $art['content'] ?? '';
$artDate = date('F j, Y', strtotime($art['published_at'] ?? $art['created_at'] ?? 'now'));
$artViews = number_format($art['views'] ?? 0);
$artCategory = $art['category_name'] ?? '';
$artImage = $art['featured_image'] ?? '';
$artImageAlt = $art['featured_image_alt'] ?? $artTitle;
?>
<div class="jd-page" style="max-width: 800px; margin: 0 auto;">
    <header style="text-align: center; margin-bottom: 40px;">
        <?php if ($artCategory): ?>
        <span style="display: inline-block; padding: 4px 14px; background: rgba(139, 92, 246, 0.15); color: var(--jd-purple, #8b5cf6); border-radius: 20px; font-size: 0.85rem; margin-bottom: 16px;"><?= esc($artCategory) ?></span>
        <?php endif; ?>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); margin-bottom: 16px;"><?= esc($artTitle) ?></h1>
        <div style="display: flex; justify-content: center; gap: 24px; color: var(--jd-text-dim, #94a3b8); font-size: 0.9rem;">
            <span>📅 <?= $artDate ?></span>
            <span>👁 <?= $artViews ?> views</span>
        </div>
    </header>

    <?php if ($artImage): ?>
    <div style="margin-bottom: 40px; border-radius: 16px; overflow: hidden;">
        <img src="<?= esc($artImage) ?>" alt="<?= esc($artImageAlt) ?>" style="width: 100%; height: auto; display: block;" loading="lazy">
    </div>
    <?php endif; ?>

    <div class="jd-article-content" style="line-height: 1.8; font-size: 1.05rem;">
        <?= $artContent ?>
    </div>

    <footer style="margin-top: 60px; padding-top: 32px; border-top: 1px solid var(--jd-border, #1e293b); display: flex; justify-content: space-between; align-items: center;">
        <a href="/blog" class="jd-btn jd-btn-outline" style="text-decoration: none;">← Back to Blog</a>
    </footer>
</div>

<style>
.jd-article-content h2 { margin: 40px 0 20px; font-size: 1.6rem; }
.jd-article-content h3 { margin: 32px 0 16px; font-size: 1.3rem; }
.jd-article-content p { margin-bottom: 20px; color: var(--jd-muted, #cbd5e1); }
.jd-article-content a { color: var(--jd-cyan, #06b6d4); text-decoration: underline; }
.jd-article-content strong { color: var(--jd-text, #e2e8f0); }
.jd-article-content blockquote { border-left: 4px solid var(--jd-purple, #8b5cf6); padding-left: 20px; margin: 24px 0; font-style: italic; color: var(--jd-text-dim, #94a3b8); }
.jd-article-content code { font-family: 'JetBrains Mono', monospace; background: var(--jd-surface, #0f172a); padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
.jd-article-content pre { background: var(--jd-surface, #0f172a); padding: 20px; border-radius: 12px; overflow-x: auto; margin: 24px 0; }
.jd-article-content pre code { background: none; padding: 0; }
.jd-article-content img { max-width: 100%; height: auto; border-radius: 12px; margin: 24px 0; }
</style>
