<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class ChatController
{
    /**
     * Chat API endpoint
     * POST /api/chat
     */
    public function message(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Parse input
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $message = trim($data['message'] ?? '');
        $sessionId = trim($data['session_id'] ?? '');
        $pageUrl = trim($data['page_url'] ?? '');

        if ($message === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Message is required.']);
            return;
        }

        if (mb_strlen($message) > 1000) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Message too long.']);
            return;
        }

        // Generate session ID if not provided
        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
        }

        // Check if chatbot is enabled
        $pdo = db();
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'chatbot_enabled'");
        $stmt->execute();
        $enabled = $stmt->fetchColumn();

        if ($enabled !== '1') {
            http_response_code(503);
            echo json_encode(['ok' => false, 'error' => 'Chat is currently unavailable.']);
            return;
        }

        // Load chatbot engine
        require_once CMS_ROOT . '/core/chatbot.php';

        try {
            $result = \CmsChatbot::chat($sessionId, $message, $pageUrl);
        } catch (\Throwable $e) {
            error_log("[Chatbot] Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $result = ['ok' => false, 'error' => 'Internal error: ' . $e->getMessage()];
        }

        if ($result['ok']) {
            echo json_encode([
                'ok' => true,
                'reply' => $result['reply'],
                'session_id' => $sessionId,
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => $result['error'] ?? 'Something went wrong.',
                'session_id' => $sessionId,
            ]);
        }
    }

    /**
     * Get widget configuration (public)
     * GET /api/chat/config
     */
    public function config(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=300');

        require_once CMS_ROOT . '/core/chatbot.php';
        $config = \CmsChatbot::getWidgetConfig(db());

        echo json_encode($config);
    }
}
