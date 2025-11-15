<?php
require_once __DIR__ . '/pluginmarketplace.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot();

$marketplace = PluginMarketplace::getInstance();
$products = $marketplace->getProducts();
$cart = $marketplace->getCart();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    if (isset($_POST['add_to_cart'])) {
        $marketplace->addToCart($_POST['product_id']);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } elseif (isset($_POST['purchase'])) {
        $purchaseResult = $marketplace->processPurchase();
    } elseif (isset($_POST['clear_cart'])) {
        $marketplace->clearCart();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plugin Marketplace</title>
    <style>
        .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .product-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .cart { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .license { background: #f5f5f5; padding: 15px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Plugin Marketplace</h1>
    
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p>Price: $<?= number_format($product['price'], 2) ?></p>
                <form method="post">
                    <?= csrf_field(); 
?>                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cart">
        <h2>Your Cart (<?= count($cart) ?> items)</h2>
        <?php if (!empty($cart)): ?>
            <ul>
                <?php foreach ($cart as $item): ?>
                    <li><?= htmlspecialchars($item['name']) ?> - $<?= number_format($item['price'], 2) ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Total: $<?= number_format(array_sum(array_column($cart, 'price')), 2) ?></p>
                        <form method="post">
                            <?= csrf_field(); 
?>                            <button type="submit" name="purchase">Purchase</button>
                <button type="submit" name="clear_cart">Clear Cart</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty</p>
        <?php endif; ?>
    </div>

    <?php if (isset($purchaseResult) && $purchaseResult['success']): ?>
        <div class="license">
            <h3>Purchase Complete!</h3>
            <p>Total Paid: $<?= number_format($purchaseResult['total'], 2) ?></p>
            <h4>Your License Keys:</h4>
            <ul>
                <?php foreach ($purchaseResult['licenses'] as $license): ?>
                    <li><?= htmlspecialchars($license) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</body>
</html>
