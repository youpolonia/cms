<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class ChatSettingsController
{
    private const SETTINGS_KEYS = [
        'chatbot_enabled', 'chatbot_provider', 'chatbot_model',
        'chatbot_welcome', 'chatbot_suggestions', 'chatbot_custom_instructions'
    ];

    public function index(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();

        $settings = [];
        foreach (self::SETTINGS_KEYS as $key) {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $settings[$key] = $stmt->fetchColumn() ?: '';
        }

        // Get available providers
        $providers = [];
        $aiSettingsFile = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($aiSettingsFile)) {
            $aiConfig = json_decode(file_get_contents($aiSettingsFile), true);
            foreach ($aiConfig['providers'] ?? [] as $name => $cfg) {
                if (!empty($cfg['api_key']) && !empty($cfg['enabled'])) {
                    $providers[$name] = [
                        'name' => ucfirst($name),
                        'models' => $cfg['models'] ?? [],
                        'default' => $cfg['default_model'] ?? '',
                    ];
                }
            }
        }

        // Chat session stats
        $stats = [
            'total_sessions' => (int)$pdo->query("SELECT COUNT(*) FROM chat_sessions")->fetchColumn(),
            'today' => (int)$pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'this_week' => (int)$pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
        ];

        render('admin/chat-settings/index', [
            'settings' => $settings,
            'providers' => $providers,
            'stats' => $stats,
        ]);
    }

    public function save(Request $request): void
    {
        Session::requireRole('admin');
        $data = $GLOBALS['_JSON_DATA'] ?? $_POST;
        $pdo = db();

        foreach (self::SETTINGS_KEYS as $key) {
            $value = trim($data[$key] ?? '');
            $stmt = $pdo->prepare(
                "INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
            );
            $stmt->execute([$key, $value]);
        }

        // Clear chatbot context cache
        if (function_exists('cache_clear')) {
            cache_clear('chatbot_context');
        }

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        Session::flash('success', 'Chat settings saved.');
        \Core\Response::redirect('/admin/chat-settings');
    }

    public function sessions(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $total = (int)$pdo->query("SELECT COUNT(*) FROM chat_sessions")->fetchColumn();
        $stmt = $pdo->prepare(
            "SELECT session_id, ip_address, page_url, 
                    JSON_LENGTH(messages) as msg_count, created_at, updated_at
             FROM chat_sessions ORDER BY updated_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $sessions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/chat-settings/sessions', [
            'sessions' => $sessions,
            'total' => $total,
            'page' => $page,
            'totalPages' => max(1, (int)ceil($total / $perPage)),
        ]);
    }
}
