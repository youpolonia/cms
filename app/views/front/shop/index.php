<div class="shop-page">
    <div class="shop-header">
        <h1 class="shop-title"><?= htmlspecialchars($title ?? 'Shop', ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="shop-description"><?= htmlspecialchars($description ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if (!empty($categories)): ?>
    <div class="shop-filters">
        <a href="/shop" class="shop-filter-btn<?= empty($_GET['category']) ? ' active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="/shop/category/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" class="shop-filter-btn"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <p class="shop-empty">No products found.</p>
    <?php else: ?>
        <?php
        // Load review ratings for all displayed products
        require_once CMS_ROOT . '/core/shop-reviews.php';
        require_once CMS_ROOT . '/core/shop-wishlist.php';
        $productRatings = [];
        $wishlistIds = [];
        $wlSession = (session_status() === PHP_SESSION_ACTIVE && !empty(session_id())) ? session_id() : '';
        foreach ($products as $p) {
            $productRatings[(int)$p['id']] = \ShopReviews::getProductRating((int)$p['id']);
            if ($wlSession) {
                $wishlistIds[(int)$p['id']] = \ShopWishlist::isInWishlist($wlSession, (int)$p['id']);
            }
        }
        ?>
        <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <?php $pr = $productRatings[(int)$p['id']] ?? ['average' => 0, 'count' => 0]; ?>
            <?php $isWished = $wishlistIds[(int)$p['id']] ?? false; ?>
            <div class="product-card">
            <button type="button" class="shop-wish-btn" data-id="<?= (int)$p['id'] ?>"><?= $isWished ? '❤️' : '🤍' ?></button>
            <a href="/shop/<?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card-link">
                <?php if (!empty($p['image'])): ?>
                    <div class="product-card-image"><img src="<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                <?php else: ?>
                    <div class="product-card-image"><div class="product-card-placeholder">📦</div></div>
                <?php endif; ?>
                <div class="product-card-body">
                    <h3 class="product-card-title"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if ($pr['count'] > 0): ?>
                        <div class="product-card-rating">
                            <span class="stars"><?php for ($i = 1; $i <= 5; $i++) echo $i <= round($pr['average']) ? '★' : '☆'; ?></span>
                            <span class="count">(<?= $pr['count'] ?>)</span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($p['short_description'])): ?>
                        <p class="product-card-description"><?= htmlspecialchars($p['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <div class="product-card-price">
                        <?php if ($p['sale_price'] !== null && (float)$p['sale_price'] > 0): ?>
                            <span class="original"><?= \Shop::formatPrice((float)$p['price']) ?></span>
                            <span class="sale"><?= \Shop::formatPrice((float)$p['sale_price']) ?></span>
                        <?php else: ?>
                            <?= \Shop::formatPrice((float)$p['price']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            </div>
        <?php endforeach; ?>
        </div>

<script>
(function(){
    document.querySelectorAll('.shop-wish-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = this.getAttribute('data-id');
            var el = this;
            var fd = new FormData();
            fd.append('product_id', id);
            fetch('/shop/wishlist/toggle', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        el.textContent = d.added ? '❤️' : '🤍';
                    }
                });
        });
    });
})();
</script>

        <?php if ($totalPages > 1): ?>
        <div class="shop-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $qs = $_GET; $qs['page'] = $i; ?>
                <a href="/shop?<?= http_build_query($qs) ?>" class="shop-pagination-link<?= $i === $currentPage ? ' active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* Shop fallback styles (used when no theme CSS is loaded) */
.shop-page { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
.shop-header { margin-bottom: 30px; }
.shop-title { font-size: 2rem; margin-bottom: 8px; }
.shop-description { color: #666; margin-bottom: 0; }
.shop-empty { text-align: center; padding: 60px 0; color: #999; }
.shop-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.shop-filter-btn { padding: 6px 14px; border-radius: 20px; text-decoration: none; font-size: .85rem; background: #f1f5f9; color: #333; }
.shop-filter-btn.active { background: var(--primary-color, #6366f1); color: #fff; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
.product-card { position: relative; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: transform .2s; }
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.12); }
.product-card-link { text-decoration: none; color: inherit; display: block; }
.product-card-image img { width: 100%; height: 200px; object-fit: cover; }
.product-card-placeholder { width: 100%; height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 3rem; }
.product-card-body { padding: 16px; }
.product-card-title { margin: 0 0 8px; font-size: 1rem; }
.product-card-description { margin: 0 0 12px; font-size: .85rem; color: #666; line-height: 1.4; }
.product-card-price { font-weight: 700; font-size: 1.1rem; color: var(--primary-color, #6366f1); }
.product-card-price .original { text-decoration: line-through; color: #999; font-size: .85rem; font-weight: 400; }
.product-card-price .sale { color: var(--primary-color, #6366f1); }
.product-card-rating { margin-bottom: 8px; font-size: .8rem; display: flex; align-items: center; gap: 4px; }
.product-card-rating .stars { color: #f59e0b; letter-spacing: 1px; }
.product-card-rating .count { color: #999; }
.shop-wish-btn { position: absolute; top: 8px; right: 8px; z-index: 2; background: rgba(255,255,255,.85); border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: .95rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.shop-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
.shop-pagination-link { padding: 8px 14px; border-radius: 6px; text-decoration: none; background: #f1f5f9; color: #333; }
.shop-pagination-link.active { background: var(--primary-color, #6366f1); color: #fff; }
</style>
