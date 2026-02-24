<?php
/**
 * Jessie Restaurant — Public Menu & Order Page
 * URL: /order or /menu
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-restaurant/includes/class-restaurant-menu.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$menu = RestaurantMenu::getFullMenu();
$settings = RestaurantMenu::getAllSettings();
$name = $settings['restaurant_name'] ?? 'Restaurant';
$sym = $settings['currency_symbol'] ?? '£';
$orderTypes = explode(',', $settings['order_types'] ?? 'delivery,pickup');
$accepting = (bool)($settings['accept_orders'] ?? true);
$deliveryFee = (float)($settings['delivery_fee'] ?? 0);
$minOrder = (float)($settings['min_order_amount'] ?? 0);
$deliveryTime = $settings['estimated_delivery_time'] ?? '30-45';
$pickupTime = $settings['estimated_pickup_time'] ?? '15-20';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($name) ?> — Order Online</title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6;--green:#10b981}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}

        .hero{background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(139,92,246,.1));border-bottom:1px solid var(--border);padding:36px 20px;text-align:center}
        .hero h1{font-size:1.8rem;font-weight:800;margin-bottom:4px}
        .hero p{color:var(--muted);font-size:.9rem}
        .hero .info{display:flex;justify-content:center;gap:20px;margin-top:12px;font-size:.82rem;color:var(--muted)}

        .container{max-width:1000px;margin:0 auto;padding:24px 20px}
        .layout{display:grid;grid-template-columns:1fr 340px;gap:24px}
        @media(max-width:768px){.layout{grid-template-columns:1fr}}

        .cat-nav{display:flex;gap:8px;overflow-x:auto;padding-bottom:12px;margin-bottom:20px;-webkit-overflow-scrolling:touch}
        .cat-btn{background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:8px 16px;border-radius:20px;font-size:.82rem;font-weight:600;cursor:pointer;white-space:nowrap;transition:.15s}
        .cat-btn:hover,.cat-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}

        .cat-section{margin-bottom:28px}
        .cat-section h2{font-size:1.1rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px}

        .menu-item{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:10px;display:flex;gap:14px;transition:.15s;cursor:pointer}
        .menu-item:hover{border-color:var(--accent)}
        .menu-item .info{flex:1}
        .menu-item h3{font-size:.95rem;font-weight:600;margin-bottom:4px}
        .menu-item .desc{font-size:.8rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:6px}
        .menu-item .meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .menu-item .price{font-weight:700;color:var(--green);font-size:.95rem}
        .menu-item .price-old{text-decoration:line-through;color:var(--muted);font-size:.8rem}
        .diet-tag{font-size:.6rem;padding:1px 5px;border-radius:3px;font-weight:700}
        .dt-v{background:rgba(16,185,129,.15);color:#34d399}.dt-vg{background:rgba(52,211,153,.15);color:#6ee7b7}
        .dt-gf{background:rgba(245,158,11,.15);color:#fbbf24}.dt-sp{background:rgba(239,68,68,.15);color:#fca5a5}
        .btn-add{background:var(--accent);color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;white-space:nowrap}

        /* Cart sidebar */
        .cart{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;position:sticky;top:24px}
        .cart h2{font-size:1rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
        .cart-empty{text-align:center;padding:30px 0;color:var(--muted);font-size:.85rem}
        .cart-item{display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid rgba(51,65,85,.4);font-size:.85rem}
        .cart-item:last-child{border-bottom:none}
        .cart-qty{display:flex;align-items:center;gap:4px}
        .cart-qty button{width:22px;height:22px;border-radius:4px;border:1px solid var(--border);background:var(--bg);color:var(--text);cursor:pointer;font-size:.8rem;display:flex;align-items:center;justify-content:center}
        .cart-qty span{min-width:20px;text-align:center;font-weight:600}
        .cart-totals{border-top:1px solid var(--border);padding-top:12px;margin-top:12px}
        .cart-row{display:flex;justify-content:space-between;padding:4px 0;font-size:.82rem}
        .cart-total{font-size:1.05rem;font-weight:700;color:var(--green)}
        .btn-order{width:100%;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px;border-radius:10px;font-weight:700;font-size:.95rem;cursor:pointer;margin-top:12px}
        .btn-order:disabled{opacity:.5;cursor:not-allowed}

        /* Checkout form */
        .checkout-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:100;align-items:center;justify-content:center}
        .checkout-overlay.show{display:flex}
        .checkout-form{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:28px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto}
        .checkout-form h2{margin-bottom:16px}
        .fg{margin-bottom:12px}.fg label{display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;color:var(--text)}
        .fg input,.fg select,.fg textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
        .fg textarea{min-height:60px;resize:vertical}
        .fr2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn-close{background:none;border:none;color:var(--muted);font-size:1.5rem;cursor:pointer;float:right}
        .msg{padding:14px;border-radius:8px;margin-bottom:12px;font-size:.85rem}
        .msg-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#34d399}
        .msg-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
        .closed-banner{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px;border-radius:8px;text-align:center;margin-bottom:16px;font-weight:600}
    </style>
</head>
<body>
    <div class="hero">
        <h1>🍕 <?= h($name) ?></h1>
        <p>Order online for delivery or pickup</p>
        <div class="info">
            <?php if (in_array('delivery', $orderTypes)): ?><span>🚗 Delivery: <?= h($deliveryTime) ?> min</span><?php endif; ?>
            <?php if (in_array('pickup', $orderTypes)): ?><span>🏃 Pickup: <?= h($pickupTime) ?> min</span><?php endif; ?>
            <?php if ($minOrder > 0): ?><span>💰 Min: <?= $sym ?><?= number_format($minOrder, 2) ?></span><?php endif; ?>
        </div>
    </div>

    <div class="container">
        <?php if (!$accepting): ?><div class="closed-banner">❌ We're currently not accepting orders. Please check back later.</div><?php endif; ?>

        <div class="cat-nav">
            <?php foreach ($menu as $cat): if (empty($cat['items'])) continue; ?>
            <button class="cat-btn" onclick="document.getElementById('cat-<?= $cat['id'] ?>').scrollIntoView({behavior:'smooth',block:'start'})"><?= h($cat['icon'] ?? '🍽️') ?> <?= h($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="layout">
            <main>
                <?php foreach ($menu as $cat): if (empty($cat['items'])) continue; ?>
                <div class="cat-section" id="cat-<?= $cat['id'] ?>">
                    <h2><?= h($cat['icon'] ?? '🍽️') ?> <?= h($cat['name']) ?></h2>
                    <?php foreach ($cat['items'] as $item): ?>
                    <div class="menu-item" data-id="<?= $item['id'] ?>" data-name="<?= h($item['name']) ?>" data-price="<?= (float)($item['sale_price'] ?: $item['price']) ?>">
                        <div class="info">
                            <h3><?= h($item['name']) ?><?= $item['is_featured']?' ⭐':'' ?></h3>
                            <?php if ($item['description']): ?><div class="desc"><?= h($item['description']) ?></div><?php endif; ?>
                            <div class="meta">
                                <?php if ($item['sale_price']): ?><span class="price-old"><?= $sym ?><?= number_format((float)$item['price'],2) ?></span><?php endif; ?>
                                <span class="price"><?= $sym ?><?= number_format((float)($item['sale_price'] ?: $item['price']),2) ?></span>
                                <?= $item['is_vegetarian']?'<span class="diet-tag dt-v">V</span>':'' ?>
                                <?= $item['is_vegan']?'<span class="diet-tag dt-vg">VG</span>':'' ?>
                                <?= $item['is_gluten_free']?'<span class="diet-tag dt-gf">GF</span>':'' ?>
                                <?= $item['is_spicy']?'<span class="diet-tag dt-sp">🌶️</span>':'' ?>
                                <?php if ($item['calories']): ?><span style="font-size:.7rem;color:var(--muted)"><?= $item['calories'] ?> cal</span><?php endif; ?>
                            </div>
                        </div>
                        <button class="btn-add" onclick="addToCart(<?= $item['id'] ?>,'<?= h(addslashes($item['name'])) ?>',<?= (float)($item['sale_price'] ?: $item['price']) ?>)">+ Add</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </main>

            <aside>
                <div class="cart">
                    <h2>🛒 Your Order</h2>
                    <div id="cart-items"><div class="cart-empty">Your cart is empty</div></div>
                    <div id="cart-totals" style="display:none">
                        <div class="cart-totals">
                            <div class="cart-row"><span>Subtotal</span><span id="cart-subtotal"><?= $sym ?>0.00</span></div>
                            <div class="cart-row" id="cart-delivery-row" style="display:none"><span>Delivery</span><span><?= $sym ?><?= number_format($deliveryFee,2) ?></span></div>
                            <div class="cart-row"><span class="cart-total">Total</span><span class="cart-total" id="cart-total"><?= $sym ?>0.00</span></div>
                        </div>
                        <button class="btn-order" id="btn-checkout" onclick="showCheckout()" <?= !$accepting?'disabled':'' ?>><?= $accepting ? '🛒 Checkout' : '❌ Closed' ?></button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="checkout-overlay" id="checkout-overlay">
        <div class="checkout-form">
            <button class="btn-close" onclick="hideCheckout()">×</button>
            <h2>🛒 Complete Your Order</h2>
            <div id="checkout-msg"></div>
            <form id="orderForm" onsubmit="submitOrder(event)">
                <div class="fg"><label>Order Type</label><select id="order-type" onchange="toggleDelivery()"><?php foreach ($orderTypes as $t): ?><option value="<?= h(trim($t)) ?>"><?= ucfirst(h(trim($t))) ?></option><?php endforeach; ?></select></div>
                <div class="fr2">
                    <div class="fg"><label>Your Name *</label><input type="text" id="order-name" required></div>
                    <div class="fg"><label>Phone *</label><input type="tel" id="order-phone" required></div>
                </div>
                <div class="fg"><label>Email</label><input type="email" id="order-email"></div>
                <div id="delivery-fields">
                    <div class="fg"><label>Delivery Address *</label><input type="text" id="order-address"></div>
                    <div class="fg"><label>Delivery Notes</label><textarea id="order-delivery-notes" placeholder="Flat number, buzzer code, etc."></textarea></div>
                </div>
                <div class="fg"><label>Payment Method</label><select id="order-payment"><option value="cash">💵 Cash on Delivery</option><option value="card">💳 Card on Delivery</option></select></div>
                <div class="fg"><label>Order Notes</label><textarea id="order-notes" placeholder="Special requests..."></textarea></div>
                <button type="submit" class="btn-order">📤 Place Order</button>
            </form>
        </div>
    </div>

    <script>
    var cart = {};
    var SYM = '<?= $sym ?>';
    var DELIVERY_FEE = <?= $deliveryFee ?>;
    var MIN_ORDER = <?= $minOrder ?>;

    function addToCart(id, name, price) {
        if (cart[id]) cart[id].qty++;
        else cart[id] = {id:id, name:name, price:price, qty:1};
        renderCart();
    }
    function removeFromCart(id) { if (cart[id]) { cart[id].qty--; if (cart[id].qty <= 0) delete cart[id]; } renderCart(); }
    function renderCart() {
        var el = document.getElementById('cart-items');
        var totals = document.getElementById('cart-totals');
        var keys = Object.keys(cart);
        if (keys.length === 0) { el.innerHTML = '<div class="cart-empty">Your cart is empty</div>'; totals.style.display = 'none'; return; }
        var html = '';
        var subtotal = 0;
        keys.forEach(function(k) {
            var item = cart[k];
            subtotal += item.price * item.qty;
            html += '<div class="cart-item"><div class="cart-qty"><button onclick="removeFromCart('+k+')">−</button><span>'+item.qty+'</span><button onclick="addToCart('+k+',\''+item.name.replace(/'/g,"\\'")+'\','+item.price+')">+</button></div><span style="flex:1;font-size:.82rem">'+item.name+'</span><span style="font-weight:600">'+SYM+(item.price*item.qty).toFixed(2)+'</span></div>';
        });
        el.innerHTML = html;
        totals.style.display = 'block';
        document.getElementById('cart-subtotal').textContent = SYM + subtotal.toFixed(2);
        var isDelivery = (document.getElementById('order-type') || {}).value === 'delivery';
        var fee = isDelivery ? DELIVERY_FEE : 0;
        document.getElementById('cart-delivery-row').style.display = isDelivery ? 'flex' : 'none';
        document.getElementById('cart-total').textContent = SYM + (subtotal + fee).toFixed(2);
        var btn = document.getElementById('btn-checkout');
        if (MIN_ORDER > 0 && subtotal < MIN_ORDER) { btn.disabled = true; btn.textContent = 'Min order: ' + SYM + MIN_ORDER.toFixed(2); }
        else { btn.disabled = false; btn.textContent = '🛒 Checkout'; }
    }
    function showCheckout() { document.getElementById('checkout-overlay').classList.add('show'); toggleDelivery(); }
    function hideCheckout() { document.getElementById('checkout-overlay').classList.remove('show'); }
    function toggleDelivery() { var t = document.getElementById('order-type').value; document.getElementById('delivery-fields').style.display = t === 'delivery' ? 'block' : 'none'; renderCart(); }
    function submitOrder(e) {
        e.preventDefault();
        var items = Object.values(cart).map(function(i) { return {id:i.id, quantity:i.qty}; });
        var data = {
            items: items, order_type: document.getElementById('order-type').value,
            customer_name: document.getElementById('order-name').value,
            customer_phone: document.getElementById('order-phone').value,
            customer_email: document.getElementById('order-email').value,
            delivery_address: document.getElementById('order-address').value,
            delivery_notes: document.getElementById('order-delivery-notes').value,
            payment_method: document.getElementById('order-payment').value,
            notes: document.getElementById('order-notes').value
        };
        fetch('/api/restaurant/order', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data), credentials:'same-origin'})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var msg = document.getElementById('checkout-msg');
            if (d.ok) {
                msg.innerHTML = '<div class="msg msg-success">✅ Order placed! Your order number is <strong>' + d.order_number + '</strong>. Estimated time: ' + d.estimated_time + ' min. Total: ' + SYM + parseFloat(d.total).toFixed(2) + '</div>';
                cart = {}; renderCart(); document.getElementById('orderForm').style.display = 'none';
            } else { msg.innerHTML = '<div class="msg msg-error">❌ ' + (d.error || 'Error placing order') + '</div>'; }
        }).catch(function() { document.getElementById('checkout-msg').innerHTML = '<div class="msg msg-error">❌ Network error</div>'; });
    }
    </script>
</body>
</html>
