<?php
/**
 * Ensure all extension tables exist — run once on install/upgrade
 * Covers: Shop extensions (6 tables) + Dropshipping (5 tables) = 11 tables
 */
define('CMS_ROOT', '/var/www/cms');
require_once CMS_ROOT . '/db.php';

// Shop Extensions
require_once CMS_ROOT . '/core/shop-coupons.php';
require_once CMS_ROOT . '/core/shop-variants.php';
require_once CMS_ROOT . '/core/shop-reviews.php';
require_once CMS_ROOT . '/core/shop-digital.php';
require_once CMS_ROOT . '/core/shop-wishlist.php';
require_once CMS_ROOT . '/core/shop-analytics.php';
require_once CMS_ROOT . '/core/shop-abandoned-carts.php';

// Dropshipping
require_once CMS_ROOT . '/core/dropshipping.php';

echo "Creating shop extension tables...\n";
ShopCoupons::ensureTable();       echo "  ✅ coupons\n";
ShopVariants::ensureTable();      echo "  ✅ product_variants\n";
ShopReviews::ensureTable();       echo "  ✅ product_reviews\n";
ShopDigital::ensureTable();       echo "  ✅ digital_downloads\n";
ShopWishlist::ensureTable();      echo "  ✅ wishlists\n";
ShopAnalytics::ensureTable();     echo "  ✅ shop_analytics\n";
AbandonedCarts::ensureTable();    echo "  ✅ abandoned_carts\n";

echo "\nCreating dropshipping tables...\n";
Dropshipping::ensureTables();
echo "  ✅ ds_suppliers\n";
echo "  ✅ ds_product_links\n";
echo "  ✅ ds_imports\n";
echo "  ✅ ds_order_forwards\n";
echo "  ✅ ds_price_rules\n";

echo "\nAll 12 tables ensured.\n";
