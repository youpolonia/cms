<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class DropshippingController
{
    private function load(): void
    {
        require_once CMS_ROOT . '/core/dropshipping.php';
    }

    // ─── DASHBOARD ───

    public function dashboard(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $stats = \Dropshipping::getDashboardStats();
        render('admin/dropshipping/dashboard', ['stats' => $stats]);
    }

    // ─── SUPPLIERS ───

    public function suppliers(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $filters = ['search' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? ''];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \Dropshipping::getSuppliers($filters, $page, 20);
        render('admin/dropshipping/suppliers', [
            'suppliers'  => $result['suppliers'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters'    => $filters,
        ]);
    }

    public function supplierCreate(Request $request): void
    {
        Session::requireRole('admin');
        render('admin/dropshipping/supplier-form', ['supplier' => null]);
    }

    public function supplierEdit(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $id = (int)$request->param('id');
        $supplier = \Dropshipping::getSupplier($id);
        if (!$supplier) {
            Session::flash('error', 'Supplier not found.');
            \Core\Response::redirect('/admin/dropshipping/suppliers');
        }
        render('admin/dropshipping/supplier-form', ['supplier' => $supplier]);
    }

    public function supplierStore(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $data = $_POST;
        unset($data['csrf_token']);
        $id = \Dropshipping::createSupplier($data);
        if ($id) {
            Session::flash('success', 'Supplier created successfully.');
        } else {
            Session::flash('error', 'Failed to create supplier.');
        }
        \Core\Response::redirect('/admin/dropshipping/suppliers');
    }

    public function supplierUpdate(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $id = (int)$request->param('id');
        $data = $_POST;
        unset($data['csrf_token']);
        \Dropshipping::updateSupplier($id, $data);
        Session::flash('success', 'Supplier updated.');
        \Core\Response::redirect("/admin/dropshipping/suppliers/{$id}/edit");
    }

    public function supplierDelete(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $id = (int)$request->param('id');
        \Dropshipping::deleteSupplier($id);
        Session::flash('success', 'Supplier deleted.');
        \Core\Response::redirect('/admin/dropshipping/suppliers');
    }

    // ─── PRODUCTS ───

    public function products(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $filters = [
            'search'      => $_GET['q'] ?? '',
            'supplier_id' => $_GET['supplier'] ?? '',
            'sync_status' => $_GET['sync'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = \Dropshipping::getProductLinks($filters, $page, 20);
        $suppliers = \Dropshipping::getAllSuppliers();
        render('admin/dropshipping/products', [
            'links'      => $result['links'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters'    => $filters,
            'suppliers'  => $suppliers,
        ]);
    }

    // ─── IMPORT ───

    public function import(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $suppliers = \Dropshipping::getAllSuppliers();
        $recentImports = \Dropshipping::getImports(1, 10);
        render('admin/dropshipping/import', [
            'suppliers' => $suppliers,
            'imports'   => $recentImports['imports'],
        ]);
    }

    // ─── PRICE RULES ───

    public function priceRules(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        require_once CMS_ROOT . '/core/dropshipping-pricing.php';
        $rules = \DSPricing::getRules();
        $suppliers = \Dropshipping::getAllSuppliers();
        render('admin/dropshipping/price-rules', [
            'rules'     => $rules,
            'suppliers' => $suppliers,
        ]);
    }

    public function priceRuleStore(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/dropshipping-pricing.php';
        $data = $_POST;
        unset($data['csrf_token']);
        \DSPricing::createRule($data);
        Session::flash('success', 'Price rule created.');
        \Core\Response::redirect('/admin/dropshipping/price-rules');
    }

    public function priceRuleUpdate(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/dropshipping-pricing.php';
        $id = (int)$request->param('id');
        $data = $_POST;
        unset($data['csrf_token']);
        \DSPricing::updateRule($id, $data);
        Session::flash('success', 'Price rule updated.');
        \Core\Response::redirect('/admin/dropshipping/price-rules');
    }

    public function priceRuleDelete(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/dropshipping-pricing.php';
        $id = (int)$request->param('id');
        \DSPricing::deleteRule($id);
        Session::flash('success', 'Price rule deleted.');
        \Core\Response::redirect('/admin/dropshipping/price-rules');
    }

    // ─── ORDERS ───

    public function orders(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = (int)$pdo->query("SELECT COUNT(*) FROM ds_order_forwards")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $orders = $pdo->prepare("
            SELECT f.*, s.name AS supplier_name, o.total AS order_total,
                   o.customer_name, o.customer_email
            FROM ds_order_forwards f
            LEFT JOIN ds_suppliers s ON f.supplier_id = s.id
            LEFT JOIN orders o ON f.order_id = o.id
            ORDER BY f.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $orders->execute();

        render('admin/dropshipping/orders', [
            'orders'     => $orders->fetchAll(\PDO::FETCH_ASSOC),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    // ═══ API ENDPOINTS ═══

    public function apiImportUrl(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $url = trim((string)($input['url'] ?? ''));
        $supplierId = !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null;
        $options = [
            'ai_rewrite' => ($input['ai_rewrite'] ?? true),
            'ai_seo'     => ($input['ai_seo'] ?? true),
            'ai_images'  => ($input['ai_images'] ?? false),
            'language'   => $input['language'] ?? 'en',
            'tone'       => $input['tone'] ?? 'professional',
        ];

        $this->load();
        require_once CMS_ROOT . '/core/dropshipping-importer.php';
        $result = \DSImporter::importFromUrl($url, $supplierId, $options);
        echo json_encode($result);
        exit;
    }

    public function apiImportBatch(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $urls = $input['urls'] ?? [];
        if (is_string($urls)) {
            $urls = array_filter(array_map('trim', explode("\n", $urls)));
        }
        $supplierId = !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null;
        $options = [
            'ai_rewrite' => ($input['ai_rewrite'] ?? true),
            'ai_seo'     => ($input['ai_seo'] ?? true),
            'ai_images'  => ($input['ai_images'] ?? false),
            'language'   => $input['language'] ?? 'en',
            'tone'       => $input['tone'] ?? 'professional',
        ];

        $this->load();
        require_once CMS_ROOT . '/core/dropshipping-importer.php';
        $result = \DSImporter::importBatch($urls, $supplierId, $options);
        echo json_encode($result);
        exit;
    }

    public function apiImportCsv(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $csvContent = $input['csv_content'] ?? '';
        $columnMap = $input['column_map'] ?? [];
        $supplierId = !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null;

        if (empty($csvContent) || empty($columnMap)) {
            echo json_encode(['ok' => false, 'error' => 'CSV content and column mapping required']);
            exit;
        }

        $this->load();
        require_once CMS_ROOT . '/core/dropshipping-importer.php';
        $result = \DSImporter::importFromCsv($csvContent, $columnMap, $supplierId);
        echo json_encode($result);
        exit;
    }

    public function apiLinkProduct(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $productId = (int)($input['product_id'] ?? 0);
        $supplierId = (int)($input['supplier_id'] ?? 0);

        if (!$productId || !$supplierId) {
            echo json_encode(['ok' => false, 'error' => 'product_id and supplier_id required']);
            exit;
        }

        $this->load();
        $linkId = \Dropshipping::linkProduct($productId, $supplierId, $input);
        echo json_encode(['ok' => true, 'link_id' => $linkId]);
        exit;
    }

    public function apiUnlinkProduct(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $productId = (int)($input['product_id'] ?? 0);
        $supplierId = (int)($input['supplier_id'] ?? 0);

        $this->load();
        \Dropshipping::unlinkProduct($productId, $supplierId);
        echo json_encode(['ok' => true]);
        exit;
    }

    public function apiCalculatePrice(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $supplierPrice = (float)($input['supplier_price'] ?? 0);
        $supplierId = !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null;
        $categoryId = !empty($input['category_id']) ? (int)$input['category_id'] : null;

        require_once CMS_ROOT . '/core/dropshipping-pricing.php';
        $result = \DSPricing::calculatePrice($supplierPrice, $categoryId, $supplierId);
        $previews = \DSPricing::previewAllRules($supplierPrice);

        echo json_encode(['ok' => true, 'result' => $result, 'all_rules' => $previews]);
        exit;
    }

    // ═══ FAZA 2: ORDERS & SYNC ═══

    public function orderDetail(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/dropshipping-orders.php';
        $id = (int)$request->param('id');
        $forward = \DSOrders::getForward($id);
        if (!$forward) {
            Session::flash('error', 'Forward not found.');
            \Core\Response::redirect('/admin/dropshipping/orders');
        }
        render('admin/dropshipping/order-detail', ['forward' => $forward]);
    }

    public function orderUpdateStatus(Request $request): void
    {
        Session::requireRole('admin');
        require_once CMS_ROOT . '/core/dropshipping-orders.php';
        $id = (int)$request->param('id');
        $status = $_POST['status'] ?? '';
        $tracking = $_POST['tracking_number'] ?? '';
        $notes = $_POST['notes'] ?? null;

        if ($tracking) {
            \DSOrders::updateTracking($id, $tracking, $_POST['tracking_url'] ?? '', $status ?: 'shipped');
        } elseif ($status) {
            \DSOrders::updateForwardStatus($id, $status, $notes);
        }

        Session::flash('success', 'Order updated.');
        \Core\Response::redirect('/admin/dropshipping/orders');
    }

    public function apiForwardOrder(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $orderId = (int)($input['order_id'] ?? 0);
        $supplierId = (int)($input['supplier_id'] ?? 0);

        if (!$orderId || !$supplierId) {
            echo json_encode(['ok' => false, 'error' => 'order_id and supplier_id required']);
            exit;
        }

        require_once CMS_ROOT . '/core/dropshipping-orders.php';
        $result = \DSOrders::manualForward($orderId, $supplierId);
        echo json_encode($result);
        exit;
    }

    public function apiSyncAll(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        require_once CMS_ROOT . '/core/dropshipping-sync.php';
        $result = \DSSync::syncAll();
        echo json_encode($result);
        exit;
    }

    public function apiSyncProduct(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $linkId = (int)($input['link_id'] ?? 0);

        if (!$linkId) {
            echo json_encode(['ok' => false, 'error' => 'link_id required']);
            exit;
        }

        require_once CMS_ROOT . '/core/dropshipping-sync.php';
        $result = \DSSync::syncSingle($linkId);
        echo json_encode($result);
        exit;
    }

    public function apiSyncTracking(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        require_once CMS_ROOT . '/core/dropshipping-orders.php';
        $result = \DSOrders::syncTracking();
        echo json_encode($result);
        exit;
    }

    // ═══ FAZA 3: AI RESEARCH ═══

    public function research(Request $request): void
    {
        Session::requireRole('admin');
        render('admin/dropshipping/research');
    }

    public function apiAiScout(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $url = trim((string)($input['url'] ?? ''));
        if (!$url) {
            echo json_encode(['ok' => false, 'error' => 'URL required']);
            exit;
        }

        require_once CMS_ROOT . '/core/dropshipping-ai.php';
        $result = \DSAI::scoutProduct($url, $input);
        echo json_encode($result);
        exit;
    }

    public function apiAiNiches(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        require_once CMS_ROOT . '/core/dropshipping-ai.php';
        $result = \DSAI::findNiches($input);
        echo json_encode($result);
        exit;
    }

    public function apiAiCompetition(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $query = trim((string)($input['query'] ?? ''));
        if (!$query) {
            echo json_encode(['ok' => false, 'error' => 'Query required']);
            exit;
        }

        require_once CMS_ROOT . '/core/dropshipping-ai.php';
        $result = \DSAI::analyzeCompetition($query, $input);
        echo json_encode($result);
        exit;
    }

    public function apiProfitCalc(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        require_once CMS_ROOT . '/core/dropshipping-ai.php';
        $result = \DSAI::calculateFullProfit($input);
        echo json_encode($result);
        exit;
    }

    public function apiAiTrends(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $category = trim((string)($input['category'] ?? ''));
        if (!$category) {
            echo json_encode(['ok' => false, 'error' => 'Category required']);
            exit;
        }

        require_once CMS_ROOT . '/core/dropshipping-ai.php';
        $result = \DSAI::analyzeTrends($category, $input);
        echo json_encode($result);
        exit;
    }

    public function apiOptimizeListing(): void
    {
        Session::requireRole('admin');
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $productId = (int)($input['product_id'] ?? 0);
        if (!$productId) {
            echo json_encode(['ok' => false, 'error' => 'product_id required']);
            exit;
        }

        require_once CMS_ROOT . '/core/dropshipping-ai.php';
        $result = \DSAI::optimizeListing($productId, $input['platform'] ?? 'general', $input['language'] ?? 'en');
        echo json_encode($result);
        exit;
    }

    // ═══ FAZA 4: SETTINGS ═══

    public function settings(Request $request): void
    {
        Session::requireRole('admin');
        $this->load();
        $pdo = db();

        $settings = [
            'table_count'     => 5,
            'suppliers'       => (int)$pdo->query("SELECT COUNT(*) FROM ds_suppliers")->fetchColumn(),
            'linked_products' => (int)$pdo->query("SELECT COUNT(*) FROM ds_product_links")->fetchColumn(),
            'price_rules'     => (int)$pdo->query("SELECT COUNT(*) FROM ds_price_rules WHERE status='active'")->fetchColumn(),
            'ai_provider'     => 'auto (CMS AI settings)',
            'last_sync'       => $pdo->query("SELECT MAX(last_sync_at) FROM ds_product_links")->fetchColumn() ?: 'Never',
        ];

        // Load saved settings from DB
        $saved = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'ds_%'")->fetchAll(\PDO::FETCH_KEY_PAIR);
        foreach ($saved as $k => $v) {
            $key = str_replace('ds_', '', $k);
            $settings[$key] = $v;
        }

        render('admin/dropshipping/settings', ['settings' => $settings]);
    }

    public function settingsSave(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $fields = [
            'default_ai_rewrite', 'default_ai_seo', 'default_ai_images',
            'default_language', 'default_tone', 'default_status',
            'auto_sync_enabled', 'sync_interval', 'low_margin_threshold', 'auto_disable_oos',
            'auto_forward', 'email_notify_supplier', 'email_tracking_customer',
        ];

        foreach ($fields as $field) {
            $value = $_POST[$field] ?? '0';
            $key = 'ds_' . $field;
            $existing = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
            $existing->execute([$key]);
            if ($existing->fetchColumn() > 0) {
                $pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?")->execute([$value, $key]);
            } else {
                $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)")->execute([$key, $value]);
            }
        }

        Session::flash('success', 'Settings saved.');
        \Core\Response::redirect('/admin/dropshipping/settings');
    }
}
