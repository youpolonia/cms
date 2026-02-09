<?php
/**
 * Gallery Page Template
 * Renders all public galleries from the CMS gallery system
 * 
 * Available variables from PageController:
 * - $page (array) — current page data
 * - $content (string) — rendered page content (shown above galleries)
 */

// Load galleries from DB
$_galleries = [];
try {
    $_pdo = \core\Database::connection();
    $_galStmt = $_pdo->query("SELECT * FROM galleries WHERE is_public = 1 ORDER BY sort_order ASC, name ASC");
    $_galleries = $_galStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Load images for each gallery
    foreach ($_galleries as &$_gal) {
        $_imgStmt = $_pdo->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order ASC");
        $_imgStmt->execute([$_gal['id']]);
        $_gal['images'] = $_imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($_gal);
} catch (\Throwable $e) {
    // Silently fail - show page content only
}
?>

<!-- Page Content (from editor) -->
<?php if (!empty($content) && trim(strip_tags($content)) !== ''): ?>
<section class="page-section">
    <div class="container">
        <div class="content-body"><?= $content ?></div>
    </div>
</section>
<?php endif; ?>

<!-- Galleries -->
<?php if (!empty($_galleries)): ?>
<?php foreach ($_galleries as $_gallery): ?>
<?php if (!empty($_gallery['images'])): ?>
<section class="gallery-section" id="gallery-<?= esc($_gallery['slug']) ?>">
    <div class="container">
        <div class="gallery-header">
            <h2><?= esc($_gallery['name']) ?></h2>
            <?php if (!empty($_gallery['description'])): ?>
            <p class="gallery-desc"><?= esc($_gallery['description']) ?></p>
            <?php endif; ?>
            <span class="gallery-count"><?= count($_gallery['images']) ?> photos</span>
        </div>
        
        <div class="gallery-grid">
            <?php foreach ($_gallery['images'] as $_img): ?>
            <div class="gallery-item" data-src="/uploads/media/<?= esc($_img['filename']) ?>">
                <img 
                    src="/uploads/media/<?= esc($_img['filename']) ?>" 
                    alt="<?= esc($_img['title'] ?? $_img['original_name'] ?? '') ?>"
                    loading="lazy"
                >
                <?php if (!empty($_img['title'])): ?>
                <div class="gallery-caption">
                    <span><?= esc($_img['title']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endforeach; ?>

<?php else: ?>
<section class="gallery-section gallery-empty">
    <div class="container" style="text-align:center;padding:60px 20px">
        <div style="font-size:3rem;margin-bottom:16px">📷</div>
        <h2>No Galleries Yet</h2>
        <p style="color:var(--text-muted,#a6adc8)">Galleries will appear here once created in the admin panel.</p>
        <?php if (function_exists('cms_is_admin_logged_in') && cms_is_admin_logged_in()): ?>
        <a href="/admin/galleries" style="display:inline-block;margin-top:16px;padding:10px 24px;background:var(--accent,#89b4fa);color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Manage Galleries</a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Lightbox -->
<div id="gallery-lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:99998;cursor:pointer;align-items:center;justify-content:center" onclick="this.style.display='none'">
    <button onclick="event.stopPropagation();document.getElementById('gallery-lightbox').style.display='none'" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:2rem;cursor:pointer;z-index:2">&times;</button>
    <img id="lightbox-img" src="" alt="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px">
    <div id="lightbox-caption" style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);color:#fff;font-size:0.9rem;background:rgba(0,0,0,.5);padding:8px 20px;border-radius:50px"></div>
</div>

<style>
.gallery-section { padding: 48px 0; }
.gallery-header { margin-bottom: 32px; }
.gallery-header h2 { font-size: 1.75rem; margin-bottom: 8px; }
.gallery-desc { color: var(--text-muted, #a6adc8); font-size: 1rem; margin-bottom: 4px; }
.gallery-count { font-size: 0.8rem; color: var(--text-muted, #6c7086); }
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
}
.gallery-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    aspect-ratio: 4/3;
    background: var(--bg-secondary, #1e1e2e);
}
.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.gallery-item:hover img { transform: scale(1.05); }
.gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 10px 14px;
    background: linear-gradient(transparent, rgba(0,0,0,.7));
    color: #fff;
    font-size: 0.85rem;
    opacity: 0;
    transition: opacity 0.3s;
}
.gallery-item:hover .gallery-caption { opacity: 1; }
@media (max-width: 600px) {
    .gallery-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .gallery-item { border-radius: 6px; }
}
</style>

<script>
document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('click', () => {
        const src = item.dataset.src || item.querySelector('img')?.src;
        const alt = item.querySelector('img')?.alt || '';
        if (src) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox-caption').textContent = alt;
            document.getElementById('gallery-lightbox').style.display = 'flex';
        }
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('gallery-lightbox').style.display = 'none';
});
</script>
