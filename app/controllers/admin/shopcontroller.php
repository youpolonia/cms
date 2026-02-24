<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class ShopController
{
    private function loadShop(): void
    {
        require_once CMS_ROOT . '/core/shop.php';
    }

    // ─── DASHBOARD ───

    public function dashboard(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $stats = \Shop::getOrderStats();
        $recentResult = \Shop::getOrders([], 1, 5);
        render('admin/shop/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentResult['orders'],
        ]);
    }

    // ─── PRODUCTS ───

    public function products(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $filters = [
            'search' => $_GET['q'] ?? '',
            'category' => $_GET['category'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \Shop::getProducts($filters, $page, 20);
        $categories = \Shop::getCategories();
        render('admin/shop/products', [
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function productCreate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $categories = \Shop::getCategories();
        render('admin/shop/product-form', [
            'product' => null,
            'categories' => $categories,
        ]);
    }

    public function productEdit(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $product = \Shop::getProduct($id);
        if (!$product) {
            Session::flash('error', 'Product not found.');
            \Core\Response::redirect('/admin/shop/products');
        }
        $categories = \Shop::getCategories();
        require_once CMS_ROOT . '/core/shop-variants.php';
        $variants = \ShopVariants::getForProduct($id);
        render('admin/shop/product-form', [
            'product' => $product,
            'categories' => $categories,
            'variants' => $variants,
        ]);
    }

    public function productStore(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $data = $_POST;
        $variantsData = $data['variants'] ?? [];
        unset($data['csrf_token'], $data['variants']);
        // Handle digital_file from form
        if (isset($data['digital_file_path']) && $data['digital_file_path'] !== '') {
            $data['digital_file'] = $data['digital_file_path'];
        }
        unset($data['digital_file_path'], $data['digital_max_downloads'], $data['digital_expiry_hours']);
        $id = \Shop::createProduct($data);
        if ($id) {
            if (!empty($variantsData) && is_array($variantsData)) {
                require_once CMS_ROOT . '/core/shop-variants.php';
                \ShopVariants::bulkSave($id, $variantsData);
            }
            Session::flash('success', 'Product created successfully.');
            \Core\Response::redirect('/admin/shop/products');
        } else {
            Session::flash('error', 'Failed to create product.');
            \Core\Response::redirect('/admin/shop/products/create');
        }
    }

    public function productUpdate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $data = $_POST;
        $variantsData = $data['variants'] ?? [];
        unset($data['csrf_token'], $data['variants']);
        // Handle digital_file from form
        if (isset($data['digital_file_path']) && $data['digital_file_path'] !== '') {
            $data['digital_file'] = $data['digital_file_path'];
        }
        unset($data['digital_file_path'], $data['digital_max_downloads'], $data['digital_expiry_hours']);
        \Shop::updateProduct($id, $data);
        require_once CMS_ROOT . '/core/shop-variants.php';
        if (!empty($variantsData) && is_array($variantsData)) {
            \ShopVariants::bulkSave($id, $variantsData);
        } else {
            \ShopVariants::deleteAllForProduct($id);
        }
        Session::flash('success', 'Product updated successfully.');
        \Core\Response::redirect("/admin/shop/products/{$id}/edit");
    }

    public function productDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        \Shop::deleteProduct($id);
        Session::flash('success', 'Product deleted.');
        \Core\Response::redirect('/admin/shop/products');
    }

    // ─── CATEGORIES ───

    public function categories(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $categories = \Shop::getCategories();
        render('admin/shop/categories', [
            'categories' => $categories,
        ]);
    }

    public function categoryStore(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $data = $_POST;
        unset($data['csrf_token']);
        \Shop::createCategory($data);
        Session::flash('success', 'Category created.');
        \Core\Response::redirect('/admin/shop/categories');
    }

    public function categoryUpdate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $data = $_POST;
        unset($data['csrf_token']);
        \Shop::updateCategory($id, $data);
        Session::flash('success', 'Category updated.');
        \Core\Response::redirect('/admin/shop/categories');
    }

    public function categoryDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        \Shop::deleteCategory($id);
        Session::flash('success', 'Category deleted.');
        \Core\Response::redirect('/admin/shop/categories');
    }

    // ─── ORDERS ───

    public function orders(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $filters = [
            'search' => $_GET['q'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \Shop::getOrders($filters, $page, 20);
        render('admin/shop/orders', [
            'orders' => $result['orders'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters' => $filters,
        ]);
    }

    public function orderView(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $order = \Shop::getOrder($id);
        if (!$order) {
            Session::flash('error', 'Order not found.');
            \Core\Response::redirect('/admin/shop/orders');
        }
        render('admin/shop/order-view', ['order' => $order]);
    }

    public function orderUpdateStatus(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $status = $_POST['status'] ?? '';
        $paymentStatus = $_POST['payment_status'] ?? '';

        if ($status) {
            \Shop::updateOrderStatus($id, $status);
        }
        if ($paymentStatus && in_array($paymentStatus, ['unpaid', 'paid', 'refunded'])) {
            db()->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$paymentStatus, $id]);
        }

        Session::flash('success', 'Order updated.');
        \Core\Response::redirect("/admin/shop/orders/{$id}");
    }

    // ─── SETTINGS ───

    public function settings(Request $request): void
    {
        Session::requireRole('admin');
        $settings = [
            'shop_name' => get_setting('shop_name', 'Shop'),
            'shop_currency' => get_setting('shop_currency', 'USD'),
            'shop_tax_rate' => get_setting('shop_tax_rate', '0'),
            'shop_shipping_cost' => get_setting('shop_shipping_cost', '0'),
            'shop_free_shipping_threshold' => get_setting('shop_free_shipping_threshold', '0'),
            'shop_notification_email' => get_setting('shop_notification_email', ''),
            'shop_low_stock_threshold' => get_setting('shop_low_stock_threshold', '5'),
            'shop_email_order_confirm' => get_setting('shop_email_order_confirm', '1'),
            'shop_email_admin_notify' => get_setting('shop_email_admin_notify', '1'),
            'shop_email_status_update' => get_setting('shop_email_status_update', '1'),
            'company_name' => get_setting('company_name', ''),
            'company_address' => get_setting('company_address', ''),
            'company_tax_id' => get_setting('company_tax_id', ''),
            'company_email' => get_setting('company_email', ''),
            'company_phone' => get_setting('company_phone', ''),
        ];
        render('admin/shop/settings', ['settings' => $settings]);
    }

    public function settingsSave(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $keys = [
            'shop_name', 'shop_currency', 'shop_tax_rate', 'shop_shipping_cost', 'shop_free_shipping_threshold',
            'shop_notification_email', 'shop_low_stock_threshold',
            'shop_email_order_confirm', 'shop_email_admin_notify', 'shop_email_status_update',
            'company_name', 'company_address', 'company_tax_id', 'company_email', 'company_phone',
        ];
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $val = $_POST[$key];
                $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                $stmt->execute([$key, $val, $val]);
            }
        }
        Session::flash('success', 'Shop settings saved.');
        \Core\Response::redirect('/admin/shop/settings');
    }

    // ─── DIGITAL UPLOAD ───

    public function digitalUpload(Request $request): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');

        if (empty($_FILES['digital_file']) || $_FILES['digital_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['ok' => false, 'error' => 'No file uploaded or upload error.']);
            exit;
        }

        $file = $_FILES['digital_file'];
        $uploadDir = CMS_ROOT . '/uploads/digital/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // Sanitize filename
        $originalName = basename($file['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $safeName = time() . '_' . $safeName;
        $destPath = $uploadDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $relativePath = 'uploads/digital/' . $safeName;
            echo json_encode(['ok' => true, 'path' => $relativePath, 'filename' => $originalName]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Failed to move uploaded file.']);
        }
        exit;
    }

    // ─── INVOICE ───

    public function orderInvoice(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $order = \Shop::getOrder($id);
        if (!$order) {
            Session::flash('error', 'Order not found.');
            \Core\Response::redirect('/admin/shop/orders');
        }

        require_once CMS_ROOT . '/core/shop-invoice.php';
        \ShopInvoice::stream($id);
        exit;
    }

    // ─── COUPONS ───

    private function loadCoupons(): void
    {
        require_once CMS_ROOT . '/core/shop-coupons.php';
    }

    public function coupons(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCoupons();
        $filters = [
            'search' => $_GET['q'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \ShopCoupons::getAll($filters, $page, 20);
        $stats = \ShopCoupons::getStats();
        render('admin/shop/coupons', [
            'coupons' => $result['coupons'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function couponCreate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $categories = \Shop::getCategories();
        render('admin/shop/coupon-form', ['coupon' => null, 'categories' => $categories]);
    }

    public function couponEdit(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCoupons();
        $this->loadShop();
        $id = (int)$request->param('id');
        $coupon = \ShopCoupons::get($id);
        if (!$coupon) {
            Session::flash('error', 'Coupon not found.');
            \Core\Response::redirect('/admin/shop/coupons');
        }
        $categories = \Shop::getCategories();
        render('admin/shop/coupon-form', ['coupon' => $coupon, 'categories' => $categories]);
    }

    public function couponStore(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCoupons();
        $data = $_POST;
        unset($data['csrf_token']);
        $id = \ShopCoupons::create($data);
        if ($id) {
            Session::flash('success', 'Coupon created successfully.');
        } else {
            Session::flash('error', 'Failed to create coupon.');
        }
        \Core\Response::redirect('/admin/shop/coupons');
    }

    public function couponUpdate(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCoupons();
        $id = (int)$request->param('id');
        $data = $_POST;
        unset($data['csrf_token']);
        \ShopCoupons::update($id, $data);
        Session::flash('success', 'Coupon updated.');
        \Core\Response::redirect("/admin/shop/coupons/{$id}/edit");
    }

    public function couponDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCoupons();
        $id = (int)$request->param('id');
        \ShopCoupons::delete($id);
        Session::flash('success', 'Coupon deleted.');
        \Core\Response::redirect('/admin/shop/coupons');
    }

    public function couponToggle(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadCoupons();
        $id = (int)$request->param('id');
        $coupon = \ShopCoupons::get($id);
        if ($coupon) {
            $newStatus = $coupon['status'] === 'active' ? 'inactive' : 'active';
            \ShopCoupons::update($id, ['status' => $newStatus]);
            Session::flash('success', 'Coupon ' . ($newStatus === 'active' ? 'activated' : 'deactivated') . '.');
        }
        \Core\Response::redirect('/admin/shop/coupons');
    }

    // ─── REVIEWS ───

    private function loadReviews(): void
    {
        require_once CMS_ROOT . '/core/shop-reviews.php';
    }

    public function reviews(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadReviews();
        $filters = [
            'status' => $_GET['status'] ?? '',
            'product_id' => $_GET['product_id'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \ShopReviews::getAll($filters, $page, 20);
        $stats = \ShopReviews::getStats();
        render('admin/shop/reviews', [
            'reviews' => $result['reviews'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function reviewApprove(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadReviews();
        $id = (int)$request->param('id');
        \ShopReviews::approve($id);
        Session::flash('success', 'Review approved.');
        \Core\Response::redirect('/admin/shop/reviews');
    }

    public function reviewReject(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadReviews();
        $id = (int)$request->param('id');
        \ShopReviews::reject($id);
        Session::flash('success', 'Review rejected.');
        \Core\Response::redirect('/admin/shop/reviews');
    }

    public function reviewDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadReviews();
        $id = (int)$request->param('id');
        \ShopReviews::delete($id);
        Session::flash('success', 'Review deleted.');
        \Core\Response::redirect('/admin/shop/reviews');
    }

    public function reviewReply(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadReviews();
        $id = (int)$request->param('id');
        $reply = trim($_POST['admin_reply'] ?? '');
        if ($reply !== '') {
            \ShopReviews::reply($id, $reply);
            Session::flash('success', 'Reply saved.');
        }
        \Core\Response::redirect('/admin/shop/reviews');
    }

    // ─── ORDER TRACKING ───

    public function orderUpdateTracking(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadShop();
        $id = (int)$request->param('id');
        $trackingNumber = trim($_POST['tracking_number'] ?? '');
        if ($trackingNumber !== '') {
            \Shop::updateTracking($id, $trackingNumber);
            Session::flash('success', 'Tracking updated and customer notified.');
        }
        \Core\Response::redirect("/admin/shop/orders/{$id}");
    }

    // ─── ABANDONED CARTS ───

    private function loadAbandonedCarts(): void
    {
        require_once CMS_ROOT . '/core/shop-abandoned-carts.php';
    }

    public function abandonedCarts(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadAbandonedCarts();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \AbandonedCarts::getAll($page, 20);
        $stats = \AbandonedCarts::getStats();
        render('admin/shop/abandoned-carts', [
            'carts' => $result['carts'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'stats' => $stats,
        ]);
    }

    public function abandonedCartsSendReminders(Request $request): void
    {
        Session::requireRole('admin');
        $this->loadAbandonedCarts();
        $result = \AbandonedCarts::sendReminders();
        Session::flash('success', "Reminders sent: {$result['sent']} successful, {$result['failed']} failed.");
        \Core\Response::redirect('/admin/shop/abandoned-carts');
    }

    // ─── ANALYTICS ───

    public function analytics(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/shop-analytics.php';
        $days = max(7, min(90, (int)($_GET['days'] ?? 30)));
        $revenueChart = \ShopAnalytics::getRevenueChart($days);
        $bestsellers = \ShopAnalytics::getBestsellers(10, $days);
        $funnel = \ShopAnalytics::getConversionFunnel($days);
        $kpis = \ShopAnalytics::getKPIs($days);
        $topCategories = \ShopAnalytics::getTopCategories(5, $days);
        $popularSearches = \ShopAnalytics::getPopularSearches(10, $days);
        $hourly = \ShopAnalytics::getHourlyDistribution($days);
        render('admin/shop/analytics', compact('days', 'revenueChart', 'bestsellers', 'funnel', 'kpis', 'topCategories', 'popularSearches', 'hourly'));
    }

    // ─── AI ───

    public function aiGenerate(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = $input['name'] ?? '';
        $category = $input['category'] ?? '';
        $features = $input['features'] ?? '';
        $tone = $input['tone'] ?? 'professional';
        $language = $input['language'] ?? 'en';

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::generateProductCopy($name, $category, $features, $tone, $language);
        echo json_encode($result);
        exit;
    }

    public function aiSeo(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::generateSEO($productId);
        echo json_encode($result);
        exit;
    }

    public function aiPrice(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::suggestPrice($productId);
        echo json_encode($result);
        exit;
    }

    public function aiReviewSummary(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::summarizeReviews($productId);
        echo json_encode($result);
        exit;
    }

    // ─── AI SEO ANALYZE ───

    public function aiSeoAnalyze(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $focusKeyword = trim((string)($input['focus_keyword'] ?? ''));
        $language = trim((string)($input['language'] ?? 'en'));

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::analyzeSEO($productId, $focusKeyword, $language);
        echo json_encode($result);
        exit;
    }

    // ─── AI KEYWORD RESEARCH ───

    public function aiKeywords(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $language = trim((string)($input['language'] ?? 'en'));

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::keywordResearch($productId, $language);
        echo json_encode($result);
        exit;
    }

    // ─── AI CONTENT REWRITE ───

    public function aiRewrite(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $mode = trim((string)($input['mode'] ?? 'seo'));
        $field = trim((string)($input['field'] ?? 'description'));
        $options = $input['options'] ?? [];

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::rewriteContent($productId, $mode, $field, is_array($options) ? $options : []);
        echo json_encode($result);
        exit;
    }

    // ─── AI CATEGORY DESCRIPTION ───

    public function aiCategoryDescription(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $categoryId = (int)($input['category_id'] ?? 0);
        $tone = trim((string)($input['tone'] ?? 'professional'));
        $language = trim((string)($input['language'] ?? 'en'));

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::generateCategoryDescription($categoryId, $tone, $language);
        echo json_encode($result);
        exit;
    }

    // ─── BULK SEO ───

    public function aiBulkSeoScan(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::bulkSEOScan();
        echo json_encode($result);
        exit;
    }

    public function aiBulkGenerateSeo(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productIds = $input['product_ids'] ?? [];
        $overwrite = !empty($input['overwrite']);

        if (!is_array($productIds) || empty($productIds)) {
            echo json_encode(['ok' => false, 'error' => 'No product IDs provided']);
            exit;
        }

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::bulkGenerateSEO($productIds, $overwrite);
        echo json_encode($result);
        exit;
    }

    public function aiBulkRewrite(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productIds = $input['product_ids'] ?? [];
        $mode = trim((string)($input['mode'] ?? 'seo'));
        $apply = !empty($input['apply']);

        if (!is_array($productIds) || empty($productIds)) {
            echo json_encode(['ok' => false, 'error' => 'No product IDs provided']);
            exit;
        }

        require_once CMS_ROOT . '/core/shop-ai.php';
        $result = \ShopAI::bulkRewrite($productIds, $mode, $apply);
        echo json_encode($result);
        exit;
    }

    // ─── AI IMAGE PROCESSING ───

    public function aiRemoveBg(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $imageUrl = trim((string)($input['image_url'] ?? ''));

        if (!$productId && !$imageUrl) {
            echo json_encode(['ok' => false, 'error' => 'product_id or image_url required']);
            exit;
        }

        require_once CMS_ROOT . '/core/shop-ai-images.php';

        if ($productId) {
            $this->loadShop();
            $product = \Shop::getProduct($productId);
            if (!$product || empty($product['image'])) {
                echo json_encode(['ok' => false, 'error' => 'Product not found or has no image']);
                exit;
            }
            $imageUrl = $product['image'];
        }

        $result = \ShopAIImages::removeBackground($imageUrl, $productId ?: null);
        echo json_encode($result);
        exit;
    }

    public function aiAltText(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $imageUrl = trim((string)($input['image_url'] ?? ''));
        $productName = trim((string)($input['product_name'] ?? ''));

        if (!$productId && !$imageUrl) {
            echo json_encode(['ok' => false, 'error' => 'product_id or image_url required']);
            exit;
        }

        require_once CMS_ROOT . '/core/shop-ai-images.php';

        if ($productId) {
            $this->loadShop();
            $product = \Shop::getProduct($productId);
            if (!$product || empty($product['image'])) {
                echo json_encode(['ok' => false, 'error' => 'Product not found or has no image']);
                exit;
            }
            $imageUrl = $product['image'];
            if ($productName === '') {
                $productName = $product['name'] ?? '';
            }
        }

        $result = \ShopAIImages::generateAltText($imageUrl, $productName);
        echo json_encode($result);
        exit;
    }

    public function aiEnhanceImage(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $imageUrl = trim((string)($input['image_url'] ?? ''));
        $prompt = trim((string)($input['prompt'] ?? ''));

        if (!$productId && !$imageUrl) {
            echo json_encode(['ok' => false, 'error' => 'product_id or image_url required']);
            exit;
        }

        require_once CMS_ROOT . '/core/shop-ai-images.php';

        if ($productId) {
            $this->loadShop();
            $product = \Shop::getProduct($productId);
            if (!$product || empty($product['image'])) {
                echo json_encode(['ok' => false, 'error' => 'Product not found or has no image']);
                exit;
            }
            $imageUrl = $product['image'];
        }

        $result = \ShopAIImages::enhanceImage($imageUrl, $prompt, $productId ?: null);
        echo json_encode($result);
        exit;
    }

    public function aiGenerateImage(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $prompt = trim((string)($input['prompt'] ?? ''));
        $productId = (int)($input['product_id'] ?? 0);

        if ($prompt === '') {
            echo json_encode(['ok' => false, 'error' => 'Prompt is required']);
            exit;
        }

        require_once CMS_ROOT . '/core/shop-ai-images.php';
        $result = \ShopAIImages::generateProductImage($prompt, $productId ?: null);
        echo json_encode($result);
        exit;
    }

    public function aiProcessImages(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $tasks = $input['tasks'] ?? ['alt_text'];

        if (!$productId) {
            echo json_encode(['ok' => false, 'error' => 'product_id required']);
            exit;
        }

        if (!is_array($tasks)) {
            $tasks = ['alt_text'];
        }

        require_once CMS_ROOT . '/core/shop-ai-images.php';
        $result = \ShopAIImages::processProduct($productId, $tasks);
        echo json_encode($result);
        exit;
    }

    // ─── SEO DASHBOARD ───

    public function seo(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/shop-ai.php';
        $scanResult = \ShopAI::bulkSEOScan();
        render('admin/shop/seo-dashboard', [
            'products' => $scanResult['products'] ?? [],
            'summary'  => $scanResult['summary'] ?? [],
        ]);
    }
}
