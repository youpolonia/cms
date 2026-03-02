<?php
// Load payment gateway
require_once CMS_ROOT . '/core/payment-gateway.php';
$paymentMethods = PaymentGateway::getAvailableMethods();
$stripeKey = PaymentGateway::getStripePublicKey();
$paypalClientId = PaymentGateway::getPayPalClientId();
$paypalMode = PaymentGateway::getPayPalMode();
?>
<div class="checkout-page">
    <h1 class="checkout-title">✅ Checkout</h1>

    <form method="post" action="/checkout" class="checkout-layout" id="checkoutForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="checkout-form">
            <div class="checkout-section">
                <h2 class="checkout-section-title">Contact Information</h2>
                <div class="checkout-field">
                    <label>Full Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="checkout-field-row">
                    <div class="checkout-field">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="checkout-field">
                        <label>Phone</label>
                        <input type="tel" name="phone">
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="checkout-section-title">Billing Address</h2>
                <div class="checkout-field">
                    <label>Address Line 1</label>
                    <input type="text" name="address_line1">
                </div>
                <div class="checkout-field">
                    <label>Address Line 2</label>
                    <input type="text" name="address_line2">
                </div>
                <div class="checkout-field-row">
                    <div class="checkout-field">
                        <label>City</label>
                        <input type="text" name="city">
                    </div>
                    <div class="checkout-field">
                        <label>State/Region</label>
                        <input type="text" name="state">
                    </div>
                </div>
                <div class="checkout-field-row">
                    <div class="checkout-field">
                        <label>ZIP/Postal Code</label>
                        <input type="text" name="zip">
                    </div>
                    <div class="checkout-field">
                        <label>Country</label>
                        <input type="text" name="country">
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="checkout-section-title">Payment Method</h2>
                <div class="checkout-payment-methods">
                    <?php foreach ($paymentMethods as $i => $pm): ?>
                    <label class="checkout-payment-option<?= $i === 0 ? ' active' : '' ?>">
                        <input type="radio" name="payment_method" value="<?= htmlspecialchars($pm['id']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                        <span class="checkout-payment-icon"><?= $pm['icon'] ?></span>
                        <span class="checkout-payment-info">
                            <strong><?= htmlspecialchars($pm['name']) ?></strong>
                            <small><?= htmlspecialchars($pm['description']) ?></small>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div id="bankInstructions" class="checkout-payment-details" style="display:none">
                    <div class="checkout-info-box">
                        <?= nl2br(htmlspecialchars(get_setting('payment_bank_instructions', 'Transfer details will be provided after placing the order.'))) ?>
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="checkout-section-title">Order Notes</h2>
                <div class="checkout-field">
                    <textarea name="notes" rows="3" placeholder="Special instructions..."></textarea>
                </div>
            </div>
        </div>

        <div class="checkout-sidebar">
            <div class="checkout-summary">
                <h3 class="checkout-summary-title">Order Summary</h3>
                <div class="checkout-items">
                    <?php foreach ($cart['items'] as $item): ?>
                    <div class="checkout-item-row">
                        <span class="checkout-item-name"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?> × <?= (int)$item['quantity'] ?></span>
                        <span class="checkout-item-price"><?= \Shop::formatPrice($item['line_total']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="checkout-totals">
                    <div class="checkout-totals-row"><span>Subtotal</span><span><?= \Shop::formatPrice($cart['subtotal']) ?></span></div>
                    <?php if ($cart['tax'] > 0): ?>
                    <div class="checkout-totals-row"><span>Tax</span><span><?= \Shop::formatPrice($cart['tax']) ?></span></div>
                    <?php endif; ?>
                    <div class="checkout-totals-row"><span>Shipping</span><span><?= $cart['shipping'] > 0 ? \Shop::formatPrice($cart['shipping']) : 'Free' ?></span></div>
                </div>
                <div class="checkout-total-row"><span>Total</span><span><?= \Shop::formatPrice($cart['total']) ?></span></div>
                <button type="submit" class="checkout-btn" id="checkoutBtn">
                    <span id="btnText">Place Order</span>
                    <span id="btnSpinner" style="display:none">Processing...</span>
                </button>
                <a href="/cart" class="checkout-back-link">← Back to Cart</a>
            </div>
        </div>
    </form>
</div>

<style>
.checkout-page { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.checkout-title { font-size: 2rem; margin-bottom: 30px; }
.checkout-layout { display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start; }
.checkout-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.checkout-section-title { font-size: 1.1rem; margin: 0 0 20px; }
.checkout-field { margin-bottom: 14px; }
.checkout-field label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 6px; }
.checkout-field input, .checkout-field select, .checkout-field textarea { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .9rem; box-sizing: border-box; font-family: inherit; }
.checkout-field textarea { resize: vertical; }
.checkout-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.checkout-sidebar { position: sticky; top: 20px; }
.checkout-summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
.checkout-summary-title { margin: 0 0 16px; font-size: 1rem; }
.checkout-item-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: .85rem; }
.checkout-item-name { color: #666; }
.checkout-totals { border-top: 1px solid #e2e8f0; margin: 12px 0; padding-top: 12px; }
.checkout-totals-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: .85rem; }
.checkout-totals-row span:first-child { color: #666; }
.checkout-total-row { display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 700; border-top: 2px solid #e2e8f0; padding-top: 12px; }
.checkout-total-row span:last-child { color: var(--primary-color, #6366f1); }
.checkout-btn { display: block; width: 100%; padding: 14px; background: var(--primary-color, #6366f1); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; margin-top: 20px; transition: .15s; }
.checkout-btn:hover { opacity: .9; }
.checkout-btn:disabled { opacity: .6; cursor: not-allowed; }
.checkout-back-link { display: block; text-align: center; color: var(--primary-color, #6366f1); text-decoration: none; font-size: .85rem; margin-top: 12px; }

/* Payment methods */
.checkout-payment-methods { display: flex; flex-direction: column; gap: 10px; }
.checkout-payment-option { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: .15s; }
.checkout-payment-option:hover { border-color: #cbd5e1; }
.checkout-payment-option.active { border-color: var(--primary-color, #6366f1); background: rgba(99,102,241,.04); }
.checkout-payment-option input[type="radio"] { display: none; }
.checkout-payment-icon { font-size: 1.5rem; width: 36px; text-align: center; }
.checkout-payment-info { display: flex; flex-direction: column; }
.checkout-payment-info strong { font-size: .9rem; }
.checkout-payment-info small { color: #64748b; font-size: .75rem; margin-top: 2px; }
.checkout-info-box { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; font-size: .85rem; color: #92400e; margin-top: 14px; }
.checkout-payment-details { margin-top: 10px; }

@media (max-width: 768px) {
    .checkout-layout { grid-template-columns: 1fr; }
    .checkout-field-row { grid-template-columns: 1fr; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const options = document.querySelectorAll('.checkout-payment-option');
    const bankBox = document.getElementById('bankInstructions');
    const btn = document.getElementById('checkoutBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    options.forEach(opt => {
        opt.addEventListener('click', function() {
            options.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            // Show/hide bank instructions
            if (bankBox) {
                bankBox.style.display = radio.value === 'bank_transfer' ? 'block' : 'none';
            }

            // Update button text for online vs offline
            const isOnline = (radio.value === 'stripe' || radio.value === 'paypal');
            btnText.textContent = isOnline ? 'Pay Now' : 'Place Order';
        });
    });

    // Form submit — show spinner
    document.getElementById('checkoutForm').addEventListener('submit', function() {
        btn.disabled = true;
        btnText.style.display = 'none';
        btnSpinner.style.display = 'inline';
    });

    // Initialize
    const checked = document.querySelector('.checkout-payment-option input:checked');
    if (checked && checked.value === 'bank_transfer' && bankBox) {
        bankBox.style.display = 'block';
    }
});
</script>
