<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class ApiKeysController
{
    /**
     * GET /admin/api-keys — Manage API keys
     */
    public function index(Request $request): void
    {
        $pdo = db();
        $keys = $pdo->query("SELECT * FROM api_keys ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'title' => 'API Keys',
            'keys' => $keys,
            'csrfToken' => csrf_token(),
        ];

        extract($data);
        ob_start();
        require \CMS_APP . '/views/admin/api-keys/index.php';
        $pageContent = ob_get_clean();
        echo $pageContent;
        exit;
    }

    /**
     * POST /admin/api-keys/create — Generate new API key
     */
    public function create(Request $request): void
    {
        csrf_validate_or_403();
        $pdo = db();

        $name = trim($_POST['name'] ?? 'API Key');
        $permissions = $_POST['permissions'] ?? ['*'];
        if (is_string($permissions)) {
            $permissions = array_map('trim', explode(',', $permissions));
        }

        $apiKey = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare(
            "INSERT INTO api_keys (name, api_key, permissions) VALUES (:name, :key, :perms)"
        );
        $stmt->execute([
            'name' => $name,
            'key' => $apiKey,
            'perms' => json_encode($permissions),
        ]);

        $_SESSION['flash_success'] = "API key created: {$apiKey}";
        Response::redirect('/admin/api-keys');
    }

    /**
     * POST /admin/api-keys/{id}/toggle — Enable/disable key
     */
    public function toggle(int $id): void
    {
        csrf_validate_or_403();
        $pdo = db();

        $pdo->prepare("UPDATE api_keys SET is_active = NOT is_active WHERE id = :id")
            ->execute(['id' => $id]);

        $_SESSION['flash_success'] = "API key status updated.";
        Response::redirect('/admin/api-keys');
    }

    /**
     * POST /admin/api-keys/{id}/delete — Delete key
     */
    public function delete(int $id): void
    {
        csrf_validate_or_403();
        $pdo = db();

        $pdo->prepare("DELETE FROM api_keys WHERE id = :id")->execute(['id' => $id]);

        $_SESSION['flash_success'] = "API key deleted.";
        Response::redirect('/admin/api-keys');
    }
}
