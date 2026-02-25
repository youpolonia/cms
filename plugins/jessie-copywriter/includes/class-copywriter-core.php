<?php
namespace Plugins\JessieCopywriter;

/**
 * Copywriter Core — generation orchestrator
 * Coordinates AI generation with brand voice, platform formatting, and storage
 */
class CopywriterCore {
    private \PDO $pdo;

    /** Available rewrite modes */
    public const REWRITE_MODES = [
        'professional' => 'Rewrite in a polished, corporate professional tone. Use formal language, industry terms, and authoritative voice.',
        'casual'       => 'Rewrite in a relaxed, conversational casual tone. Use everyday language, contractions, and friendly voice.',
        'luxury'       => 'Rewrite in an elegant, premium luxury tone. Use sophisticated language, sensory words, and exclusivity-focused voice.',
        'friendly'     => 'Rewrite in a warm, approachable friendly tone. Use positive language, inclusive words, and encouraging voice.',
        'technical'    => 'Rewrite in a precise, detailed technical tone. Use specifications, measurements, and expert terminology.',
        'seo'          => 'Rewrite optimized for search engines. Use natural keyword placement, clear headers, and semantic variations.',
        'persuasive'   => 'Rewrite in a compelling, action-driven persuasive tone. Use power words, urgency, social proof, and strong CTAs.',
        'minimal'      => 'Rewrite in a clean, minimal tone. Use short sentences, essential words only, no fluff or adjectives.'
    ];

    /** Product categories */
    public const CATEGORIES = [
        'electronics'     => 'Electronics & Technology',
        'clothing'        => 'Clothing & Fashion',
        'home'            => 'Home & Garden',
        'beauty'          => 'Beauty & Personal Care',
        'sports'          => 'Sports & Outdoors',
        'toys'            => 'Toys & Games',
        'food'            => 'Food & Beverages',
        'automotive'      => 'Automotive',
        'books'           => 'Books & Media',
        'health'          => 'Health & Wellness',
        'pets'            => 'Pet Supplies',
        'jewelry'         => 'Jewelry & Accessories',
        'office'          => 'Office Supplies',
        'baby'            => 'Baby & Kids',
        'handmade'        => 'Handmade & Crafts',
        'other'           => 'Other'
    ];

    public function __construct() {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
    }

    /**
     * Generate product copy for a single product
     */
    public function generate(int $userId, array $params): array {
        $name     = trim($params['name'] ?? '');
        $features = trim($params['features'] ?? '');
        $category = trim($params['category'] ?? 'other');
        $platform = trim($params['platform'] ?? 'general');
        $tone     = trim($params['tone'] ?? 'professional');
        $brandId  = (int)($params['brand_id'] ?? 0);

        if ($name === '') {
            return ['success' => false, 'error' => 'Product name is required'];
        }

        // Build AI prompt
        $systemPrompt = $this->buildSystemPrompt($platform, $tone, $brandId, $userId);
        $userPrompt   = $this->buildUserPrompt($name, $features, $category, $platform);

        // Call AI
        require_once CMS_ROOT . '/core/ai_content.php';
        $aiResult = ai_content_generate([
            'topic' => $systemPrompt . "\n\n" . $userPrompt,
            'tone'  => $tone,
            'length_hint' => 'long'
        ]);

        if (!$aiResult['ok']) {
            // Save failed attempt
            $this->saveContent($userId, $name, $features, $category, $platform, $tone, $brandId, null, null, 'failed');
            return ['success' => false, 'error' => $aiResult['error'] ?? 'AI generation failed'];
        }

        $rawContent = $aiResult['content'] ?? '';

        // Parse response into structured sections
        $parsed = CopywriterPlatform::parseResponse($rawContent, $platform);

        // Save to database
        $contentId = $this->saveContent(
            $userId, $name, $features, $category, $platform, $tone, $brandId,
            $parsed, $rawContent, 'completed'
        );

        return [
            'success' => true,
            'content_id' => $contentId,
            'product_name' => $name,
            'platform' => $platform,
            'title' => $parsed['title'],
            'description' => $parsed['description'],
            'bullet_points' => $parsed['bullet_points'],
            'meta_title' => $parsed['meta_title'],
            'meta_description' => $parsed['meta_description'],
            'tags' => $parsed['tags'],
            'raw' => $rawContent
        ];
    }

    /**
     * Rewrite existing content in a different tone/mode
     */
    public function rewrite(int $userId, array $params): array {
        $contentId = (int)($params['content_id'] ?? 0);
        $text      = trim($params['text'] ?? '');
        $mode      = trim($params['mode'] ?? 'professional');

        if ($text === '' && $contentId > 0) {
            $existing = $this->getContent($contentId, $userId);
            if ($existing) {
                $text = $existing['description'] ?? $existing['raw_ai_response'] ?? '';
            }
        }

        if ($text === '') {
            return ['success' => false, 'error' => 'Text to rewrite is required'];
        }

        if (!isset(self::REWRITE_MODES[$mode])) {
            return ['success' => false, 'error' => 'Invalid rewrite mode. Available: ' . implode(', ', array_keys(self::REWRITE_MODES))];
        }

        $instruction = self::REWRITE_MODES[$mode];

        require_once CMS_ROOT . '/core/ai_content.php';
        $aiResult = ai_content_generate([
            'topic' => "You are a professional copywriter. {$instruction}\n\nRewrite the following product copy while keeping all factual information intact:\n\n{$text}",
            'tone'  => $mode,
            'length_hint' => 'long'
        ]);

        if (!$aiResult['ok']) {
            return ['success' => false, 'error' => $aiResult['error'] ?? 'Rewrite failed'];
        }

        return [
            'success' => true,
            'mode' => $mode,
            'original' => $text,
            'rewritten' => $aiResult['content'] ?? ''
        ];
    }

    /**
     * Get single content by ID
     */
    public function getContent(int $id, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM copywriter_content WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get generation history
     */
    public function getHistory(int $userId, int $limit = 50, int $offset = 0, ?string $platform = null): array {
        $sql = "SELECT id, product_name, platform, tone, title, meta_title, status, credits_used, created_at
                FROM copywriter_content WHERE user_id = ?";
        $params = [$userId];

        if ($platform) {
            $sql .= " AND platform = ?";
            $params[] = $platform;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get stats for dashboard
     */
    public function getStats(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) as total_generations,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as last_7_days,
                SUM(credits_used) as total_credits
             FROM copywriter_content WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['total_generations' => 0, 'completed' => 0, 'last_7_days' => 0, 'total_credits' => 0];
    }

    // ── Private helpers ──

    private function buildSystemPrompt(string $platform, string $tone, int $brandId, int $userId): string {
        $prompt = "You are an expert e-commerce product copywriter. Write compelling, conversion-optimized product copy.\n\n";

        // Platform instructions
        $prompt .= CopywriterPlatform::buildPromptInstructions($platform);

        // Brand voice
        if ($brandId > 0) {
            $brandService = new CopywriterBrand();
            $brandInstructions = $brandService->buildPromptInstructions($brandId, $userId);
            if ($brandInstructions) {
                $prompt .= "\n" . $brandInstructions . "\n";
            }
        }

        // Tone
        if (isset(self::REWRITE_MODES[$tone])) {
            $prompt .= "\nTONE: " . self::REWRITE_MODES[$tone] . "\n";
        }

        $prompt .= "\nRESPONSE FORMAT: Use clear section headers (## Title, ## Description, ## Bullet Points, ## Meta Title, ## Meta Description, ## Tags). ";
        $prompt .= "Strictly follow character limits. Write ready-to-publish copy.\n";

        return $prompt;
    }

    private function buildUserPrompt(string $name, string $features, string $category, string $platform): string {
        $prompt = "PRODUCT: {$name}\n";
        if ($category && $category !== 'other') {
            $categoryLabel = self::CATEGORIES[$category] ?? $category;
            $prompt .= "CATEGORY: {$categoryLabel}\n";
        }
        if ($features) {
            $prompt .= "FEATURES & DETAILS:\n{$features}\n";
        }
        $prompt .= "\nGenerate complete product copy for {$platform} following all format requirements above.";
        return $prompt;
    }

    private function saveContent(
        int $userId, string $name, string $features, string $category,
        string $platform, string $tone, int $brandId,
        ?array $parsed, ?string $rawContent, string $status,
        ?int $batchId = null
    ): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO copywriter_content
             (user_id, batch_id, brand_id, product_name, product_features, product_category, platform, tone,
              title, description, bullet_points, meta_title, meta_description, tags, raw_ai_response, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId,
            $batchId ?: null,
            $brandId ?: null,
            $name,
            $features,
            $category,
            $platform,
            $tone,
            $parsed['title'] ?? null,
            $parsed['description'] ?? null,
            $parsed['bullet_points'] ?? null,
            $parsed['meta_title'] ?? null,
            $parsed['meta_description'] ?? null,
            $parsed['tags'] ?? null,
            $rawContent,
            $status
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
