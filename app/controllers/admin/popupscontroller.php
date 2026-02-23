<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class PopupsController
{
    private static bool $migrated = false;

    private function ensureSettingsColumn(): void
    {
        if (self::$migrated) return;
        self::$migrated = true;
        try {
            db()->exec("ALTER TABLE popups ADD COLUMN IF NOT EXISTS settings JSON DEFAULT NULL");
        } catch (\Throwable $e) {
            // Column may already exist
        }
    }

    /**
     * List all popups with stats
     * GET /admin/popups
     */
    public function index(): void
    {
        $pdo = db();
        $popups = $pdo->query(
            "SELECT * FROM popups ORDER BY created_at DESC"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $totalViews = 0;
        $totalSubmissions = 0;
        $active = 0;
        foreach ($popups as &$p) {
            $p['conversion'] = ($p['views'] > 0) ? round($p['submissions'] / $p['views'] * 100, 1) : 0;
            $totalViews += (int)$p['views'];
            $totalSubmissions += (int)$p['submissions'];
            if ($p['active']) $active++;
        }
        unset($p);

        $stats = [
            'total'       => count($popups),
            'active'      => $active,
            'views'       => $totalViews,
            'submissions' => $totalSubmissions,
        ];

        $title = 'Pop-ups';
        ob_start();
        require CMS_APP . '/views/admin/popups/index.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Show create form
     * GET /admin/popups/create
     */
    public function create(): void
    {
        $popup = null;
        $title = 'Create Pop-up';
        ob_start();
        require CMS_APP . '/views/admin/popups/form.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Show edit form
     * GET /admin/popups/{id}/edit
     */
    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM popups WHERE id = ?");
        $stmt->execute([$id]);
        $popup = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$popup) {
            $_SESSION['flash_error'] = 'Popup not found.';
            \Core\Response::redirect('/admin/popups');
        }

        $title = 'Edit Pop-up: ' . ($popup['name'] ?? '');
        ob_start();
        require CMS_APP . '/views/admin/popups/form.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Store new popup
     * POST /admin/popups/store
     */
    public function store(): void
    {
        $this->ensureSettingsColumn();
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO popups (name, content, type, trigger_type, trigger_value, position,
             show_on, hide_on, cta_text, cta_url, cta_action, form_fields,
             bg_color, text_color, btn_color, image, show_once, active, start_date, end_date, settings)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );

        $formFields = [];
        if (!empty($_POST['form_field_email'])) $formFields[] = 'email';
        if (!empty($_POST['form_field_name'])) $formFields[] = 'name';
        if (!empty($_POST['form_field_phone'])) $formFields[] = 'phone';

        // Build settings JSON for coupon integration
        $settings = [];
        if (!empty($_POST['generate_coupon'])) {
            $settings['generate_coupon'] = true;
            $settings['coupon_value'] = (float)($_POST['coupon_value'] ?? 10);
        }

        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['content'] ?? ''),
            $_POST['type'] ?? 'modal',
            $_POST['trigger_type'] ?? 'delay',
            trim($_POST['trigger_value'] ?? '3'),
            $_POST['position'] ?? 'center',
            trim($_POST['show_on'] ?? '*'),
            trim($_POST['hide_on'] ?? ''),
            trim($_POST['cta_text'] ?? 'Subscribe'),
            trim($_POST['cta_url'] ?? ''),
            $_POST['cta_action'] ?? 'close',
            json_encode($formFields),
            $_POST['bg_color'] ?? '#1e293b',
            $_POST['text_color'] ?? '#e2e8f0',
            $_POST['btn_color'] ?? '#6366f1',
            trim($_POST['image'] ?? ''),
            isset($_POST['show_once']) ? 1 : 0,
            isset($_POST['active']) ? 1 : 0,
            !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            !empty($settings) ? json_encode($settings) : null,
        ]);

        $_SESSION['flash_success'] = 'Popup created successfully.';
        \Core\Response::redirect('/admin/popups');
    }

    /**
     * Update existing popup
     * POST /admin/popups/{id}/update
     */
    public function update(Request $request): void
    {
        $this->ensureSettingsColumn();
        $id = (int)$request->param('id');
        $pdo = db();

        $formFields = [];
        if (!empty($_POST['form_field_email'])) $formFields[] = 'email';
        if (!empty($_POST['form_field_name'])) $formFields[] = 'name';
        if (!empty($_POST['form_field_phone'])) $formFields[] = 'phone';

        $stmt = $pdo->prepare(
            "UPDATE popups SET name=?, content=?, type=?, trigger_type=?, trigger_value=?,
             position=?, show_on=?, hide_on=?, cta_text=?, cta_url=?, cta_action=?,
             form_fields=?, bg_color=?, text_color=?, btn_color=?, image=?,
             show_once=?, active=?, start_date=?, end_date=?, settings=?
             WHERE id=?"
        );

        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['content'] ?? ''),
            $_POST['type'] ?? 'modal',
            $_POST['trigger_type'] ?? 'delay',
            trim($_POST['trigger_value'] ?? '3'),
            $_POST['position'] ?? 'center',
            trim($_POST['show_on'] ?? '*'),
            trim($_POST['hide_on'] ?? ''),
            trim($_POST['cta_text'] ?? 'Subscribe'),
            trim($_POST['cta_url'] ?? ''),
            $_POST['cta_action'] ?? 'close',
            json_encode($formFields),
            $_POST['bg_color'] ?? '#1e293b',
            $_POST['text_color'] ?? '#e2e8f0',
            $_POST['btn_color'] ?? '#6366f1',
            trim($_POST['image'] ?? ''),
            isset($_POST['show_once']) ? 1 : 0,
            isset($_POST['active']) ? 1 : 0,
            !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            !empty($settings) ? json_encode($settings) : null,
            $id,
        ]);

        $_SESSION['flash_success'] = 'Popup updated successfully.';
        \Core\Response::redirect('/admin/popups');
    }

    /**
     * Delete popup
     * POST /admin/popups/{id}/delete
     */
    public function delete(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $pdo->prepare("DELETE FROM popup_submissions WHERE popup_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM popups WHERE id = ?")->execute([$id]);
        $_SESSION['flash_success'] = 'Popup deleted.';
        \Core\Response::redirect('/admin/popups');
    }

    /**
     * Toggle active/inactive
     * POST /admin/popups/{id}/toggle
     */
    public function toggle(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $pdo->prepare("UPDATE popups SET active = NOT active WHERE id = ?")->execute([$id]);
        $_SESSION['flash_success'] = 'Popup status toggled.';
        \Core\Response::redirect('/admin/popups');
    }

    /**
     * List submissions for a popup
     * GET /admin/popups/{id}/submissions
     */
    public function submissions(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();

        $stmt = $pdo->prepare("SELECT * FROM popups WHERE id = ?");
        $stmt->execute([$id]);
        $popup = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$popup) {
            $_SESSION['flash_error'] = 'Popup not found.';
            \Core\Response::redirect('/admin/popups');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM popup_submissions WHERE popup_id = ?");
        $countStmt->execute([$id]);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $stmt = $pdo->prepare(
            "SELECT * FROM popup_submissions WHERE popup_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$id, $perPage, $offset]);
        $submissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode data JSON for each submission
        foreach ($submissions as &$sub) {
            $sub['parsed'] = json_decode($sub['data'] ?? '{}', true) ?: [];
        }
        unset($sub);

        $title = 'Submissions: ' . ($popup['name'] ?? '');
        ob_start();
        require CMS_APP . '/views/admin/popups/submissions.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Export submissions as CSV
     * GET /admin/popups/{id}/export
     */
    public function exportCsv(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();

        $stmt = $pdo->prepare("SELECT name FROM popups WHERE id = ?");
        $stmt->execute([$id]);
        $popupName = $stmt->fetchColumn();

        if (!$popupName) {
            http_response_code(404);
            echo 'Popup not found';
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM popup_submissions WHERE popup_id = ? ORDER BY created_at DESC");
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $filename = preg_replace('/[^a-z0-9_-]/i', '_', $popupName) . '_submissions_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Email', 'Name', 'Phone', 'Page URL', 'IP Address', 'Date']);

        foreach ($rows as $r) {
            $data = json_decode($r['data'] ?? '{}', true) ?: [];
            fputcsv($out, [
                $r['id'],
                $data['email'] ?? '',
                $data['name'] ?? '',
                $data['phone'] ?? '',
                $r['page_url'] ?? '',
                $r['ip_address'] ?? '',
                $r['created_at'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    // ─── PUBLIC API ENDPOINTS ───

    /**
     * GET /api/popups — returns active popups for frontend
     */
    public function apiList(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=60');

        $pdo = db();
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            "SELECT id, name, content, type, trigger_type, trigger_value, position,
                    show_on, hide_on, cta_text, cta_url, cta_action, form_fields,
                    bg_color, text_color, btn_color, image, show_once,
                    start_date, end_date
             FROM popups
             WHERE active = 1
               AND (start_date IS NULL OR start_date <= ?)
               AND (end_date IS NULL OR end_date >= ?)"
        );
        $stmt->execute([$now, $now]);
        $popups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($popups as &$p) {
            $p['form_fields'] = json_decode($p['form_fields'] ?? '[]', true) ?: [];
        }
        unset($p);

        echo json_encode(['popups' => $popups]);
    }

    /**
     * POST /api/popup-submit — handle form submission
     */
    public function apiSubmit(): void
    {
        $this->ensureSettingsColumn();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $popupId = (int)($input['popup_id'] ?? 0);

        if (!$popupId) {
            echo json_encode(['ok' => false, 'error' => 'Missing popup_id']);
            return;
        }

        $pdo = db();

        // Get popup info
        $stmt = $pdo->prepare("SELECT id, name, settings FROM popups WHERE id = ?");
        $stmt->execute([$popupId]);
        $popup = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$popup) {
            echo json_encode(['ok' => false, 'error' => 'Popup not found']);
            return;
        }

        $email = trim($input['email'] ?? '');
        $name = trim($input['name'] ?? '');
        $phone = trim($input['phone'] ?? '');

        $data = json_encode(array_filter([
            'email' => $email,
            'name'  => $name,
            'phone' => $phone,
        ]));

        $pageUrl = trim($input['page_url'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        $stmt = $pdo->prepare(
            "INSERT INTO popup_submissions (popup_id, data, ip_address, page_url) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$popupId, $data, $ip, $pageUrl]);

        // Increment submissions counter
        $pdo->prepare("UPDATE popups SET submissions = submissions + 1 WHERE id = ?")->execute([$popupId]);

        // Event integration
        if (function_exists('cms_event')) {
            cms_event('popup.submitted', [
                'popup_id'   => $popupId,
                'email'      => $email,
                'popup_name' => $popup['name'],
            ]);
        }

        // Coupon generation integration
        $couponCode = null;
        $popupSettings = json_decode($popup['settings'] ?? '{}', true) ?: [];
        if (!empty($popupSettings['generate_coupon'])) {
            require_once CMS_ROOT . '/core/shop-coupons.php';
            $couponValue = (float)($popupSettings['coupon_value'] ?? 10);
            $code = \ShopCoupons::generateCode('POP');
            $validUntil = date('Y-m-d H:i:s', strtotime('+7 days'));
            \ShopCoupons::create([
                'code' => $code,
                'type' => 'percentage',
                'value' => $couponValue,
                'max_uses' => 1,
                'per_customer_limit' => 1,
                'valid_until' => $validUntil,
                'status' => 'active',
            ]);
            $couponCode = $code;
        }

        // CRM integration
        if ($email && file_exists(CMS_ROOT . '/core/crm_manager.php')) {
            require_once CMS_ROOT . '/core/crm_manager.php';
            $existing = $pdo->prepare("SELECT id FROM crm_contacts WHERE email = ?");
            $existing->execute([$email]);
            if (!$existing->fetch()) {
                \CrmManager::createContact([
                    'first_name' => $name ?: 'Subscriber',
                    'email'      => $email,
                    'source'     => 'popup',
                    'status'     => 'new',
                    'notes'      => 'Captured via popup: ' . ($popup['name'] ?? ''),
                ]);
            }
        }

        echo json_encode(array_filter(['ok' => true, 'coupon_code' => $couponCode]));
    }

    /**
     * POST /api/popup-track — track view/click
     */
    public function apiTrack(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $popupId = (int)($input['popup_id'] ?? 0);
        $type = ($input['type'] ?? '') === 'click' ? 'click' : 'view';

        if (!$popupId) {
            echo json_encode(['ok' => false]);
            return;
        }

        $col = $type === 'click' ? 'clicks' : 'views';
        db()->prepare("UPDATE popups SET {$col} = {$col} + 1 WHERE id = ?")->execute([$popupId]);

        echo json_encode(['ok' => true]);
    }
}
