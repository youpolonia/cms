<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class WhiteLabelController
{
    /**
     * GET /admin/white-label — White-label settings
     */
    public function index(Request $request): void
    {
        $pdo = db();
        $rows = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'wl_%'")->fetchAll(\PDO::FETCH_KEY_PAIR);

        $data = [
            'title' => 'White Label',
            'settings' => $rows,
            'csrfToken' => csrf_token(),
        ];

        extract($data);
        ob_start();
        require \CMS_APP . '/views/admin/white-label/index.php';
        $pageContent = ob_get_clean();
        echo $pageContent;
        exit;
    }

    /**
     * POST /admin/white-label/save — Save white-label settings
     */
    public function save(Request $request): void
    {
        csrf_validate_or_403();
        $pdo = db();

        $fields = [
            'wl_admin_name', 'wl_admin_icon', 'wl_admin_accent',
            'wl_admin_footer', 'wl_login_title', 'wl_login_subtitle',
        ];

        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = :v2");
        foreach ($fields as $key) {
            $val = trim($_POST[$key] ?? '');
            $stmt->execute(['k' => $key, 'v' => $val, 'v2' => $val]);
        }

        // Handle checkbox
        $hideBranding = isset($_POST['wl_hide_branding']) ? '1' : '0';
        $stmt->execute(['k' => 'wl_hide_branding', 'v' => $hideBranding, 'v2' => $hideBranding]);

        // Handle logo upload
        if (!empty($_FILES['wl_admin_logo']['tmp_name'])) {
            $uploadDir = \CMS_ROOT . '/uploads/branding/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['wl_admin_logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif'])) {
                $filename = 'admin-logo.' . $ext;
                move_uploaded_file($_FILES['wl_admin_logo']['tmp_name'], $uploadDir . $filename);
                $logoUrl = '/uploads/branding/' . $filename;
                $stmt->execute(['k' => 'wl_admin_logo', 'v' => $logoUrl, 'v2' => $logoUrl]);
            }
        }

        // Clear logo if requested
        if (!empty($_POST['clear_logo'])) {
            $stmt->execute(['k' => 'wl_admin_logo', 'v' => '', 'v2' => '']);
        }

        $_SESSION['flash_success'] = "White-label settings saved.";
        Response::redirect('/admin/white-label');
    }
}
