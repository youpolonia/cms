<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

class AiChatController
{
    /**
     * GET /admin/ai-chat — AI Chat Assistant page
     */
    public function index(): void
    {
        $pdo = \core\Database::connection();
        
        // Load AI settings for model selector
        $aiSettingsFile = \CMS_ROOT . '/config/ai_settings.json';
        $models = [];
        if (file_exists($aiSettingsFile)) {
            $config = json_decode(file_get_contents($aiSettingsFile), true) ?: [];
            foreach ($config['providers'] ?? [] as $provId => $provider) {
                if (!is_array($provider)) continue;
                if (empty($provider['api_key']) || !($provider['enabled'] ?? true)) continue;
                // Skip non-chat providers (e.g. HuggingFace image/vision models)
                if (isset($provider['chat_capable']) && !$provider['chat_capable']) continue;
                $provName = $provider['name'] ?? (is_string($provId) ? ucfirst($provId) : 'Unknown');
                foreach ($provider['models'] ?? [] as $modelId => $model) {
                    if (!is_array($model)) continue;
                    $mId = is_string($modelId) ? $modelId : ($model['id'] ?? $modelId);
                    $mName = $model['name'] ?? $mId;
                    $models[] = [
                        'id' => $mId,
                        'name' => $mName,
                        'provider' => $provName,
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
            'csrfToken' => \csrf_token(),
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

        // Find provider + model (supports assoc arrays: providers.{id} and models.{id})
        $provider = null;
        $modelConfig = null;
        foreach ($config['providers'] ?? [] as $pId => $p) {
            if (!is_array($p) || empty($p['api_key']) || !($p['enabled'] ?? true)) continue;
            $p['_id'] = is_string($pId) ? $pId : ($p['id'] ?? $pId);
            foreach ($p['models'] ?? [] as $mId => $m) {
                if (!is_array($m)) continue;
                $resolvedId = is_string($mId) ? $mId : ($m['id'] ?? $mId);
                if ($resolvedId === $model) {
                    $provider = $p;
                    $modelConfig = $m;
                    break 2;
                }
            }
        }

        // Fallback to first available
        if (!$provider) {
            foreach ($config['providers'] ?? [] as $pId => $p) {
                if (!is_array($p) || empty($p['api_key']) || !($p['enabled'] ?? true)) continue;
                $p['_id'] = is_string($pId) ? $pId : ($p['id'] ?? $pId);
                foreach ($p['models'] ?? [] as $mId => $m) {
                    if (!is_array($m)) continue;
                    $provider = $p;
                    $modelConfig = $m;
                    $model = is_string($mId) ? $mId : ($m['id'] ?? $mId);
                    break 2;
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
        $pdo = \core\Database::connection();

        // Gather CMS stats
        $pageCount = (int)$pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
        $articleCount = (int)$pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
        $mediaCount = 0;
        try { $mediaCount = (int)$pdo->query("SELECT COUNT(*) FROM media")->fetchColumn(); } catch (\Throwable $e) {}
        $theme = function_exists('get_active_theme') ? \get_active_theme() : 'unknown';
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
        $providerId = strtolower($provider['_id'] ?? $provider['id'] ?? $provider['name'] ?? '');
        $apiKey = $provider['api_key'];

        // DeepSeek model ID mapping (config names → API names)
        if (str_contains($providerId, 'deepseek')) {
            $dsMap = ['deepseek-v3' => 'deepseek-chat', 'deepseek-r1' => 'deepseek-reasoner'];
            $model = $dsMap[$model] ?? $model;
        }

        // OpenAI reasoning models: no temperature, system→user, max_completion_tokens
        $isOpenAiReasoning = false;
        if (str_contains($providerId, 'openai')) {
            $isOpenAiReasoning = preg_match('/^(o1|o3|o4)/', $model);
        }

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
            $payload = [
                'model' => $model,
                'messages' => $messages,
            ];
            if ($isOpenAiReasoning) {
                // Reasoning models: no temperature, no system role, use max_completion_tokens
                $payload['max_completion_tokens'] = 4096;
                // Convert system messages to user messages
                $payload['messages'] = array_map(function($m) {
                    return $m['role'] === 'system' ? ['role' => 'user', 'content' => $m['content']] : $m;
                }, $payload['messages']);
            } else {
                $payload['max_tokens'] = 4096;
                $payload['temperature'] = 0.7;
            }
            $body = json_encode($payload);
        }

        // cURL with retry on 429/529 (rate limit / overloaded)
        $maxRetries = 3;
        $response = '';
        $httpCode = 0;
        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            if ($attempt > 0) {
                usleep($attempt * 1500000); // 1.5s, 3s, 4.5s backoff
            }
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
            // Retry on overloaded (529) or rate limit (429)
            if (in_array($httpCode, [429, 529]) && $attempt < $maxRetries) {
                continue;
            }
            break;
        }

        if ($httpCode >= 400) {
            $errData = json_decode($response, true);
            $errMsg = $errData['error']['message'] ?? $errData['error'] ?? "HTTP {$httpCode}";
            if (is_array($errMsg)) $errMsg = json_encode($errMsg);
            // User-friendly messages for common errors
            if ($httpCode === 529) {
                throw new \RuntimeException("Model is currently overloaded (tried {$maxRetries}x). Try a different model or wait a moment.");
            }
            if ($httpCode === 429) {
                throw new \RuntimeException("Rate limit exceeded. Wait a moment and try again.");
            }
            if ($httpCode === 401) {
                throw new \RuntimeException("Invalid API key for " . ucfirst($providerId) . ". Check Settings → AI.");
            }
            if ($httpCode === 402 || str_contains(strtolower($errMsg), 'insufficient') || str_contains(strtolower($errMsg), 'billing')) {
                throw new \RuntimeException("No credits/funds on your " . ucfirst($providerId) . " account. Add billing at provider dashboard.");
            }
            if ($httpCode === 404) {
                throw new \RuntimeException("Model '{$model}' not found. It may not be available on your plan.");
            }
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
