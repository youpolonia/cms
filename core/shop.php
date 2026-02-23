<?php
declare(strict_types=1);

/**
 * Shop Core — Products, Cart, Orders, Categories
 * E-commerce engine for Jessie AI CMS
 */
class Shop
{
    // ─── PRODUCTS ───

    public static function getProducts(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'p.category_id = ?';
            $params[] = (int)$filters['category'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $where[] = 'p.price >= ?';
            $params[] = (float)$filters['price_min'];
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $where[] = 'p.price <= ?';
            $params[] = (float)$filters['price_max'];
        }
        if (isset($filters['featured'])) {
            $where[] = 'p.featured = ?';
            $params[] = (int)$filters['featured'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'p.type = ?';
            $params[] = $filters['type'];
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sort = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            $allowed = [
                'name_asc' => 'p.name ASC',
                'name_desc' => 'p.name DESC',
                'price_asc' => 'p.price ASC',
                'price_desc' => 'p.price DESC',
                'newest' => 'p.created_at DESC',
                'oldest' => 'p.created_at ASC',
            ];
            $sort = $allowed[$filters['sort']] ?? $sort;
        }

        $stmt = $pdo->prepare(
            "SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE {$whereStr} ORDER BY {$sort} LIMIT ? OFFSET ?"
        );
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return compact('products', 'total', 'page', 'perPage', 'totalPages');
    }

    public static function getProduct(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getProductBySlug(string $slug): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getFeaturedProducts(int $limit = 8): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'active' ORDER BY p.created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getRelatedProducts(int $productId, int $limit = 4): array
    {
        $product = self::getProduct($productId);
        if (!$product) return [];
        $pdo = db();
        $params = [$productId];
        $catWhere = '';
        if ($product['category_id']) {
            $catWhere = 'AND p.category_id = ?';
            $params[] = (int)$product['category_id'];
        }
        $params[] = $limit;
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.id != ? AND p.status = 'active' {$catWhere} ORDER BY RAND() LIMIT ?");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function createProduct(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'product', 'products');
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock, image, gallery, category_id, type, digital_file, status, featured, weight, meta_title, meta_description, attributes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'] ?? '',
            $data['slug'] ?? $slug,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            (float)($data['price'] ?? 0),
            !empty($data['sale_price']) ? (float)$data['sale_price'] : null,
            $data['sku'] ?? null,
            (int)($data['stock'] ?? -1),
            $data['image'] ?? null,
            !empty($data['gallery']) ? (is_string($data['gallery']) ? $data['gallery'] : json_encode($data['gallery'])) : null,
            !empty($data['category_id']) ? (int)$data['category_id'] : null,
            $data['type'] ?? 'physical',
            $data['digital_file'] ?? null,
            $data['status'] ?? 'draft',
            !empty($data['featured']) ? 1 : 0,
            !empty($data['weight']) ? (float)$data['weight'] : null,
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null,
            !empty($data['attributes']) ? (is_string($data['attributes']) ? $data['attributes'] : json_encode($data['attributes'])) : null,
        ]);
        $id = (int)$pdo->lastInsertId();
        if ($id && function_exists('cms_event')) {
            cms_event('shop.product.created', ['id' => $id, 'name' => $data['name'] ?? '']);
        }
        return $id;
    }

    public static function updateProduct(int $id, array $data): bool
    {
        $pdo = db();
        $fields = [];
        $params = [];
        $map = ['name','slug','description','short_description','price','sale_price','sku','stock','image','gallery','category_id','type','digital_file','status','featured','weight','meta_title','meta_description','attributes'];
        foreach ($map as $key) {
            if (array_key_exists($key, $data)) {
                $val = $data[$key];
                if ($key === 'price') $val = (float)$val;
                if ($key === 'sale_price') $val = ($val !== '' && $val !== null) ? (float)$val : null;
                if ($key === 'stock') $val = (int)$val;
                if ($key === 'category_id') $val = ($val !== '' && $val !== null) ? (int)$val : null;
                if ($key === 'featured') $val = !empty($val) ? 1 : 0;
                if ($key === 'weight') $val = ($val !== '' && $val !== null) ? (float)$val : null;
                if (in_array($key, ['gallery', 'attributes']) && is_array($val)) $val = json_encode($val);
                $fields[] = "`{$key}` = ?";
                $params[] = $val;
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        return $pdo->prepare("UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function deleteProduct(int $id): bool
    {
        return db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    }

    // ─── CART ───

    public static function getCart(): array
    {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $items = [];
        $subtotal = 0;
        $pdo = db();
        foreach ($_SESSION['cart'] as $cartKey => $qty) {
            // Cart key format: "productId" or "productId:variantId"
            $parts = explode(':', (string)$cartKey);
            $productId = (int)$parts[0];
            $variantId = isset($parts[1]) ? (int)$parts[1] : 0;

            $stmt = $pdo->prepare("SELECT id, name, slug, price, sale_price, image, stock, status FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($product && $product['status'] === 'active') {
                $ep = ($product['sale_price'] !== null && (float)$product['sale_price'] > 0) ? (float)$product['sale_price'] : (float)$product['price'];
                $itemImage = $product['image'];
                $variantLabel = '';
                $itemStock = (int)$product['stock'];

                // Load variant info if present
                if ($variantId > 0) {
                    require_once CMS_ROOT . '/core/shop-variants.php';
                    $variant = \ShopVariants::get($variantId);
                    if ($variant) {
                        $ep = \ShopVariants::getEffectivePrice($variant, $product);
                        $variantLabel = \ShopVariants::getVariantLabel($variant);
                        if (!empty($variant['image'])) $itemImage = $variant['image'];
                        $itemStock = (int)$variant['stock'];
                    }
                }

                $lt = $ep * $qty;
                $subtotal += $lt;
                $items[] = [
                    'product_id'=>(int)$product['id'],
                    'variant_id'=>$variantId,
                    'variant_label'=>$variantLabel,
                    'name'=>$product['name'],
                    'slug'=>$product['slug'],
                    'price'=>(float)$product['price'],
                    'sale_price'=>$product['sale_price']!==null?(float)$product['sale_price']:null,
                    'effective_price'=>$ep,
                    'quantity'=>$qty,
                    'line_total'=>$lt,
                    'image'=>$itemImage,
                    'stock'=>$itemStock,
                    'cart_key'=>$cartKey,
                ];
            } else {
                unset($_SESSION['cart'][$cartKey]);
            }
        }
        $taxRate = (float)(get_setting('shop_tax_rate', '0') ?: 0) / 100;
        $tax = round($subtotal * $taxRate, 2);
        $sft = (float)(get_setting('shop_free_shipping_threshold', '0') ?: 0);
        $sc = (float)(get_setting('shop_shipping_cost', '0') ?: 0);
        $shipping = ($sft > 0 && $subtotal >= $sft) ? 0 : $sc;
        $total = $subtotal + $tax + $shipping;
        return ['items'=>$items,'subtotal'=>$subtotal,'tax'=>$tax,'shipping'=>$shipping,'total'=>$total,'count'=>array_sum(array_column($items,'quantity'))];
    }

    public static function addToCart(int $productId, int $quantity = 1, int $variantId = 0): array
    {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, name, stock, status FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$product) return ['success'=>false,'message'=>'Product not found or unavailable.'];

        // Build cart key: "productId" or "productId:variantId"
        $cartKey = $variantId > 0 ? $productId . ':' . $variantId : (string)$productId;
        $cur = $_SESSION['cart'][$cartKey] ?? 0;
        $newQty = $cur + $quantity;

        // Stock check — variant stock takes priority
        if ($variantId > 0) {
            require_once CMS_ROOT . '/core/shop-variants.php';
            if (!\ShopVariants::checkStock($variantId, $newQty)) {
                return ['success'=>false,'message'=>'Not enough stock available for this variant.'];
            }
        } else {
            if ((int)$product['stock'] !== -1 && $newQty > (int)$product['stock']) {
                return ['success'=>false,'message'=>'Not enough stock available.'];
            }
        }

        $_SESSION['cart'][$cartKey] = $newQty;
        return ['success'=>true,'message'=>'Added to cart.','cartCount'=>self::getCartCount()];
    }

    public static function updateCartItem(string $cartKey, int $quantity): array
    {
        if ($quantity <= 0) return self::removeFromCart($cartKey);
        if (!isset($_SESSION['cart'][$cartKey])) return ['success'=>false,'message'=>'Item not in cart.'];

        $parts = explode(':', $cartKey);
        $productId = (int)$parts[0];
        $variantId = isset($parts[1]) ? (int)$parts[1] : 0;

        if ($variantId > 0) {
            require_once CMS_ROOT . '/core/shop-variants.php';
            if (!\ShopVariants::checkStock($variantId, $quantity)) {
                return ['success'=>false,'message'=>'Not enough stock.'];
            }
        } else {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $p = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($p && (int)$p['stock'] !== -1 && $quantity > (int)$p['stock']) return ['success'=>false,'message'=>'Not enough stock.'];
        }

        $_SESSION['cart'][$cartKey] = $quantity;
        return ['success'=>true,'message'=>'Cart updated.'];
    }

    public static function removeFromCart(string $cartKey): array
    {
        unset($_SESSION['cart'][$cartKey]);
        return ['success'=>true,'message'=>'Item removed.','cartCount'=>self::getCartCount()];
    }

    public static function clearCart(): void { $_SESSION['cart'] = []; }

    public static function getCartCount(): int
    {
        if (!isset($_SESSION['cart'])) return 0;
        return array_sum($_SESSION['cart']);
    }

    public static function getCartTotal(): float { return self::getCart()['total']; }

    // ─── ORDERS ───

    public static function createOrder(array $cartItems, array $customerData): ?int
    {
        $pdo = db();
        $orderNumber = 'JC-' . date('Ymd') . '-' . str_pad((string)rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $subtotal = 0;
        $itemsJson = [];
        foreach ($cartItems as $item) {
            $subtotal += $item['line_total'];
            $itemData = ['product_id'=>$item['product_id'],'name'=>$item['name'],'price'=>$item['effective_price'],'quantity'=>$item['quantity'],'line_total'=>$item['line_total'],'image'=>$item['image']??null];
            if (!empty($item['variant_id'])) {
                $itemData['variant_id'] = (int)$item['variant_id'];
                $itemData['variant_label'] = $item['variant_label'] ?? '';
            }
            $itemsJson[] = $itemData;
        }
        $taxRate = (float)(get_setting('shop_tax_rate', '0') ?: 0) / 100;
        $tax = round($subtotal * $taxRate, 2);
        $sft = (float)(get_setting('shop_free_shipping_threshold', '0') ?: 0);
        $sc = (float)(get_setting('shop_shipping_cost', '0') ?: 0);
        $shipping = ($sft > 0 && $subtotal >= $sft) ? 0 : $sc;
        $total = $subtotal + $tax + $shipping;
        $currency = get_setting('shop_currency', 'USD');
        $ba = !empty($customerData['billing_address']) ? json_encode($customerData['billing_address']) : null;
        $sa = !empty($customerData['shipping_address']) ? json_encode($customerData['shipping_address']) : null;
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_email, customer_name, customer_phone, billing_address, shipping_address, items, subtotal, tax, shipping, discount, total, currency, status, payment_method, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'unpaid', ?)");
            $stmt->execute([$orderNumber, $customerData['email']??'', $customerData['name']??'', $customerData['phone']??null, $ba, $sa, json_encode($itemsJson), $subtotal, $tax, $shipping, 0, $total, $currency, $customerData['payment_method']??null, $customerData['notes']??null]);
            $orderId = (int)$pdo->lastInsertId();
            // Decrement stock — variant stock or product stock
            require_once CMS_ROOT . '/core/shop-variants.php';
            foreach ($cartItems as $item) {
                if (!empty($item['variant_id'])) {
                    \ShopVariants::decrementStock((int)$item['variant_id'], $item['quantity']);
                } else {
                    $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock > 0")->execute([$item['quantity'], $item['product_id']]);
                }
            }
            $pdo->commit();
            self::clearCart();
            if (function_exists('cms_event')) {
                cms_event('shop.order.created', ['id' => $orderId, 'order_number' => $orderNumber, 'total' => $total, 'customer_email' => $customerData['email'] ?? '']);
            }

            // Send order emails
            require_once CMS_ROOT . '/core/shop-emails.php';
            ShopEmails::sendOrderConfirmation($orderId);
            ShopEmails::sendOrderNotificationToAdmin($orderId);

            // Sync to CRM
            require_once CMS_ROOT . '/core/shop-crm.php';
            ShopCRM::syncOrderToContact($orderId);

            // Generate download tokens for digital products
            require_once CMS_ROOT . '/core/shop-digital.php';
            foreach ($cartItems as $item) {
                $productFull = self::getProduct($item['product_id']);
                if ($productFull && $productFull['type'] === 'digital' && $productFull['digital_file']) {
                    ShopDigital::createDownloadToken($item['product_id'], $orderId, $customerData['email'] ?? '');
                }
            }

            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('Shop::createOrder error: ' . $e->getMessage());
            return null;
        }
    }

    public static function getOrders(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['payment_status'])) { $where[] = 'payment_status = ?'; $params[] = $filters['payment_status']; }
        if (!empty($filters['search'])) {
            $where[] = '(order_number LIKE ? OR customer_email LIKE ? OR customer_name LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }
        $whereStr = implode(' AND ', $where);
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return compact('orders', 'total', 'page', 'totalPages');
    }

    public static function getOrder(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function getOrderByNumber(string $number): ?array
    {
        $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$number]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function updateOrderStatus(int $id, string $status): bool
    {
        $allowed = ['pending','processing','shipped','delivered','cancelled','refunded'];
        if (!in_array($status, $allowed)) return false;
        $result = db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);

        if ($result) {
            // Send status update email
            require_once CMS_ROOT . '/core/shop-emails.php';
            ShopEmails::sendStatusUpdate($id, $status);
            // Fire event
            if (function_exists('cms_event')) {
                cms_event('shop.order.status_changed', ['order_id' => $id, 'status' => $status]);
            }
        }

        return $result;
    }

    public static function getOrderStats(): array
    {
        $pdo = db();
        $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
        $today = date('Y-m-d');
        $s = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?"); $s->execute([$today]);
        $todayOrders = (int)$s->fetchColumn();
        $s = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at) = ? AND payment_status = 'paid'"); $s->execute([$today]);
        $todayRevenue = (float)$s->fetchColumn();
        $ms = date('Y-m-01');
        $s = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE created_at >= ?"); $s->execute([$ms]);
        $monthOrders = (int)$s->fetchColumn();
        $s = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE created_at >= ? AND payment_status = 'paid'"); $s->execute([$ms]);
        $monthRevenue = (float)$s->fetchColumn();
        $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
        $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $lowStock = $pdo->query("SELECT id, name, stock FROM products WHERE stock >= 0 AND stock <= 5 AND status = 'active' ORDER BY stock ASC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        return compact('totalOrders','totalRevenue','todayOrders','todayRevenue','monthOrders','monthRevenue','totalProducts','activeProducts','pendingOrders','lowStock');
    }

    public static function updateTracking(int $id, string $trackingNumber): bool
    {
        $result = db()->prepare("UPDATE orders SET tracking_number = ? WHERE id = ?")->execute([$trackingNumber, $id]);
        if ($result) {
            require_once CMS_ROOT . '/core/shop-emails.php';
            ShopEmails::sendShippingNotification($id, $trackingNumber);
        }
        return $result;
    }

    public static function checkLowStock(int $threshold = 5): array
    {
        $stmt = db()->prepare("SELECT id, name, sku, stock FROM products WHERE stock > 0 AND stock <= ? AND status = 'active' ORDER BY stock ASC");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ─── CATEGORIES ───

    public static function getCategories(): array
    {
        return db()->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM product_categories c ORDER BY c.sort_order ASC, c.name ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getCategory(int $id): ?array
    {
        $s = db()->prepare("SELECT * FROM product_categories WHERE id = ?"); $s->execute([$id]);
        $r = $s->fetch(\PDO::FETCH_ASSOC); return $r ?: null;
    }

    public static function getCategoryBySlug(string $slug): ?array
    {
        $s = db()->prepare("SELECT * FROM product_categories WHERE slug = ?"); $s->execute([$slug]);
        $r = $s->fetch(\PDO::FETCH_ASSOC); return $r ?: null;
    }

    public static function createCategory(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'category', 'product_categories');
        $pdo->prepare("INSERT INTO product_categories (name, slug, description, parent_id, image, sort_order) VALUES (?, ?, ?, ?, ?, ?)")->execute([
            $data['name']??'', $data['slug']??$slug, $data['description']??null,
            !empty($data['parent_id'])?(int)$data['parent_id']:null, $data['image']??null, (int)($data['sort_order']??0)
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateCategory(int $id, array $data): bool
    {
        $fields = []; $params = [];
        foreach (['name','slug','description','parent_id','image','sort_order'] as $k) {
            if (array_key_exists($k, $data)) {
                $v = $data[$k];
                if ($k === 'parent_id') $v = ($v !== '' && $v !== null) ? (int)$v : null;
                if ($k === 'sort_order') $v = (int)$v;
                $fields[] = "`{$k}` = ?"; $params[] = $v;
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        return db()->prepare("UPDATE product_categories SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function deleteCategory(int $id): bool
    {
        $pdo = db();
        $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        return $pdo->prepare("DELETE FROM product_categories WHERE id = ?")->execute([$id]);
    }

    // ─── HELPERS ───

    private static function generateSlug(string $name, string $table): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        if ($slug === '') $slug = 'item';
        $pdo = db(); $base = $slug; $c = 0;
        while (true) {
            $ch = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE slug = ?"); $ch->execute([$slug]);
            if ((int)$ch->fetchColumn() === 0) break;
            $c++; $slug = $base . '-' . $c;
        }
        return $slug;
    }

    public static function getEffectivePrice(array $product): float
    {
        return ($product['sale_price'] !== null && (float)$product['sale_price'] > 0) ? (float)$product['sale_price'] : (float)$product['price'];
    }

    public static function formatPrice(float $amount): string
    {
        $currency = get_setting('shop_currency', 'USD');
        $symbols = ['USD'=>'$','EUR'=>'€','GBP'=>'£','PLN'=>'zł','JPY'=>'¥','CAD'=>'CA$','AUD'=>'A$'];
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }
}

// ─── Event: Auto-post new products to social media ───
if (function_exists('cms_on')) {
    cms_on('shop.product.created', function($data) {
        require_once CMS_ROOT . '/core/shop-social.php';
        ShopSocial::autoPostNewProduct($data);
    });
}
