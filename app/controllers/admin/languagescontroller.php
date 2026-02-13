<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class LanguagesController
{
    /**
     * GET /admin/languages — Language management
     */
    public function index(Request $request): void
    {
        $pdo = db();
        $languages = $pdo->query("SELECT * FROM languages ORDER BY sort_order, name")->fetchAll(\PDO::FETCH_ASSOC);
        $defaultLocale = $pdo->query("SELECT code FROM languages WHERE is_default = 1 LIMIT 1")->fetchColumn() ?: 'en';

        // Count translations per locale
        $translationCounts = [];
        $rows = $pdo->query("SELECT locale, COUNT(*) as cnt FROM translations GROUP BY locale")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $r) $translationCounts[$r['locale']] = (int)$r['cnt'];

        // Count content translations
        $contentCounts = [];
        $rows = $pdo->query("SELECT locale, COUNT(*) as cnt FROM content_translations GROUP BY locale")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $r) $contentCounts[$r['locale']] = (int)$r['cnt'];

        $data = [
            'title' => 'Languages',
            'languages' => $languages,
            'defaultLocale' => $defaultLocale,
            'translationCounts' => $translationCounts,
            'contentCounts' => $contentCounts,
            'csrfToken' => csrf_token(),
        ];

        extract($data);
        ob_start();
        require \CMS_APP . '/views/admin/languages/index.php';
        $pageContent = ob_get_clean();
        echo $pageContent;
        exit;
    }

    /**
     * POST /admin/languages/toggle/{id} — Enable/disable language
     */
    public function toggle(int $id): void
    {
        csrf_validate_or_403();
        $pdo = db();

        // Don't allow disabling default language
        $lang = $pdo->prepare("SELECT * FROM languages WHERE id = :id");
        $lang->execute(['id' => $id]);
        $lang = $lang->fetch(\PDO::FETCH_ASSOC);

        if ($lang && $lang['is_default']) {
            $_SESSION['flash_error'] = "Cannot disable the default language.";
            Response::redirect('/admin/languages');
            return;
        }

        $pdo->prepare("UPDATE languages SET is_active = NOT is_active WHERE id = :id")->execute(['id' => $id]);
        $_SESSION['flash_success'] = "Language status updated.";
        Response::redirect('/admin/languages');
    }

    /**
     * POST /admin/languages/default/{id} — Set as default language
     */
    public function setDefault(int $id): void
    {
        csrf_validate_or_403();
        $pdo = db();

        $pdo->exec("UPDATE languages SET is_default = 0");
        $pdo->prepare("UPDATE languages SET is_default = 1, is_active = 1 WHERE id = :id")->execute(['id' => $id]);

        $_SESSION['flash_success'] = "Default language updated.";
        Response::redirect('/admin/languages');
    }

    /**
     * POST /admin/languages/add — Add custom language
     */
    public function add(Request $request): void
    {
        csrf_validate_or_403();
        $pdo = db();

        $code = strtolower(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $nativeName = trim($_POST['native_name'] ?? '');
        $direction = ($_POST['direction'] ?? 'ltr') === 'rtl' ? 'rtl' : 'ltr';

        if (strlen($code) < 2 || strlen($name) < 2) {
            $_SESSION['flash_error'] = "Language code and name are required.";
            Response::redirect('/admin/languages');
            return;
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO languages (code, name, native_name, direction, is_active, sort_order) 
                 VALUES (:code, :name, :native, :dir, 1, 99)"
            );
            $stmt->execute(['code' => $code, 'name' => $name, 'native' => $nativeName ?: $name, 'dir' => $direction]);
            $_SESSION['flash_success'] = "Language '{$name}' added.";
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Language code '{$code}' already exists.";
        }

        Response::redirect('/admin/languages');
    }

    /**
     * POST /api/languages/set — Set current locale (AJAX)
     */
    public function setLocale(): void
    {
        $locale = $_POST['locale'] ?? $_GET['locale'] ?? '';
        if ($locale) {
            $_SESSION['cms_locale'] = $locale;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'locale' => $locale]);
        exit;
    }
}
