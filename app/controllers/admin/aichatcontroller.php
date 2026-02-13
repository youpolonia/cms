<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class AiChatController
{
    /**
     * GET /admin/ai-chat — AI Chat Assistant page
     */
    public function index(Request $request): void
    {
        $pdo = db();
        
        // Load AI settings for model selector
        $aiSettingsFile = \CMS_ROOT . '/config/ai_settings.json';
        $models = [];
        if (file_exists($aiSettingsFile)) {
            $config = json_decode(file_get_contents($aiSettingsFile), true) ?: [];
            foreach ($config['providers'] ?? [] as $provider) {
                if (empty($provider['api_key'])) continue;
                foreach ($provider['models'] ?? [] as $model) {
                    if (!($model['enabled'] ?? true)) continue;
                    $models[] = [
                        'id' => $model['id'],
                        'name' => $model['name'] ?? $model['id'],
                        'provider' => $provider['name'] ?? $provider['id'],
                    ];
                }
            }
        }

        // Load chat history from session
        $history = $_SESSION['ai_chat_history'] ?? [];

        $data = [
            'title' => 'AI Assistant',
            'models' => $models,
            'history' => $history,
            'csrfToken' => csrf_token(),
        ];

        extract($data);
        ob_start();
        require \CMS_APP . '/views/admin/ai-chat/index.php';
        $pageContent = ob_get_clean();
        echo $pageContent;
        exit;
    }

    /**
     * POST /api/ai-chat/send — Send message to AI
     */
    public function send(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $message = trim($input['message'] ?? '');
        $model = trim($input['model'] ?? '');

        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            exit;
        }

        // Load AI settings
        $aiSettingsFile = \CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($aiSettingsFile)) {
            echo json_encode(['error' => 'AI not configured. Go to Settings to add API keys.']);
            exit;
        }

        $config = json_decode(file_get_contents($aiSettingsFile), true) ?: [];

        // Find provider + model
        $provider = null;
        $modelConfig = null;
        foreach ($config['providers'] ?? [] as $p) {
            if (empty($p['api_key'])) continue;
            foreach ($p['models'] ?? [] as $m) {
                if ($m['id'] === $model) {
                    $provider = $p;
                    $modelConfig = $m;
                    break 2;
                }
            }
        }

        // Fallback to first available
        if (!$provider) {
            foreach ($config['providers'] ?? [] as $p) {
                if (!empty($p['api_key']) && !empty($p['models'])) {
                    $provider = $p;
                    $modelConfig = $p['models'][0];
                    $model = $modelConfig['id'];
                    break;
                }
            }
        }

        if (!$provider) {
            echo json_encode(['error' => 'No AI provider configured with a valid API key.']);
            exit;
        }

        // Build conversation with system prompt
        $systemPrompt = $this->buildSystemPrompt();

        // Get chat history from session (last 10 messages for context)
        $history = $_SESSION['ai_chat_history'] ?? [];
        $contextMessages = array_slice($history, -10);

        // Build messages array
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        foreach ($contextMessages as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        // Call AI
        try {
            $response = $this->callAI($provider, $model, $messages);
        } catch (\Throwable $e) {
            echo json_encode(['error' => 'AI error: ' . $e->getMessage()]);
            exit;
        }

        // Save to session history
        $history[] = ['role' => 'user', 'content' => $message, 'time' => time()];
        $history[] = ['role' => 'assistant', 'content' => $response, 'time' => time(), 'model' => $model];
        // Keep last 50 messages
        $_SESSION['ai_chat_history'] = array_slice($history, -50);

        echo json_encode([
            'response' => $response,
            'model' => $model,
        ]);
        exit;
    }

    /**
     * POST /api/ai-chat/clear — Clear chat history
     */
    public function clear(): void
    {
        $_SESSION['ai_chat_history'] = [];
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Build system prompt with CMS context
     */
    private function buildSystemPrompt(): string
    {
        $pdo = db();

        // Gather CMS stats
        $pageCount = (int)$pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
        $articleCount = (int)$pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
        $mediaCount = 0;
        try { $mediaCount = (int)$pdo->query("SELECT COUNT(*) FROM media")->fetchColumn(); } catch (\Throwable $e) {}
        $theme = function_exists('get_active_theme') ? get_active_theme() : 'unknown';
        $siteName = $pdo->query("SELECT `value` FROM settings WHERE `key`='site_name'")->fetchColumn() ?: 'My Site';

        return <<<PROMPT
You are an AI assistant for Jessie AI-CMS, a modern content management system. You help the admin user with:

- Creating and managing content (pages, articles, media)
- Customizing themes and design (Theme Studio, AI Theme Builder)
- SEO optimization (meta titles, descriptions, keywords, best practices)
- Technical questions about the CMS
- Writing and editing content
- General website strategy and best practices

Current site context:
- Site name: {$siteName}
- Active theme: {$theme}
- Pages: {$pageCount}, Articles: {$articleCount}, Media: {$mediaCount}

CMS features available:
- 5 starter themes + AI Theme Builder (generates unique themes)
- Theme Studio (visual customizer with 15+ tools)
- JTB Page Builder (79 drag-and-drop modules)
- 12+ AI content tools (writer, rewriter, translator, image generator)
- 15 SEO tools (assistant, bulk editor, keywords, competitor analysis)
- REST API for headless use
- Multi-language support (i18n)
- White-label customization

Admin navigation:
- Content: Pages (/admin/pages), Articles (/admin/articles), Media (/admin/media), Menus (/admin/menus)
- Appearance: Themes (/admin/themes), Theme Studio (/admin/theme-studio), AI Theme Builder (/admin/ai-theme-builder)
- SEO: SEO Assistant (/admin/ai-seo-assistant), Keywords (/admin/ai-seo-keywords)
- AI Tools: Content Creator (/admin/ai-content-creator), Copywriter (/admin/ai-copywriter)
- System: Settings (/admin/settings), Users (/admin/users), Backup (/admin/backup)

Be helpful, concise, and practical. Give specific step-by-step instructions when asked how to do something. Use markdown formatting for readability.
PROMPT;
    }

    /**
     * Call AI provider API
     */
    private function callAI(array $provider, string $model, array $messages): string
    {
        $providerId = strtolower($provider['id'] ?? $provider['name'] ?? '');
        $apiKey = $provider['api_key'];

        // Map provider to endpoint
        if (str_contains($providerId, 'openai')) {
            $url = 'https://api.openai.com/v1/chat/completions';
            $headers = ["Authorization: Bearer {$apiKey}", "Content-Type: application/json"];
        } elseif (str_contains($providerId, 'anthropic')) {
            $url = 'https://api.anthropic.com/v1/messages';
            $headers = ["x-api-key: {$apiKey}", "anthropic-version: 2023-06-01", "Content-Type: application/json"];
        } elseif (str_contains($providerId, 'deepseek')) {
            $url = 'https://api.deepseek.com/v1/chat/completions';
            $headers = ["Authorization: Bearer {$apiKey}", "Content-Type: application/json"];
        } elseif (str_contains($providerId, 'google')) {
            // Gemini
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $headers = ["Content-Type: application/json"];
        } else {
            throw new \RuntimeException("Unsupported provider: {$providerId}");
        }

        // Build request body
        if (str_contains($providerId, 'anthropic')) {
            $system = '';
            $apiMessages = [];
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $system = $msg['content'];
                } else {
                    $apiMessages[] = $msg;
                }
            }
            $body = json_encode([
                'model' => $model,
                'system' => $system,
                'messages' => $apiMessages,
                'max_tokens' => 4096,
            ]);
        } elseif (str_contains($providerId, 'google')) {
            $contents = [];
            $systemInstruction = '';
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $systemInstruction = $msg['content'];
                } else {
                    $contents[] = [
                        'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                        'parts' => [['text' => $msg['content']]],
                    ];
                }
            }
            $body = json_encode([
                'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
                'contents' => $contents,
                'generationConfig' => ['maxOutputTokens' => 4096],
            ]);
        } else {
            // OpenAI / DeepSeek compatible
            $body = json_encode([
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 4096,
                'temperature' => 0.7,
            ]);
        }

        // cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("Network error: {$error}");
        }

        if ($httpCode >= 400) {
            $errData = json_decode($response, true);
            $errMsg = $errData['error']['message'] ?? $errData['error'] ?? "HTTP {$httpCode}";
            if (is_array($errMsg)) $errMsg = json_encode($errMsg);
            throw new \RuntimeException($errMsg);
        }

        $data = json_decode($response, true);
        if (!$data) {
            throw new \RuntimeException("Invalid response from AI provider");
        }

        // Extract text based on provider
        if (str_contains($providerId, 'anthropic')) {
            return $data['content'][0]['text'] ?? 'No response';
        } elseif (str_contains($providerId, 'google')) {
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
        } else {
            return $data['choices'][0]['message']['content'] ?? 'No response';
        }
    }
}
