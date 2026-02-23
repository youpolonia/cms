<div class="product-page">
    <a href="/shop" class="product-back-link">← Back to Shop</a>

    <div class="product-layout">
        <div class="product-gallery">
            <?php if (!empty($product['image'])): ?>
                <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>" class="product-image">
            <?php else: ?>
                <div class="product-image-placeholder">📦</div>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <?php if (!empty($product['category_name'])): ?>
                <span class="product-category"><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <h1 class="product-title"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if (!empty($product['short_description'])): ?>
                <p class="product-description"><?= htmlspecialchars($product['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div id="product-price-display" class="product-price">
                <?php if ($product['sale_price'] !== null && (float)$product['sale_price'] > 0): ?>
                    <span class="original"><?= \Shop::formatPrice((float)$product['price']) ?></span>
                    <span class="sale"><?= \Shop::formatPrice((float)$product['sale_price']) ?></span>
                <?php else: ?>
                    <?= \Shop::formatPrice((float)$product['price']) ?>
                <?php endif; ?>
            </div>

            <div class="product-meta">
                <?php if ((int)$product['stock'] === -1): ?>
                    <span class="product-stock in-stock">✓ In Stock</span>
                <?php elseif ((int)$product['stock'] > 0): ?>
                    <span class="product-stock in-stock">✓ <?= (int)$product['stock'] ?> in stock</span>
                <?php else: ?>
                    <span class="product-stock out-of-stock">✗ Out of stock</span>
                <?php endif; ?>
                <?php if (!empty($product['sku'])): ?>
                    <span class="product-sku">SKU: <?= htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <?php
            // Load variants
            require_once CMS_ROOT . '/core/shop-variants.php';
            $productVariants = \ShopVariants::getForProduct((int)$product['id']);
            $activeVariants = array_filter($productVariants, fn($v) => ($v['status'] ?? 'active') === 'active');
            ?>

            <?php if (!empty($activeVariants)): ?>
            <!-- Variant Selector -->
            <div id="variant-selector" class="variant-swatches">
                <?php
                // Group options by name for swatches
                $optionGroups = [];
                foreach ($activeVariants as $v) {
                    $opts = $v['options'] ?? [];
                    foreach ($opts as $opt) {
                        $oName = $opt['name'] ?? '';
                        $oValue = $opt['value'] ?? '';
                        if ($oName !== '' && $oValue !== '') {
                            if (!isset($optionGroups[$oName])) $optionGroups[$oName] = [];
                            if (!in_array($oValue, $optionGroups[$oName])) {
                                $optionGroups[$oName][] = $oValue;
                            }
                        }
                    }
                }
                ?>
                <?php if (!empty($optionGroups)): ?>
                    <?php foreach ($optionGroups as $groupName => $groupValues): ?>
                    <div class="variant-group">
                        <label class="variant-group-label"><?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="variant-swatch-list" data-group="<?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ($groupValues as $val): ?>
                            <button type="button" class="variant-swatch" data-group="<?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?>" data-value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- No option groups — show dropdown -->
                    <div class="variant-group">
                        <label class="variant-group-label">Variant</label>
                        <select id="variant-dropdown" class="variant-dropdown">
                            <option value="">Select variant...</option>
                            <?php foreach ($activeVariants as $v): ?>
                            <option value="<?= (int)$v['id'] ?>" data-price="<?= (float)($v['price'] ?? 0) ?>" data-sale-price="<?= (float)($v['sale_price'] ?? 0) ?>" data-stock="<?= (int)$v['stock'] ?>"><?= htmlspecialchars($v['variant_name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div id="variant-stock-msg" class="variant-stock-msg"></div>
            </div>
            <script>
            var _variants = <?= json_encode(array_values($activeVariants)) ?>;
            var _productPrice = <?= (float)$product['price'] ?>;
            var _productSalePrice = <?= (float)($product['sale_price'] ?? 0) ?>;
            </script>
            <?php endif; ?>

            <?php if ((int)$product['stock'] !== 0): ?>
            <form id="add-to-cart-form" class="product-actions">
                <input type="hidden" id="variant-id-input" name="variant_id" value="">
                <input type="number" id="qty-input" value="1" min="1" class="product-qty-input">
                <button type="submit" class="add-to-cart-btn">🛒 Add to Cart</button>
                <?php
                $inWishlist = false;
                if (session_status() === PHP_SESSION_ACTIVE && !empty(session_id())) {
                    require_once CMS_ROOT . '/core/shop-wishlist.php';
                    $inWishlist = \ShopWishlist::isInWishlist(session_id(), (int)$product['id']);
                }
                ?>
                <button type="button" id="wishlist-toggle" class="product-wishlist-btn<?= $inWishlist ? ' active' : '' ?>" data-id="<?= (int)$product['id'] ?>" title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>"><?= $inWishlist ? '❤️' : '🤍' ?></button>
            </form>
            <div id="cart-msg" class="cart-msg"></div>
            <?php endif; ?>

            <?php if (!empty($product['description'])): ?>
                <div class="product-description-full">
                    <?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ REVIEWS SECTION ═══ -->
    <?php
    $ratingData = $ratingData ?? ['average' => 0, 'count' => 0, 'distribution' => [5=>0,4=>0,3=>0,2=>0,1=>0]];
    $reviewsData = $reviewsData ?? ['reviews' => [], 'total' => 0, 'page' => 1, 'totalPages' => 1];
    $productReviews = $reviewsData['reviews'];
    $reviewTotal = $ratingData['count'];
    $reviewAvg = $ratingData['average'];
    $dist = $ratingData['distribution'];
    ?>
    <div id="reviews-section" class="reviews-section">
        <h2 class="reviews-title">Customer Reviews</h2>

        <div class="review-summary">
            <!-- Average Rating -->
            <div class="review-average">
                <div class="review-average-number"><?= $reviewTotal > 0 ? number_format($reviewAvg, 1) : '—' ?></div>
                <div class="review-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= round($reviewAvg) ? '★' : '☆' ?>
                    <?php endfor; ?>
                </div>
                <div class="review-count"><?= $reviewTotal ?> review<?= $reviewTotal !== 1 ? 's' : '' ?></div>
            </div>

            <!-- Rating Distribution -->
            <div class="review-distribution">
                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php $cnt = $dist[$star] ?? 0; $pct = $reviewTotal > 0 ? round($cnt / $reviewTotal * 100) : 0; ?>
                    <div class="review-bar">
                        <span class="review-bar-label"><?= $star ?> star</span>
                        <div class="review-bar-track">
                            <div class="review-bar-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="review-bar-pct"><?= $pct ?>%</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Review List -->
        <?php if (empty($productReviews)): ?>
            <p class="review-empty">No reviews yet. Be the first to review this product!</p>
        <?php else: ?>
            <div class="review-list">
                <?php foreach ($productReviews as $rv): ?>
                    <div class="review-item">
                        <div class="review-item-header">
                            <div>
                                <span class="review-item-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= (int)$rv['rating'] ? '★' : '☆' ?>
                                    <?php endfor; ?>
                                </span>
                                <?php if (!empty($rv['title'])): ?>
                                    <strong class="review-item-title"><?= htmlspecialchars($rv['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php endif; ?>
                            </div>
                            <span class="review-item-date"><?= date('M j, Y', strtotime($rv['created_at'])) ?></span>
                        </div>
                        <?php if (!empty($rv['review_text'])): ?>
                            <p class="review-item-text"><?= nl2br(htmlspecialchars($rv['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <div class="review-item-footer">
                            <span class="review-item-author">— <?= htmlspecialchars($rv['customer_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!empty($rv['is_verified_purchase'])): ?>
                                <span class="review-verified">✓ Verified Purchase</span>
                            <?php endif; ?>
                            <button type="button" class="helpful-btn" data-id="<?= (int)$rv['id'] ?>">
                                👍 Helpful (<?= (int)$rv['helpful_count'] ?>)
                            </button>
                        </div>
                        <?php if (!empty($rv['admin_reply'])): ?>
                            <div class="review-admin-reply">
                                <strong class="review-admin-reply-label">Store Reply:</strong>
                                <p><?= nl2br(htmlspecialchars($rv['admin_reply'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($reviewsData['totalPages'] > 1): ?>
            <div class="shop-pagination">
                <?php for ($i = 1; $i <= $reviewsData['totalPages']; $i++): ?>
                    <a href="?review_page=<?= $i ?>#reviews-section" class="shop-pagination-link<?= $i === $reviewsData['page'] ? ' active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Write a Review Form -->
        <div class="review-form">
            <h3 class="review-form-title">Write a Review</h3>
            <form id="review-form">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                <div class="review-form-row">
                    <div class="review-form-field">
                        <label>Your Name *</label>
                        <input type="text" name="customer_name" required>
                    </div>
                    <div class="review-form-field">
                        <label>Email *</label>
                        <input type="email" name="customer_email" required>
                    </div>
                </div>

                <div class="review-form-field">
                    <label>Rating *</label>
                    <div id="star-picker" class="star-picker">
                        <span data-val="1">★</span>
                        <span data-val="2">★</span>
                        <span data-val="3">★</span>
                        <span data-val="4">★</span>
                        <span data-val="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="0">
                </div>

                <div class="review-form-field">
                    <label>Review Title</label>
                    <input type="text" name="title">
                </div>

                <div class="review-form-field">
                    <label>Your Review *</label>
                    <textarea name="review_text" required rows="4"></textarea>
                </div>

                <button type="submit" id="review-submit-btn" class="review-submit-btn">Submit Review</button>
                <div id="review-msg" class="review-msg"></div>
            </form>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="related-products">
        <h2 class="related-products-title">Related Products</h2>
        <div class="product-grid product-grid--related">
        <?php foreach ($related as $rp): ?>
            <a href="/shop/<?= htmlspecialchars($rp['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card product-card-link">
                <?php if (!empty($rp['image'])): ?>
                    <div class="product-card-image"><img src="<?= htmlspecialchars($rp['image'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"></div>
                <?php else: ?>
                    <div class="product-card-image"><div class="product-card-placeholder">📦</div></div>
                <?php endif; ?>
                <div class="product-card-body">
                    <h3 class="product-card-title"><?= htmlspecialchars($rp['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="product-card-price"><?= \Shop::formatPrice(\Shop::getEffectivePrice($rp)) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(function(){
    // ─── Variant Selector ───
    var selectedOptions = {};
    var variantIdInput = document.getElementById('variant-id-input');

    // Swatch click handlers
    document.querySelectorAll('.variant-swatch').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var group = this.getAttribute('data-group');
            var value = this.getAttribute('data-value');
            var siblings = document.querySelectorAll('.variant-swatch[data-group="' + group + '"]');
            siblings.forEach(function(s) { s.classList.remove('active'); });
            this.classList.add('active');
            selectedOptions[group] = value;
            matchVariant();
        });
    });

    // Dropdown handler
    var dd = document.getElementById('variant-dropdown');
    if (dd) {
        dd.addEventListener('change', function() {
            var vid = this.value;
            if (variantIdInput) variantIdInput.value = vid;
            if (vid && typeof _variants !== 'undefined') {
                var v = _variants.find(function(x) { return String(x.id) === String(vid); });
                if (v) updatePriceDisplay(v);
            }
        });
    }

    function matchVariant() {
        if (typeof _variants === 'undefined') return;
        var matched = null;
        for (var i = 0; i < _variants.length; i++) {
            var v = _variants[i];
            var opts = v.options || [];
            var match = true;
            var keys = Object.keys(selectedOptions);
            for (var k = 0; k < keys.length; k++) {
                var found = false;
                for (var j = 0; j < opts.length; j++) {
                    if (opts[j].name === keys[k] && opts[j].value === selectedOptions[keys[k]]) {
                        found = true; break;
                    }
                }
                if (!found) { match = false; break; }
            }
            if (match && keys.length > 0) { matched = v; break; }
        }
        if (matched) {
            if (variantIdInput) variantIdInput.value = matched.id;
            updatePriceDisplay(matched);
        } else {
            if (variantIdInput) variantIdInput.value = '';
        }
    }

    function updatePriceDisplay(variant) {
        var stockMsg = document.getElementById('variant-stock-msg');
        if (stockMsg) {
            var st = parseInt(variant.stock);
            if (st === -1) {
                stockMsg.style.display = 'block';
                stockMsg.innerHTML = '<span class="product-stock in-stock">✓ In Stock</span>';
            } else if (st > 0) {
                stockMsg.style.display = 'block';
                stockMsg.innerHTML = '<span class="product-stock in-stock">✓ ' + st + ' in stock</span>';
            } else {
                stockMsg.style.display = 'block';
                stockMsg.innerHTML = '<span class="product-stock out-of-stock">✗ Out of stock</span>';
            }
            if (variant.sku) {
                stockMsg.innerHTML += ' <span class="product-sku">SKU: ' + variant.sku + '</span>';
            }
        }
        var priceEl = document.getElementById('product-price-display');
        if (!priceEl) return;
        var vp = parseFloat(variant.price) || 0;
        var vsp = parseFloat(variant.sale_price) || 0;
        var pp = typeof _productPrice !== 'undefined' ? _productPrice : 0;
        var psp = typeof _productSalePrice !== 'undefined' ? _productSalePrice : 0;
        var displayPrice = vp > 0 ? vp : pp;
        var displaySale = vsp > 0 ? vsp : (vp > 0 ? 0 : psp);

        if (displaySale > 0) {
            priceEl.innerHTML = '<span class="original">' + formatMoney(displayPrice) + '</span> <span class="sale">' + formatMoney(displaySale) + '</span>';
        } else {
            priceEl.innerHTML = formatMoney(displayPrice);
        }
    }

    function formatMoney(n) {
        return '<?= htmlspecialchars(\Shop::formatPrice(0), ENT_QUOTES, 'UTF-8')[0] ?? '$' ?>' + parseFloat(n).toFixed(2);
    }

    // Add to cart
    var form = document.getElementById('add-to-cart-form');
    if (form) {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var qty = document.getElementById('qty-input').value;
            var msg = document.getElementById('cart-msg');
            var vid = variantIdInput ? variantIdInput.value : '';
            var fd = new FormData();
            fd.append('product_id', '<?= (int)$product['id'] ?>');
            fd.append('quantity', qty);
            if (vid) fd.append('variant_id', vid);
            fetch('/cart/add', {method:'POST', body:fd})
                .then(function(r){return r.json()})
                .then(function(d){
                    msg.style.display='block';
                    if(d.success){msg.className='cart-msg success';msg.textContent='✓ '+d.message}
                    else{msg.className='cart-msg error';msg.textContent='✗ '+d.message}
                    setTimeout(function(){msg.style.display='none'},3000);
                })
                .catch(function(){msg.style.display='block';msg.className='cart-msg error';msg.textContent='Error adding to cart';});
        });
    }

    // Star picker
    var picker = document.getElementById('star-picker');
    var ratingInput = document.getElementById('rating-input');
    var stars = picker ? picker.querySelectorAll('span') : [];
    var currentRating = 0;

    function highlightStars(n) {
        stars.forEach(function(s, i) {
            s.style.color = i < n ? '#f59e0b' : '#d1d5db';
        });
    }

    stars.forEach(function(s) {
        s.addEventListener('mouseenter', function() {
            highlightStars(parseInt(this.getAttribute('data-val')));
        });
        s.addEventListener('click', function() {
            currentRating = parseInt(this.getAttribute('data-val'));
            ratingInput.value = currentRating;
            highlightStars(currentRating);
        });
    });

    if (picker) {
        picker.addEventListener('mouseleave', function() {
            highlightStars(currentRating);
        });
    }

    // Review form AJAX
    var reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var msg = document.getElementById('review-msg');
            var btn = document.getElementById('review-submit-btn');

            if (parseInt(ratingInput.value) < 1) {
                msg.style.display = 'block';
                msg.className = 'review-msg error';
                msg.textContent = 'Please select a rating.';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Submitting...';

            var fd = new FormData(reviewForm);
            fetch('/shop/review/submit', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    msg.style.display = 'block';
                    if (d.success) {
                        msg.className = 'review-msg success';
                        msg.textContent = d.message || 'Review submitted! It will appear after moderation.';
                        reviewForm.reset();
                        currentRating = 0;
                        highlightStars(0);
                        ratingInput.value = '0';
                    } else {
                        msg.className = 'review-msg error';
                        msg.textContent = d.message || 'Failed to submit review.';
                    }
                    btn.disabled = false;
                    btn.textContent = 'Submit Review';
                })
                .catch(function() {
                    msg.style.display = 'block';
                    msg.className = 'review-msg error';
                    msg.textContent = 'Network error. Please try again.';
                    btn.disabled = false;
                    btn.textContent = 'Submit Review';
                });
        });
    }

    // Helpful buttons
    document.querySelectorAll('.helpful-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var el = this;
            fetch('/shop/review/' + id + '/helpful', { method: 'POST' })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        el.textContent = '👍 Helpful (' + d.count + ')';
                        el.disabled = true;
                        el.style.opacity = '.6';
                    }
                });
        });
    });

    // Wishlist toggle
    var wishBtn = document.getElementById('wishlist-toggle');
    if (wishBtn) {
        wishBtn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var el = this;
            var fd = new FormData();
            fd.append('product_id', id);
            fetch('/shop/wishlist/toggle', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        if (d.added) {
                            el.textContent = '❤️';
                            el.classList.add('active');
                            el.title = 'Remove from wishlist';
                        } else {
                            el.textContent = '🤍';
                            el.classList.remove('active');
                            el.title = 'Add to wishlist';
                        }
                    }
                });
        });
    }
})();
</script>

<style>
/* Product page fallback styles */
.product-page { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
.product-back-link { color: var(--primary-color, #6366f1); text-decoration: none; font-size: .85rem; margin-bottom: 20px; display: inline-block; }
.product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 10px; }
.product-image { width: 100%; border-radius: 12px; object-fit: cover; }
.product-image-placeholder { width: 100%; aspect-ratio: 1; background: #f1f5f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 5rem; }
.product-category { font-size: .8rem; color: var(--primary-color, #6366f1); text-transform: uppercase; letter-spacing: .05em; }
.product-title { font-size: 1.8rem; margin: 8px 0 12px; }
.product-description { color: #666; line-height: 1.6; margin-bottom: 20px; }
.product-price { font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; color: var(--primary-color, #6366f1); }
.product-price .original { text-decoration: line-through; color: #999; font-size: 1rem; font-weight: 400; }
.product-price .sale { color: var(--primary-color, #6366f1); }
.product-meta { margin-bottom: 20px; font-size: .9rem; color: #666; }
.product-stock { font-weight: 500; }
.product-stock.in-stock { color: #10b981; }
.product-stock.out-of-stock { color: #ef4444; }
.product-sku { margin-left: 16px; color: #999; }
.variant-swatches { margin-bottom: 20px; }
.variant-group { margin-bottom: 12px; }
.variant-group-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 6px; color: #333; }
.variant-swatch-list { display: flex; flex-wrap: wrap; gap: 8px; }
.variant-swatch { padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; font-size: .85rem; font-weight: 500; transition: all .2s; }
.variant-swatch.active { border-color: var(--primary-color, #6366f1); background: #eef2ff; color: var(--primary-color, #6366f1); }
.variant-dropdown { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .9rem; }
.variant-stock-msg { font-size: .85rem; margin-bottom: 8px; display: none; }
.product-actions { display: flex; gap: 12px; align-items: center; margin-bottom: 30px; }
.product-qty-input { width: 70px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-size: 1rem; }
.add-to-cart-btn { flex: 1; padding: 12px 24px; background: var(--primary-color, #6366f1); color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; }
.product-wishlist-btn { padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-size: 1.2rem; background: #fff; transition: all .2s; }
.product-wishlist-btn.active { background: #fee2e2; }
.cart-msg { display: none; padding: 10px; border-radius: 8px; font-size: .85rem; margin-bottom: 20px; }
.cart-msg.success { background: #d1fae5; color: #065f46; }
.cart-msg.error { background: #fee2e2; color: #991b1b; }
.product-description-full { border-top: 1px solid #e2e8f0; padding-top: 20px; line-height: 1.7; color: #444; }
.reviews-section { margin-top: 60px; border-top: 2px solid #e2e8f0; padding-top: 40px; }
.reviews-title { font-size: 1.4rem; margin-bottom: 24px; }
.review-summary { display: grid; grid-template-columns: 200px 1fr; gap: 30px; margin-bottom: 30px; }
.review-average { text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; }
.review-average-number { font-size: 2.5rem; font-weight: 700; color: var(--primary-color, #6366f1); }
.review-stars { color: #f59e0b; font-size: 1.3rem; letter-spacing: 2px; margin: 6px 0; }
.review-count { font-size: .85rem; color: #666; }
.review-distribution { display: flex; flex-direction: column; gap: 6px; justify-content: center; }
.review-bar { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.review-bar-label { width: 50px; text-align: right; color: #666; }
.review-bar-track { flex: 1; height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden; }
.review-bar-fill { height: 100%; background: #f59e0b; border-radius: 5px; transition: width .3s; }
.review-bar-pct { width: 40px; font-size: .8rem; color: #999; }
.review-empty { text-align: center; padding: 30px; color: #999; background: #f8fafc; border-radius: 12px; }
.review-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 30px; }
.review-item { padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; }
.review-item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
.review-item-stars { color: #f59e0b; letter-spacing: 1px; }
.review-item-title { margin-left: 8px; }
.review-item-date { font-size: .8rem; color: #999; }
.review-item-text { margin: 8px 0; line-height: 1.6; color: #444; }
.review-item-footer { display: flex; align-items: center; gap: 12px; margin-top: 8px; font-size: .8rem; }
.review-item-author { color: #666; }
.review-verified { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 6px; font-size: .7rem; font-weight: 600; }
.helpful-btn { margin-left: auto; background: none; border: 1px solid #e2e8f0; border-radius: 6px; padding: 3px 10px; cursor: pointer; color: #666; font-size: .75rem; }
.review-admin-reply { margin-top: 12px; padding: 12px; background: #eef2ff; border-radius: 8px; border-left: 3px solid var(--primary-color, #6366f1); }
.review-admin-reply-label { font-size: .8rem; color: var(--primary-color, #6366f1); }
.review-admin-reply p { margin: 4px 0 0; font-size: .85rem; color: #444; }
.review-form { margin-top: 20px; padding: 24px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; }
.review-form-title { margin: 0 0 16px; font-size: 1.1rem; }
.review-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
.review-form-field { margin-bottom: 12px; }
.review-form-field label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: 4px; color: #555; }
.review-form-field input, .review-form-field textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .9rem; box-sizing: border-box; }
.review-form-field textarea { resize: vertical; font-family: inherit; }
.star-picker { display: flex; gap: 4px; cursor: pointer; font-size: 1.8rem; color: #d1d5db; user-select: none; }
.star-picker span { cursor: pointer; }
.review-submit-btn { padding: 12px 28px; background: var(--primary-color, #6366f1); color: #fff; border: none; border-radius: 8px; font-size: .95rem; font-weight: 600; cursor: pointer; }
.review-msg { display: none; margin-top: 12px; padding: 10px; border-radius: 8px; font-size: .85rem; }
.review-msg.success { background: #d1fae5; color: #065f46; }
.review-msg.error { background: #fee2e2; color: #991b1b; }
.related-products { margin-top: 60px; }
.related-products-title { font-size: 1.3rem; margin-bottom: 20px; }
.product-grid--related { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
@media (max-width: 768px) {
    .product-layout { grid-template-columns: 1fr; }
    .review-summary { grid-template-columns: 1fr; }
    .review-form-row { grid-template-columns: 1fr; }
}
</style>
