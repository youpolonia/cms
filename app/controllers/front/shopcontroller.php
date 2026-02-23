<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class ShopController
{
    private function loadShop(): void
    {
        require_once CMS_ROOT . '/core/shop.php';
    }

    private function loadReviews(): void
    {
        require_once CMS_ROOT . '/core/shop-reviews.php';
    }

    private function loadWishlist(): void
    {
        require_once CMS_ROOT . '/core/shop-wishlist.php';
    }

    private function loadAbandonedCarts(): void
    {
        require_once CMS_ROOT . '/core/shop-abandoned-carts.php';
    }

    /**
     * GET /shop — Product listing with filters
     */
    public function index(Request $request): void
    {
        $this->loadShop();
        $filters = [
            'search' => $_GET['q'] ?? '',
            'category' => $_GET['category'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest',
            'status' => 'active',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \Shop::getProducts($filters, $page, 12);
        $categories = \Shop::getCategories();

        $title = get_setting('shop_name', 'Shop');
        $description = 'Browse our products';

        $products = $result['products'];
        $total = $result['total'];
        $currentPage = $result['page'];
        $totalPages = $result['totalPages'];

        ob_start();
        require CMS_APP . '/views/front/shop/index.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * GET /shop/{slug} — Single product
     */
    public function product(Request $request): void
    {
        $this->loadShop();
        $this->loadReviews();
        $slug = $request->param('slug');
        $product = \Shop::getProductBySlug($slug);

        if (!$product || $product['status'] !== 'active') {
            http_response_code(404);
            echo '404 - Product not found';
            exit;
        }

        $related = \Shop::getRelatedProducts((int)$product['id'], 4);

        // Analytics tracking
        require_once CMS_ROOT . '/core/shop-analytics.php';
        \ShopAnalytics::trackProductView((int)$product['id']);

        // Reviews data
        $reviewPage = max(1, (int)($_GET['review_page'] ?? 1));
        $ratingData = \ShopReviews::getProductRating((int)$product['id']);
        $reviewsData = \ShopReviews::getForProduct((int)$product['id'], $reviewPage, 10);

        $title = $product['meta_title'] ?: $product['name'];
        $description = $product['meta_description'] ?: ($product['short_description'] ?: '');

        ob_start();
        require CMS_APP . '/views/front/shop/product.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * GET /shop/category/{slug}
     */
    public function category(Request $request): void
    {
        $this->loadShop();
        $slug = $request->param('slug');
        $category = \Shop::getCategoryBySlug($slug);

        if (!$category) {
            http_response_code(404);
            echo '404 - Category not found';
            exit;
        }

        $filters = [
            'category' => $category['id'],
            'status' => 'active',
            'sort' => $_GET['sort'] ?? 'newest',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \Shop::getProducts($filters, $page, 12);
        $categories = \Shop::getCategories();

        $title = $category['name'] . ' — ' . get_setting('shop_name', 'Shop');
        $description = $category['description'] ?: 'Products in ' . $category['name'];
        $products = $result['products'];
        $total = $result['total'];
        $currentPage = $result['page'];
        $totalPages = $result['totalPages'];

        ob_start();
        require CMS_APP . '/views/front/shop/category.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * GET /cart
     */
    public function cart(Request $request): void
    {
        $this->loadShop();
        $cart = \Shop::getCart();
        $title = 'Shopping Cart';
        $description = 'Your shopping cart';

        ob_start();
        require CMS_APP . '/views/front/shop/cart.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * POST /cart/add — AJAX
     */
    public function addToCart(Request $request): void
    {
        $this->loadShop();
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $result = \Shop::addToCart($productId, $quantity);

        // Analytics tracking
        require_once CMS_ROOT . '/core/shop-analytics.php';
        \ShopAnalytics::trackAddToCart($productId);

        // Abandoned cart tracking
        if ($result['success'] && !empty($_SESSION['cart'])) {
            $this->loadAbandonedCarts();
            $cart = \Shop::getCart();
            $email = $_SESSION['customer_email'] ?? null;
            \AbandonedCarts::saveCart(session_id(), $_SESSION['cart'], (float)$cart['subtotal'], $email);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * POST /cart/update — AJAX
     */
    public function updateCart(Request $request): void
    {
        $this->loadShop();
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $result = \Shop::updateCartItem($productId, $quantity);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * POST /cart/remove — AJAX
     */
    public function removeFromCart(Request $request): void
    {
        $this->loadShop();
        $productId = (int)($_POST['product_id'] ?? 0);
        $result = \Shop::removeFromCart($productId);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * GET /checkout
     */
    public function checkout(Request $request): void
    {
        $this->loadShop();
        $cart = \Shop::getCart();
        if (empty($cart['items'])) {
            header('Location: /cart');
            exit;
        }

        // Analytics tracking
        require_once CMS_ROOT . '/core/shop-analytics.php';
        \ShopAnalytics::trackCheckout((float)($cart['total'] ?? $cart['subtotal'] ?? 0));

        // Abandoned cart — save email from session if available
        $checkoutEmail = $_SESSION['customer_email'] ?? ($_GET['email'] ?? null);
        if ($checkoutEmail) {
            $_SESSION['customer_email'] = $checkoutEmail;
            $this->loadAbandonedCarts();
            \AbandonedCarts::saveCart(session_id(), $_SESSION['cart'] ?? [], (float)($cart['subtotal'] ?? 0), $checkoutEmail);
        }

        $title = 'Checkout';
        $description = 'Complete your order';

        ob_start();
        require CMS_APP . '/views/front/shop/checkout.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * POST /checkout — Process order
     */
    public function processCheckout(Request $request): void
    {
        $this->loadShop();
        $cart = \Shop::getCart();
        if (empty($cart['items'])) {
            header('Location: /cart');
            exit;
        }

        $customerData = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'notes' => trim($_POST['notes'] ?? ''),
            'billing_address' => [
                'line1' => $_POST['address_line1'] ?? '',
                'line2' => $_POST['address_line2'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'zip' => $_POST['zip'] ?? '',
                'country' => $_POST['country'] ?? '',
            ],
        ];

        if (empty($customerData['name']) || empty($customerData['email'])) {
            if (function_exists('\\Core\\Session::flash')) {
                \Core\Session::flash('error', 'Name and email are required.');
            }
            header('Location: /checkout');
            exit;
        }

        $orderId = \Shop::createOrder($cart['items'], $customerData);
        if ($orderId) {
            // Analytics tracking
            require_once CMS_ROOT . '/core/shop-analytics.php';
            \ShopAnalytics::trackPurchase($orderId, (float)($cart['total'] ?? $cart['subtotal'] ?? 0));

            // Mark abandoned cart as recovered
            $this->loadAbandonedCarts();
            \AbandonedCarts::markRecovered(session_id());

            $order = \Shop::getOrder($orderId);
            header('Location: /order/thank-you/' . ($order['order_number'] ?? ''));
            exit;
        }

        header('Location: /checkout');
        exit;
    }

    /**
     * GET /order/thank-you/{number}
     */
    public function thankYou(Request $request): void
    {
        $this->loadShop();
        $number = $request->param('number');
        $order = \Shop::getOrderByNumber($number);

        if (!$order) {
            http_response_code(404);
            echo '404 - Order not found';
            exit;
        }

        $title = 'Order Confirmed';
        $description = 'Thank you for your order';

        ob_start();
        require CMS_APP . '/views/front/shop/thank-you.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * POST /shop/review/submit — AJAX
     */
    public function submitReview(Request $request): void
    {
        $this->loadReviews();

        header('Content-Type: application/json');

        $name = trim($_POST['customer_name'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($name === '' || $email === '') {
            echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
            exit;
        }
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5.']);
            exit;
        }
        if ($reviewText === '') {
            echo json_encode(['success' => false, 'message' => 'Review text is required.']);
            exit;
        }
        if ($productId < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $id = \ShopReviews::submit([
            'product_id' => $productId,
            'customer_name' => $name,
            'customer_email' => $email,
            'rating' => $rating,
            'title' => $title,
            'review_text' => $reviewText,
        ]);

        if ($id > 0) {
            echo json_encode(['success' => true, 'message' => 'Thank you! Your review has been submitted and will appear after moderation.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
        }
        exit;
    }

    /**
     * POST /shop/review/{id}/helpful — AJAX
     */
    public function reviewHelpful(Request $request): void
    {
        $this->loadReviews();

        header('Content-Type: application/json');
        $id = (int)$request->param('id');
        \ShopReviews::markHelpful($id);

        $row = db()->prepare("SELECT helpful_count FROM product_reviews WHERE id = ?");
        $row->execute([$id]);
        $count = (int)($row->fetchColumn() ?: 0);

        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    // ─── DIGITAL DOWNLOADS ───

    /**
     * GET /shop/download/{token} — Download a digital product
     */
    public function download(Request $request): void
    {
        require_once CMS_ROOT . '/core/shop-digital.php';

        $token = $request->param('token');
        if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            http_response_code(400);
            echo '<h1>Invalid Download Link</h1><p>The download link is invalid.</p>';
            exit;
        }

        $result = \ShopDigital::processDownload($token);

        if (!$result['ok']) {
            http_response_code(403);
            echo '<div style="max-width:500px;margin:80px auto;text-align:center;font-family:sans-serif">';
            echo '<div style="font-size:3rem;margin-bottom:16px">⚠️</div>';
            echo '<h1 style="margin-bottom:8px">Download Unavailable</h1>';
            echo '<p style="color:#666">' . htmlspecialchars($result['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<a href="/shop" style="display:inline-block;margin-top:20px;padding:10px 24px;background:#6366f1;color:#fff;border-radius:8px;text-decoration:none">Back to Shop</a>';
            echo '</div>';
            exit;
        }

        // Stream the file
        $filePath = $result['file'];
        $filename = $result['filename'];
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        readfile($filePath);
        exit;
    }

    // ─── COUPONS ───

    /**
     * POST /cart/coupon/apply
     */
    public function applyCoupon(Request $request): void
    {
        $this->loadShop();
        require_once CMS_ROOT . '/core/shop-coupons.php';

        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        if ($code === '') {
            \Core\Session::flash('error', 'Please enter a coupon code.');
            header('Location: /cart');
            exit;
        }

        $cart = \Shop::getCart();
        $result = \ShopCoupons::validate($code, $cart['subtotal']);

        if ($result['valid']) {
            $_SESSION['coupon_code'] = $code;
            \Core\Session::flash('success', 'Coupon applied! ' . $result['message']);
        } else {
            \Core\Session::flash('error', $result['message']);
        }

        header('Location: /cart');
        exit;
    }

    /**
     * POST /cart/coupon/remove
     */
    public function removeCoupon(Request $request): void
    {
        unset($_SESSION['coupon_code']);
        \Core\Session::flash('success', 'Coupon removed.');
        header('Location: /cart');
        exit;
    }

    // ─── WISHLIST ───

    /**
     * GET /shop/wishlist — Show wishlist page
     */
    public function wishlist(Request $request): void
    {
        $this->loadShop();
        $this->loadWishlist();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $sessionId = session_id();
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = \ShopWishlist::getAll($sessionId, $page, 12);
        $products = $result['products'];
        $total = $result['total'];
        $currentPage = $result['page'];
        $totalPages = $result['totalPages'];
        $wishlistCount = $total;

        $title = 'My Wishlist';
        $description = 'Your saved products';

        ob_start();
        require CMS_APP . '/views/front/shop/wishlist.php';
        $content = ob_get_clean();

        $this->renderWithTheme($content, $title, $description);
    }

    /**
     * POST /shop/wishlist/toggle — AJAX toggle product in wishlist
     */
    public function wishlistToggle(Request $request): void
    {
        $this->loadWishlist();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        header('Content-Type: application/json');

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $sessionId = session_id();
        $result = \ShopWishlist::toggle($sessionId, $productId);
        $count = \ShopWishlist::getCount($sessionId);

        echo json_encode([
            'success' => true,
            'added' => $result['added'],
            'count' => $count,
        ]);
        exit;
    }

    /**
     * POST /shop/wishlist/remove — AJAX remove from wishlist
     */
    public function wishlistRemove(Request $request): void
    {
        $this->loadWishlist();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        header('Content-Type: application/json');

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $sessionId = session_id();
        \ShopWishlist::remove($sessionId, $productId);
        $count = \ShopWishlist::getCount($sessionId);

        echo json_encode([
            'success' => true,
            'count' => $count,
        ]);
        exit;
    }

    /**
     * Render content within the active theme layout (same pattern as SearchController)
     */
    private function renderWithTheme(string $content, string $title, string $description = ''): void
    {
        $layoutFile = theme_path('layout.php');

        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            $output = ob_get_clean();
            if (function_exists('cms_inject_admin_toolbar')) {
                $output = cms_inject_admin_toolbar($output, ['type' => 'shop']);
            }
            echo $output;
        } else {
            echo $content;
        }
        exit;
    }
}
