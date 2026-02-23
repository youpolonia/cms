<div class="shop-page">
    <div class="shop-header wishlist-header">
        <div>
            <h1 class="shop-title">❤️ My Wishlist</h1>
            <p class="shop-description"><?= (int)($wishlistCount ?? 0) ?> item<?= ($wishlistCount ?? 0) !== 1 ? 's' : '' ?></p>
        </div>
        <a href="/shop" class="product-back-link">← Continue Shopping</a>
    </div>

    <?php if (empty($products)): ?>
        <div class="cart-empty">
            <div class="cart-empty-icon">💔</div>
            <h2 class="cart-empty-title">Your wishlist is empty</h2>
            <p class="cart-empty-text">Browse our shop and save products you love!</p>
            <a href="/shop" class="cart-empty-btn">Browse Products</a>
        </div>
    <?php else: ?>
        <?php
        require_once CMS_ROOT . '/core/shop-reviews.php';
        $productRatings = [];
        foreach ($products as $p) {
            $productRatings[(int)$p['id']] = \ShopReviews::getProductRating((int)$p['id']);
        }
        ?>
        <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <?php $pr = $productRatings[(int)$p['id']] ?? ['average' => 0, 'count' => 0]; ?>
            <div class="product-card">
                <!-- Remove from wishlist -->
                <button type="button" class="wishlist-remove-btn shop-wish-btn" data-id="<?= (int)$p['id'] ?>" title="Remove from wishlist">❤️</button>

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
                <div class="wishlist-actions">
                    <button type="button" class="wishlist-add-cart-btn add-to-cart-btn" data-id="<?= (int)$p['id'] ?>" <?= (int)$p['stock'] === 0 ? 'disabled class="wishlist-add-cart-btn add-to-cart-btn disabled"' : '' ?>>
                        <?= (int)$p['stock'] === 0 ? 'Out of Stock' : '🛒 Add to Cart' ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="shop-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/shop/wishlist?page=<?= $i ?>" class="shop-pagination-link<?= $i === $currentPage ? ' active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function(){
    // Remove from wishlist
    document.querySelectorAll('.wishlist-remove-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            var card = this.closest('.product-card');
            var fd = new FormData();
            fd.append('product_id', id);
            fetch('/shop/wishlist/remove', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        if (card) card.style.display = 'none';
                        var countEl = document.querySelector('.shop-description');
                        if (countEl && typeof d.count !== 'undefined') {
                            countEl.textContent = d.count + ' item' + (d.count !== 1 ? 's' : '');
                        }
                        if (d.count === 0) location.reload();
                    }
                });
        });
    });

    // Add to cart from wishlist
    document.querySelectorAll('.wishlist-add-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            var id = this.getAttribute('data-id');
            var el = this;
            var fd = new FormData();
            fd.append('product_id', id);
            fd.append('quantity', '1');
            el.textContent = 'Adding...';
            fetch('/cart/add', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        el.textContent = '✓ Added!';
                        el.style.background = '#10b981';
                        setTimeout(function() { el.textContent = '🛒 Add to Cart'; el.style.background = ''; }, 2000);
                    } else {
                        el.textContent = d.message || 'Error';
                        setTimeout(function() { el.textContent = '🛒 Add to Cart'; }, 2000);
                    }
                });
        });
    });
})();
</script>

<style>
/* Wishlist fallback styles */
.shop-page { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
.wishlist-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.shop-title { font-size: 2rem; margin: 0 0 4px; }
.shop-description { color: #666; margin: 0; }
.product-back-link { color: var(--primary-color, #6366f1); text-decoration: none; font-size: .9rem; }
.cart-empty { text-align: center; padding: 80px 20px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0; }
.cart-empty-icon { font-size: 4rem; margin-bottom: 16px; }
.cart-empty-title { font-size: 1.3rem; color: #333; margin: 0 0 8px; }
.cart-empty-text { color: #666; margin: 0 0 24px; }
.cart-empty-btn { display: inline-block; padding: 12px 28px; background: var(--primary-color, #6366f1); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
.product-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; position: relative; }
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.12); }
.product-card-link { text-decoration: none; color: inherit; display: block; }
.product-card-image img { width: 100%; height: 200px; object-fit: cover; }
.product-card-placeholder { width: 100%; height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 3rem; }
.product-card-body { padding: 16px; }
.product-card-title { margin: 0 0 8px; font-size: 1rem; }
.product-card-rating { margin-bottom: 8px; font-size: .8rem; display: flex; align-items: center; gap: 4px; }
.product-card-rating .stars { color: #f59e0b; letter-spacing: 1px; }
.product-card-rating .count { color: #999; }
.product-card-price { font-weight: 700; font-size: 1.1rem; color: var(--primary-color, #6366f1); }
.product-card-price .original { text-decoration: line-through; color: #999; font-size: .85rem; font-weight: 400; }
.shop-wish-btn { position: absolute; top: 10px; right: 10px; z-index: 2; background: rgba(255,255,255,.9); border: none; border-radius: 50%; width: 36px; height: 36px; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
.wishlist-actions { padding: 0 16px 16px; }
.wishlist-add-cart-btn { width: 100%; padding: 10px; background: var(--primary-color, #6366f1); color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-weight: 600; cursor: pointer; }
.wishlist-add-cart-btn.disabled { opacity: .5; cursor: not-allowed; }
.shop-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
.shop-pagination-link { padding: 8px 14px; border-radius: 6px; text-decoration: none; background: #f1f5f9; color: #333; }
.shop-pagination-link.active { background: var(--primary-color, #6366f1); color: #fff; }
</style>
