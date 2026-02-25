<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-directory-listing.php';
require_once $pluginDir . '/includes/class-directory-category.php';
require_once $pluginDir . '/includes/class-directory-review.php';
$stats = \DirectoryListing::getStats();
$categories = \DirectoryCategory::getAll('active');
$pendingReviews = \DirectoryReview::getPending();
ob_start();
?>
<style>
.dir-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.dir-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.dir-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.dir-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:24px}
.dir-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.dir-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.dir-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.dir-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.dir-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-dir{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.review-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.review-row:last-child{border-bottom:none}
.stars{color:#f59e0b;font-size:.85rem}
</style>
<div class="dir-wrap">
    <div class="dir-header"><h1>📍 Directory Dashboard</h1><a href="/admin/directory/listings/create" class="btn-dir">➕ Add Listing</a></div>
    <div class="dir-stats">
        <div class="dir-stat"><div class="val" style="color:#6366f1"><?= $stats['active'] ?></div><div class="lbl">Active</div></div>
        <div class="dir-stat"><div class="val" style="color:#f59e0b"><?= $stats['pending'] ?></div><div class="lbl">Pending</div></div>
        <div class="dir-stat"><div class="val" style="color:#10b981"><?= $stats['featured'] ?></div><div class="lbl">Featured</div></div>
        <div class="dir-stat"><div class="val" style="color:#a5b4fc"><?= $stats['claimed'] ?></div><div class="lbl">Claimed</div></div>
        <div class="dir-stat"><div class="val" style="color:#ef4444"><?= $stats['pending_reviews'] ?></div><div class="lbl">Reviews Queue</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/directory/listings" class="quick-link"><span class="icon">🏢</span><div><div class="text">Listings</div><div class="desc"><?= $stats['total'] ?> total</div></div></a>
        <a href="/admin/directory/categories" class="quick-link"><span class="icon">📁</span><div><div class="text">Categories</div><div class="desc"><?= count($categories) ?> active</div></div></a>
        <a href="/admin/directory/reviews" class="quick-link"><span class="icon">⭐</span><div><div class="text">Reviews</div><div class="desc"><?= $stats['pending_reviews'] ?> pending</div></div></a>
    </div>
    <?php if (!empty($pendingReviews)): ?>
    <div class="dir-card">
        <h3>⭐ Pending Reviews</h3>
        <?php foreach (array_slice($pendingReviews, 0, 5) as $r): ?>
        <div class="review-row">
            <div style="flex:1"><strong style="font-size:.85rem;color:var(--text)"><?= h($r['listing_title']) ?></strong><br><span style="font-size:.75rem;color:var(--muted)"><?= h($r['reviewer_name']) ?> — <span class="stars"><?= str_repeat('★', (int)$r['rating']) ?></span></span></div>
            <div style="display:flex;gap:6px">
                <button onclick="fetch('/api/directory/approve-review/<?= $r['id'] ?>',{method:'POST',credentials:'same-origin'}).then(function(){location.reload()})" style="background:rgba(16,185,129,.15);color:#34d399;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✓</button>
                <button onclick="fetch('/api/directory/reject-review/<?= $r['id'] ?>',{method:'POST',credentials:'same-origin'}).then(function(){location.reload()})" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✕</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Directory Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
