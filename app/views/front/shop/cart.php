<div class="cart-page">
    <h1 class="cart-title">🛒 Shopping Cart</h1>

    <?php if (empty($cart['items'])): ?>
        <div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <p class="cart-empty-text">Your cart is empty.</p>
            <a href="/shop" class="cart-empty-btn">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-table">
            <table>
                <thead>
                    <tr>
                        <th class="cart-th-product">Product</th>
                        <th class="cart-th-qty">Qty</th>
                        <th class="cart-th-price">Price</th>
                        <th class="cart-th-total">Total</th>
                        <th class="cart-th-remove"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart['items'] as $item): ?>
                    <tr class="cart-item">
                        <td class="cart-item-details">
                            <div class="cart-item-info">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>" class="cart-item-image" alt="">
                                <?php endif; ?>
                                <div>
                                    <a href="/shop/<?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>" class="cart-item-name"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></a>
                                </div>
                            </div>
                        </td>
                        <td class="cart-item-quantity">
                            <input type="number" value="<?= (int)$item['quantity'] ?>" min="1" class="cart-qty-input" onchange="updateQty(<?= (int)$item['product_id'] ?>, this.value)">
                        </td>
                        <td class="cart-item-price"><?= \Shop::formatPrice($item['effective_price']) ?></td>
                        <td class="cart-item-total"><?= \Shop::formatPrice($item['line_total']) ?></td>
                        <td class="cart-item-remove">
                            <button onclick="removeItem(<?= (int)$item['product_id'] ?>)" class="cart-remove-btn" title="Remove">✕</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-footer">
            <div class="cart-actions">
                <a href="/shop" class="cart-continue-link">← Continue Shopping</a>
            </div>
            <div class="cart-summary">
                <h3 class="cart-summary-title">Order Summary</h3>
                <div class="cart-totals">
                    <div class="cart-totals-row"><span>Subtotal</span><span><?= \Shop::formatPrice($cart['subtotal']) ?></span></div>
                    <?php if ($cart['tax'] > 0): ?>
                    <div class="cart-totals-row"><span>Tax</span><span><?= \Shop::formatPrice($cart['tax']) ?></span></div>
                    <?php endif; ?>
                    <div class="cart-totals-row"><span>Shipping</span><span><?= $cart['shipping'] > 0 ? \Shop::formatPrice($cart['shipping']) : 'Free' ?></span></div>
                    <div class="cart-totals-row cart-totals-total"><span>Total</span><span><?= \Shop::formatPrice($cart['total']) ?></span></div>
                </div>
                <a href="/checkout" class="checkout-btn">Proceed to Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQty(pid, qty) {
    var fd = new FormData();
    fd.append('product_id', pid);
    fd.append('quantity', qty);
    fetch('/cart/update', {method:'POST', body:fd})
        .then(function(r){return r.json()})
        .then(function(d){if(d.success) location.reload()});
}
function removeItem(pid) {
    var fd = new FormData();
    fd.append('product_id', pid);
    fetch('/cart/remove', {method:'POST', body:fd})
        .then(function(r){return r.json()})
        .then(function(d){if(d.success) location.reload()});
}
</script>

<style>
/* Cart fallback styles */
.cart-page { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.cart-title { font-size: 2rem; margin-bottom: 30px; }
.cart-empty { text-align: center; padding: 60px 0; }
.cart-empty-icon { font-size: 4rem; margin-bottom: 16px; }
.cart-empty-text { color: #666; font-size: 1.1rem; margin-bottom: 20px; }
.cart-empty-btn { display: inline-block; padding: 12px 24px; background: var(--primary-color, #6366f1); color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; }
.cart-table { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 30px; }
.cart-table table { width: 100%; border-collapse: collapse; font-size: .9rem; }
.cart-table thead tr { background: #f8fafc; }
.cart-table th { padding: 12px 16px; text-align: left; font-weight: 600; color: #666; font-size: .8rem; }
.cart-th-qty, .cart-th-price, .cart-th-total { text-align: center; }
.cart-th-remove { width: 50px; }
.cart-item td { padding: 12px 16px; border-top: 1px solid #e2e8f0; }
.cart-item-info { display: flex; align-items: center; gap: 12px; }
.cart-item-image { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; }
.cart-item-name { color: inherit; text-decoration: none; font-weight: 600; }
.cart-item-quantity { text-align: center; }
.cart-qty-input { width: 60px; padding: 6px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: center; }
.cart-item-price { text-align: right; }
.cart-item-total { text-align: right; font-weight: 600; }
.cart-item-remove { text-align: center; }
.cart-remove-btn { background: none; border: none; cursor: pointer; font-size: 1.1rem; color: #ef4444; }
.cart-footer { display: grid; grid-template-columns: 1fr 300px; gap: 30px; align-items: start; }
.cart-continue-link { color: var(--primary-color, #6366f1); text-decoration: none; font-size: .9rem; }
.cart-summary { background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; }
.cart-summary-title { margin: 0 0 16px; font-size: 1rem; }
.cart-totals-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: .9rem; }
.cart-totals-row span:first-child { color: #666; }
.cart-totals-total { font-size: 1.1rem; font-weight: 700; border-top: 2px solid #e2e8f0; padding-top: 12px; margin-top: 12px; }
.cart-totals-total span:last-child { color: var(--primary-color, #6366f1); }
.checkout-btn { display: block; text-align: center; padding: 14px; background: var(--primary-color, #6366f1); color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 20px; font-size: 1rem; border: none; width: 100%; cursor: pointer; }
@media (max-width: 768px) {
    .cart-footer { grid-template-columns: 1fr; }
}
</style>
