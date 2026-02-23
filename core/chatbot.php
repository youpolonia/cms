<?php
/**
 * Jessie CMS — AI Chatbot Engine
 * RAG-based chatbot using site content as knowledge base
 */

class CmsChatbot
{
    private const CACHE_KEY = 'chatbot_context';
    private const CACHE_TTL = 3600; // 1 hour
    private const MAX_CONTEXT_CHARS = 8000;
    private const MAX_HISTORY = 10;
    private const RATE_LIMIT = 20; // messages per hour per IP

    /**
     * Handle a chat message and return AI response
     */
    public static function chat(string $sessionId, string $message, string $pageUrl = ''): array
    {
        $pdo = db();

        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM chat_sessions 
             WHERE ip_address = ? AND updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $stmt->execute([$ip]);
        // Simple rate limit on sessions, not messages — good enough
        if ((int)$stmt->fetchColumn() > 50) {
            return ['ok' => false, 'error' => 'Too many requests. Please try again later.'];
        }

        // Order status lookup — intercept before AI call
        if (preg_match('/JC-[\d\-]+/', $message, $matches)) {
            $orderReply = self::lookupOrderStatus($pdo, $matches[0]);
            if ($orderReply !== null) {
                $history = self::loadSession($pdo, $sessionId);
                $history[] = ['role' => 'user', 'content' => $message];
                $history[] = ['role' => 'assistant', 'content' => $orderReply];
                self::saveSession($pdo, $sessionId, $ip, $history, $pageUrl);
                return ['ok' => true, 'reply' => $orderReply];
            }
        }

        // Load or create session
        $history = self::loadSession($pdo, $sessionId);

        // Add user message to history
        $history[] = ['role' => 'user', 'content' => $message];

        // Keep only last N messages
        if (count($history) > self::MAX_HISTORY * 2) {
            $history = array_slice($history, -self::MAX_HISTORY * 2);
        }

        // Build context from site content
        $context = self::buildSiteContext($pdo);
        $settings = self::getSettings($pdo);

        // Build system prompt
        $siteName = self::getSetting($pdo, 'site_name') ?: 'this website';
        $customInstructions = $settings['custom_instructions'] ?? '';

        $systemPrompt = "You are a helpful assistant for {$siteName}. "
            . "Answer visitor questions using ONLY the website content provided below. "
            . "Be concise, friendly, and helpful. If you don't know the answer from the provided content, "
            . "say so and suggest contacting the team directly.\n\n"
            . "You can help customers find products, check prices, and recommend items. "
            . "When mentioning products, include the link /shop/{slug}. "
            . "If asked about order status, ask for the order number (format: JC-YYYYMMDD-XXXX).\n\n"
            . "WEBSITE CONTENT:\n{$context}\n\n";

        if ($customInstructions) {
            $systemPrompt .= "ADDITIONAL INSTRUCTIONS:\n{$customInstructions}\n\n";
        }

        $systemPrompt .= "RULES:\n"
            . "- Only answer based on the website content above\n"
            . "- Keep responses concise (2-4 sentences usually)\n"
            . "- If asked about pricing, features, or services, refer to specific pages\n"
            . "- When recommending products, include links like /shop/{slug}\n"
            . "- Be conversational and warm\n"
            . "- Format with markdown when helpful\n";

        // Build conversation for AI
        $conversationMessages = '';
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
            $conversationMessages .= "{$role}: {$msg['content']}\n\n";
        }
        $conversationMessages .= "Assistant:";

        // Get AI provider/model from settings
        $provider = $settings['provider'] ?? '';
        $model = $settings['model'] ?? '';

        // Auto-detect if not configured
        if (!$provider || !$model) {
            [$provider, $model] = self::detectProvider();
        }

        if (!$provider) {
            return ['ok' => false, 'error' => 'No AI provider configured. Please set up AI in admin settings.'];
        }

        // Call AI
        require_once CMS_ROOT . '/core/ai_content.php';
        $result = ai_universal_generate(
            $provider,
            $model,
            $systemPrompt,
            $conversationMessages,
            ['max_tokens' => 500, 'temperature' => 0.7]
        );

        if (!$result['ok']) {
            return ['ok' => false, 'error' => 'I\'m having trouble connecting right now. Please try again in a moment.'];
        }

        $reply = trim($result['content'] ?? '');
        // Clean up — remove "Assistant:" prefix if AI included it
        $reply = preg_replace('/^Assistant:\s*/i', '', $reply);

        // Add assistant reply to history
        $history[] = ['role' => 'assistant', 'content' => $reply];

        // Save session
        self::saveSession($pdo, $sessionId, $ip, $history, $pageUrl);

        // Fire event (only for new sessions or every Nth message to avoid spam)
        if (count($history) <= 2) {
            if (function_exists('cms_event')) {
                cms_event('chatbot.session.started', ['session_id' => $sessionId, 'page' => $pageUrl, 'first_message' => $message]);
            }
        }

        return ['ok' => true, 'reply' => $reply];
    }

    /**
     * Build RAG context from site content
     */
    private static function buildSiteContext(\PDO $pdo): string
    {
        // Try cache first
        if (function_exists('cache_get')) {
            $cached = cache_get(self::CACHE_KEY);
            if ($cached) return $cached;
        }

        $activeTheme = function_exists('get_active_theme') ? get_active_theme() : '';
        $context = '';

        // Site info
        $siteInfo = [];
        foreach (['site_name', 'site_description', 'site_url', 'admin_email'] as $key) {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            if ($val) $siteInfo[$key] = $val;
        }
        if ($siteInfo) {
            $context .= "SITE INFO:\n";
            foreach ($siteInfo as $k => $v) {
                $context .= "- " . ucwords(str_replace('_', ' ', $k)) . ": {$v}\n";
            }
            $context .= "\n";
        }

        // Pages
        $stmt = $pdo->prepare(
            "SELECT title, slug, content, meta_description FROM pages 
             WHERE status = 'published' AND (theme_slug = ? OR theme_slug IS NULL)
             ORDER BY id LIMIT 30"
        );
        $stmt->execute([$activeTheme]);
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($pages) {
            $context .= "PAGES:\n";
            foreach ($pages as $p) {
                $text = $p['meta_description'] ?: strip_tags($p['content'] ?? '');
                $text = preg_replace('/\s+/', ' ', trim($text));
                $text = mb_substr($text, 0, 300);
                $context .= "- [{$p['title']}](/page/{$p['slug']}): {$text}\n";
            }
            $context .= "\n";
        }

        // Articles
        $stmt = $pdo->prepare(
            "SELECT title, slug, excerpt, content FROM articles 
             WHERE status = 'published' AND (theme_slug = ? OR theme_slug IS NULL)
             ORDER BY created_at DESC LIMIT 20"
        );
        $stmt->execute([$activeTheme]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($articles) {
            $context .= "ARTICLES/BLOG:\n";
            foreach ($articles as $a) {
                $text = $a['excerpt'] ?: strip_tags($a['content'] ?? '');
                $text = preg_replace('/\s+/', ' ', trim($text));
                $text = mb_substr($text, 0, 200);
                $context .= "- [{$a['title']}](/article/{$a['slug']}): {$text}\n";
            }
            $context .= "\n";
        }

        // Products
        try {
            $stmt = $pdo->prepare(
                "SELECT p.name, p.price, p.sale_price, p.short_description, p.stock, p.slug,
                        c.name as category_name
                 FROM products p
                 LEFT JOIN product_categories c ON p.category_id = c.id
                 WHERE p.status = 'active'
                 ORDER BY p.created_at DESC
                 LIMIT 50"
            );
            $stmt->execute();
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($products) {
                // Categories summary
                $catStmt = $pdo->query(
                    "SELECT c.name, COUNT(p.id) as cnt
                     FROM product_categories c
                     JOIN products p ON p.category_id = c.id AND p.status = 'active'
                     GROUP BY c.id, c.name
                     ORDER BY cnt DESC"
                );
                $cats = $catStmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($cats) {
                    $context .= "PRODUCT CATEGORIES:\n";
                    foreach ($cats as $cat) {
                        $context .= "- {$cat['name']} ({$cat['cnt']} products)\n";
                    }
                    $context .= "\n";
                }

                $context .= "AVAILABLE PRODUCTS:\n";
                foreach ($products as $p) {
                    $price = ($p['sale_price'] !== null && (float)$p['sale_price'] > 0)
                        ? number_format((float)$p['sale_price'], 2) . ' (was ' . number_format((float)$p['price'], 2) . ')'
                        : number_format((float)$p['price'], 2);
                    $stock = ((int)$p['stock'] === -1 || (int)$p['stock'] > 0) ? 'In Stock' : 'Out of Stock';
                    $cat = $p['category_name'] ?: 'Uncategorized';
                    $desc = $p['short_description'] ? '  ' . mb_substr(trim($p['short_description']), 0, 120) : '';
                    $context .= "- {$p['name']} ({$cat}) — {$price} [{$stock}] — /shop/{$p['slug']}\n{$desc}\n";
                }
                $context .= "\n";
            }
        } catch (\Throwable $e) {
            // Products table may not exist yet — silently skip
        }

        // Contact info from theme customizations
        $stmt = $pdo->prepare(
            "SELECT CONCAT(section, '.', field_key) as k, field_value as v 
             FROM theme_customizations 
             WHERE theme_slug = ? AND section = 'footer' 
             LIMIT 20"
        );
        $stmt->execute([$activeTheme]);
        $footerData = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $footerData[$row['k']] = $row['v'];
        }
        if ($footerData) {
            $context .= "CONTACT/FOOTER INFO:\n";
            foreach ($footerData as $k => $v) {
                if ($v && !str_contains($v, 'http') && strlen($v) < 200) {
                    $context .= "- {$k}: {$v}\n";
                }
            }
            $context .= "\n";
        }

        // Trim to max
        if (mb_strlen($context) > self::MAX_CONTEXT_CHARS) {
            $context = mb_substr($context, 0, self::MAX_CONTEXT_CHARS) . "\n...(truncated)";
        }

        // Cache
        if (function_exists('cache_set')) {
            cache_set(self::CACHE_KEY, $context, self::CACHE_TTL);
        }

        return $context;
    }

    /**
     * Look up order status by order number
     */
    private static function lookupOrderStatus(\PDO $pdo, string $orderNumber): ?string
    {
        try {
            $stmt = $pdo->prepare("SELECT order_number, status, total, currency, items, created_at FROM orders WHERE order_number = ?");
            $stmt->execute([$orderNumber]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                return "I couldn't find order **{$orderNumber}**. Please double-check the number and try again.";
            }

            $statusLabels = [
                'pending' => '⏳ Pending',
                'processing' => '🔄 Processing',
                'shipped' => '📦 Shipped',
                'delivered' => '✅ Delivered',
                'cancelled' => '❌ Cancelled',
                'refunded' => '💰 Refunded',
            ];
            $statusText = $statusLabels[$order['status']] ?? ucfirst($order['status']);

            $items = json_decode($order['items'] ?? '[]', true);
            $itemSummary = '';
            if (is_array($items) && !empty($items)) {
                $names = array_map(fn($i) => ($i['name'] ?? 'Item') . ' ×' . ($i['quantity'] ?? 1), $items);
                $itemSummary = implode(', ', $names);
            }

            $date = date('M j, Y', strtotime($order['created_at']));
            $total = number_format((float)$order['total'], 2);

            $reply = "**Order {$order['order_number']}**\n"
                . "Status: {$statusText}\n"
                . "Date: {$date}\n"
                . "Total: {$total} {$order['currency']}\n";
            if ($itemSummary) {
                $reply .= "Items: {$itemSummary}\n";
            }

            return $reply;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Auto-detect first available AI provider
     */
    private static function detectProvider(): array
    {
        $settingsFile = CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($settingsFile)) return ['', ''];

        $settings = json_decode(file_get_contents($settingsFile), true);
        $providers = $settings['providers'] ?? [];

        // Preferred order
        $preferred = ['deepseek', 'anthropic', 'openai', 'google'];
        foreach ($preferred as $p) {
            if (!empty($providers[$p]['api_key']) && !empty($providers[$p]['enabled'])) {
                $models = $providers[$p]['models'] ?? [];
                $model = $providers[$p]['default_model'] ?? ($models[0] ?? '');
                if ($model) return [$p, $model];
            }
        }

        return ['', ''];
    }

    private static function loadSession(\PDO $pdo, string $sessionId): array
    {
        $stmt = $pdo->prepare("SELECT messages FROM chat_sessions WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $json = $stmt->fetchColumn();
        if ($json) {
            $msgs = json_decode($json, true);
            return is_array($msgs) ? $msgs : [];
        }
        return [];
    }

    private static function saveSession(\PDO $pdo, string $sessionId, string $ip, array $messages, string $pageUrl): void
    {
        $json = json_encode($messages, JSON_UNESCAPED_UNICODE);
        $stmt = $pdo->prepare(
            "INSERT INTO chat_sessions (session_id, ip_address, messages, page_url)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE messages = VALUES(messages), page_url = VALUES(page_url), updated_at = NOW()"
        );
        $stmt->execute([$sessionId, $ip, $json, $pageUrl ?: null]);
    }

    private static function getSettings(\PDO $pdo): array
    {
        $keys = ['chatbot_enabled', 'chatbot_provider', 'chatbot_model', 
                 'chatbot_welcome', 'chatbot_suggestions', 'chatbot_custom_instructions'];
        $settings = [];
        foreach ($keys as $key) {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            $shortKey = str_replace('chatbot_', '', $key);
            $settings[$shortKey] = $val ?: '';
        }
        return $settings;
    }

    private static function getSetting(\PDO $pdo, string $key): string
    {
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: '';
    }

    /**
     * Get chatbot config for frontend widget (public, no sensitive data)
     */
    public static function getWidgetConfig(\PDO $pdo): array
    {
        $settings = self::getSettings($pdo);
        $enabled = ($settings['enabled'] ?? '') === '1';

        return [
            'enabled' => $enabled,
            'welcome' => $settings['welcome'] ?: 'Hi! 👋 How can I help you today?',
            'suggestions' => array_filter(array_map('trim', explode("\n", $settings['suggestions'] ?? ''))),
        ];
    }
}
