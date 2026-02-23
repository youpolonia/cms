<div class="shop-page">
    <div class="shop-header">
        <a href="/shop" class="product-back-link">← All Products</a>
        <h1 class="shop-title"><?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="shop-description"><?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>

    <?php if (empty($products)): ?>
        <p class="shop-empty">No products in this category.</p>
    <?php else: ?>
        <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <a href="/shop/<?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card product-card-link">
                <?php if (!empty($p['image'])): ?>
                    <div class="product-card-image"><img src="<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                <?php else: ?>
                    <div class="product-card-image"><div class="product-card-placeholder">📦</div></div>
                <?php endif; ?>
                <div class="product-card-body">
                    <h3 class="product-card-title"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
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
        <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="shop-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $qs = $_GET; $qs['page'] = $i; ?>
                <a href="/shop/category/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($qs) ?>" class="shop-pagination-link<?= $i === $currentPage ? ' active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* Category page shares shop-page styles — fallback only if no theme loaded */
.shop-page { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
.shop-header { margin-bottom: 30px; }
.shop-title { font-size: 2rem; margin-bottom: 8px; }
.shop-description { color: #666; }
.shop-empty { text-align: center; padding: 60px 0; color: #999; }
.product-back-link { color: var(--primary-color, #6366f1); text-decoration: none; font-size: .85rem; margin-bottom: 12px; display: inline-block; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
.product-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: transform .2s; }
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.12); }
.product-card-link { text-decoration: none; color: inherit; display: block; }
.product-card-image img { width: 100%; height: 200px; object-fit: cover; }
.product-card-placeholder { width: 100%; height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 3rem; }
.product-card-body { padding: 16px; }
.product-card-title { margin: 0 0 8px; font-size: 1rem; }
.product-card-price { font-weight: 700; color: var(--primary-color, #6366f1); }
.product-card-price .original { text-decoration: line-through; color: #999; font-size: .85rem; font-weight: 400; }
.shop-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
.shop-pagination-link { padding: 8px 14px; border-radius: 6px; text-decoration: none; background: #f1f5f9; color: #333; }
.shop-pagination-link.active { background: var(--primary-color, #6366f1); color: #fff; }
</style>
