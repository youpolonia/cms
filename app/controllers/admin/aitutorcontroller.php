<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

/**
 * AI Tutor Controller — Interactive onboarding & learning assistant
 * Routes: GET /admin/ai-tutor, POST /api/ai-tutor/ask
 */
class AiTutorController
{
    /**
     * GET /admin/ai-tutor — Tutor page
     */
    public function index(): void
    {
        require_once \CMS_ROOT . '/core/ai-tutor-knowledge.php';

        // Load AI models for model selector
        $models = $this->getAvailableModels();
        $topics = getAiTutorTopics();

        // Load installed plugins for context
        $pluginsFile = \CMS_ROOT . '/config/installed_plugins.json';
        $plugins = file_exists($pluginsFile) ? (json_decode(file_get_contents($pluginsFile), true) ?: []) : [];
        $enabledPlugins = array_filter($plugins, fn($p) => !empty($p['enabled']));

        $data = [
            'title'      => 'AI Tutor',
            'models'     => $models,
            'topics'     => $topics,
            'plugins'    => $enabledPlugins,
            'csrfToken'  => \csrf_token(),
            'history'    => $_SESSION['ai_tutor_history'] ?? [],
        ];

        extract($data);
        ob_start();
        require \CMS_APP . '/views/admin/ai-tutor/index.php';
        $pageContent = ob_get_clean();
        echo $pageContent;
        exit;
    }

    /**
     * POST /api/ai-tutor/ask — Send question to AI with tutor knowledge
     */
    public function ask(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $message = trim($input['message'] ?? '');
        $model = trim($input['model'] ?? '');
        $context = trim($input['context'] ?? ''); // optional: current admin page context

        if (empty($message)) {
            echo json_encode(['error' => 'Question is required']);
            exit;
        }

        // Load AI settings
        $aiSettingsFile = \CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($aiSettingsFile)) {
            echo json_encode(['error' => 'AI not configured. Go to System → AI Settings to add API keys.']);
            exit;
        }

        $config = json_decode(file_get_contents($aiSettingsFile), true) ?: [];

        // Find provider
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
            echo json_encode(['error' => 'No AI provider configured. Go to AI & SEO → AI Settings.']);
            exit;
        }

        // Build system prompt with full knowledge base
        require_once \CMS_ROOT . '/core/ai-tutor-knowledge.php';
        $knowledge = getAiTutorKnowledge();

        $systemPrompt = $knowledge . "\n\n---\n\n";
        $systemPrompt .= "## Your Behavior\n";
        $systemPrompt .= "- You are a friendly, patient tutor. Think of yourself as a helpful colleague.\n";
        $systemPrompt .= "- Always give **exact navigation paths** (e.g., 'Go to **Design → Themes**')\n";
        $systemPrompt .= "- Use **step-by-step numbered instructions** for how-to questions\n";
        $systemPrompt .= "- Use **bold** for menu items, buttons, and important terms\n";
        $systemPrompt .= "- Keep answers concise but complete. 3-5 steps is ideal for tutorials.\n";
        $systemPrompt .= "- If you don't know something specific about this CMS, say so honestly.\n";
        $systemPrompt .= "- Suggest related topics at the end: 'Want to learn more about X?'\n";
        $systemPrompt .= "- Answer in the same language the user writes in.\n";

        if ($context) {
            $systemPrompt .= "\n**Current context:** The user is currently on the '{$context}' page in the admin panel.\n";
        }

        // Build messages with history (last 8 for context)
        $history = $_SESSION['ai_tutor_history'] ?? [];
        $contextMessages = array_slice($history, -8);

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($contextMessages as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        // Call AI
        try {
            $chatController = new AiChatController();
            // Use the same callAI method via reflection (it's private, so we replicate the call)
            $response = $this->callAI($provider, $model, $messages);
        } catch (\Throwable $e) {
            echo json_encode(['error' => 'AI error: ' . $e->getMessage()]);
            exit;
        }

        // Save to session
        $history[] = ['role' => 'user', 'content' => $message, 'time' => time()];
        $history[] = ['role' => 'assistant', 'content' => $response, 'time' => time(), 'model' => $model];
        $_SESSION['ai_tutor_history'] = array_slice($history, -30);

        echo json_encode(['response' => $response, 'model' => $model]);
        exit;
    }

    /**
     * POST /api/ai-tutor/clear — Clear tutor chat history
     */
    public function clear(): void
    {
        $_SESSION['ai_tutor_history'] = [];
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // ─── Private helpers ─────────────────────────────────

    private function getAvailableModels(): array
    {
        $aiSettingsFile = \CMS_ROOT . '/config/ai_settings.json';
        $models = [];
        if (!file_exists($aiSettingsFile)) return $models;

        $config = json_decode(file_get_contents($aiSettingsFile), true) ?: [];
        foreach ($config['providers'] ?? [] as $provId => $provider) {
            if (!is_array($provider) || empty($provider['api_key']) || !($provider['enabled'] ?? true)) continue;
            if (isset($provider['chat_capable']) && !$provider['chat_capable']) continue;
            $provName = $provider['name'] ?? (is_string($provId) ? ucfirst($provId) : 'Unknown');
            foreach ($provider['models'] ?? [] as $modelId => $model) {
                if (!is_array($model)) continue;
                $models[] = [
                    'id'       => is_string($modelId) ? $modelId : ($model['id'] ?? $modelId),
                    'name'     => $model['name'] ?? (is_string($modelId) ? $modelId : 'Model'),
                    'provider' => $provName,
                ];
            }
        }
        return $models;
    }

    private function callAI(array $provider, string $model, array $messages): string
    {
        $providerId = strtolower($provider['_id'] ?? $provider['id'] ?? $provider['name'] ?? '');
        $apiKey = $provider['api_key'];

        // DeepSeek model ID mapping
        if (str_contains($providerId, 'deepseek')) {
            $dsMap = ['deepseek-v3' => 'deepseek-chat', 'deepseek-r1' => 'deepseek-reasoner'];
            $model = $dsMap[$model] ?? $model;
        }

        $isOpenAiReasoning = false;
        if (str_contains($providerId, 'openai')) {
            $isOpenAiReasoning = (bool)preg_match('/^(o1|o3|o4)/', $model);
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
                if ($msg['role'] === 'system') { $system = $msg['content']; } else { $apiMessages[] = $msg; }
            }
            $body = json_encode(['model' => $model, 'system' => $system, 'messages' => $apiMessages, 'max_tokens' => 4096]);
        } elseif (str_contains($providerId, 'google')) {
            $contents = [];
            $systemInstruction = '';
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') { $systemInstruction = $msg['content']; } else {
                    $contents[] = ['role' => $msg['role'] === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $msg['content']]]];
                }
            }
            $body = json_encode([
                'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
                'contents' => $contents,
                'generationConfig' => ['maxOutputTokens' => 4096],
            ]);
        } else {
            $payload = ['model' => $model, 'messages' => $messages];
            if ($isOpenAiReasoning) {
                $payload['max_completion_tokens'] = 4096;
                $payload['messages'] = array_map(fn($m) => $m['role'] === 'system' ? ['role' => 'user', 'content' => $m['content']] : $m, $payload['messages']);
            } else {
                $payload['max_tokens'] = 4096;
                $payload['temperature'] = 0.7;
            }
            $body = json_encode($payload);
        }

        // cURL with retry
        $maxRetries = 3;
        $response = '';
        $httpCode = 0;
        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            if ($attempt > 0) usleep($attempt * 1500000);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 120, CURLOPT_CONNECTTIMEOUT => 10,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($error) throw new \RuntimeException("Network error: {$error}");
            if (in_array($httpCode, [429, 529]) && $attempt < $maxRetries) continue;
            break;
        }

        if ($httpCode >= 400) {
            $errData = json_decode($response, true);
            $errMsg = $errData['error']['message'] ?? $errData['error'] ?? "HTTP {$httpCode}";
            if (is_array($errMsg)) $errMsg = json_encode($errMsg);
            if ($httpCode === 529) throw new \RuntimeException("Model overloaded. Try another model.");
            if ($httpCode === 429) throw new \RuntimeException("Rate limit. Wait a moment.");
            if ($httpCode === 401) throw new \RuntimeException("Invalid API key. Check AI Settings.");
            throw new \RuntimeException($errMsg);
        }

        $data = json_decode($response, true);
        if (!$data) throw new \RuntimeException("Invalid AI response");

        if (str_contains($providerId, 'anthropic'))      return $data['content'][0]['text'] ?? 'No response';
        elseif (str_contains($providerId, 'google'))      return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
        else                                               return $data['choices'][0]['message']['content'] ?? 'No response';
    }
}
