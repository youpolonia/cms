<?php
/**
 * AI Copywriter Assistant
 *
 * Provides AI-powered copywriting assistance for content creation.
 * Supports multiple copy types, tones, and A/B variant generation.
 *
 * @package CMS\Core
 * @since 1.0.0
 */

namespace Core;

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../config.php';

use \core\Database;

class AICopywriter
{
    /** @var \PDO Database connection */
    private \PDO $db;

    /** @var array AI provider configuration */
    private array $config;

    /** @var string Current AI provider */
    private string $provider;

    /** @var int|null Current user ID */
    private ?int $userId;

    /** @var int|null Current tenant ID */
    private ?int $tenantId;

    /** @var string|null Override model for API calls */
    private ?string $overrideModel = null;

    /** @var array Supported copy types */
    private const COPY_TYPES = [
        'headline' => [
            'label' => 'Headline',
            'max_length' => 100,
            'description' => 'Attention-grabbing headlines for articles, ads, or landing pages'
        ],
        'subheadline' => [
            'label' => 'Subheadline',
            'max_length' => 150,
            'description' => 'Supporting text that expands on the headline'
        ],
        'meta_title' => [
            'label' => 'Meta Title',
            'max_length' => 60,
            'description' => 'SEO-optimized page titles'
        ],
        'meta_description' => [
            'label' => 'Meta Description',
            'max_length' => 160,
            'description' => 'SEO-optimized page descriptions'
        ],
        'cta' => [
            'label' => 'Call to Action',
            'max_length' => 50,
            'description' => 'Compelling action phrases for buttons and links'
        ],
        'product_description' => [
            'label' => 'Product Description',
            'max_length' => 500,
            'description' => 'Persuasive product or service descriptions'
        ],
        'social_post' => [
            'label' => 'Social Media Post',
            'max_length' => 280,
            'description' => 'Engaging social media content'
        ],
        'email_subject' => [
            'label' => 'Email Subject Line',
            'max_length' => 60,
            'description' => 'Email subject lines that drive opens'
        ],
        'email_body' => [
            'label' => 'Email Body',
            'max_length' => 2000,
            'description' => 'Complete email copy'
        ],
        'tagline' => [
            'label' => 'Tagline/Slogan',
            'max_length' => 80,
            'description' => 'Memorable brand taglines and slogans'
        ],
        'bullet_points' => [
            'label' => 'Bullet Points',
            'max_length' => 500,
            'description' => 'Feature lists and key benefits'
        ],
        'blog_intro' => [
            'label' => 'Blog Introduction',
            'max_length' => 300,
            'description' => 'Engaging blog post introductions'
        ],
        'blog_outline' => [
            'label' => 'Blog Outline',
            'max_length' => 1000,
            'description' => 'Structured blog post outlines'
        ],
        'ad_copy' => [
            'label' => 'Ad Copy',
            'max_length' => 300,
            'description' => 'Advertising copy for various platforms'
        ],
        'testimonial_request' => [
            'label' => 'Testimonial Request',
            'max_length' => 500,
            'description' => 'Templates for requesting customer testimonials'
        ],
        'faq_answer' => [
            'label' => 'FAQ Answer',
            'max_length' => 500,
            'description' => 'Clear and helpful FAQ responses'
        ],
        'custom' => [
            'label' => 'Custom Copy',
            'max_length' => 2000,
            'description' => 'Custom copywriting with your own parameters'
        ]
    ];

    /** @var array Supported tones */
    private const TONES = [
        'professional' => 'Professional and authoritative',
        'casual' => 'Friendly and conversational',
        'persuasive' => 'Compelling and action-oriented',
        'informative' => 'Educational and factual',
        'playful' => 'Fun and lighthearted',
        'urgent' => 'Time-sensitive and pressing',
        'empathetic' => 'Understanding and supportive',
        'luxurious' => 'Premium and exclusive',
        'minimalist' => 'Simple and clean',
        'bold' => 'Confident and assertive'
    ];

    /** @var array Supported audiences */
    private const AUDIENCES = [
        'general' => 'General audience',
        'business' => 'Business professionals',
        'technical' => 'Technical/Developer audience',
        'consumer' => 'Everyday consumers',
        'enterprise' => 'Enterprise decision makers',
        'creative' => 'Creative professionals',
        'academic' => 'Academic/Research audience',
        'youth' => 'Younger demographics (18-25)',
        'senior' => 'Senior demographics (55+)',
        'parents' => 'Parents and families'
    ];

    /**
     * Constructor
     *
     * @param int|null $userId Current user ID
     * @param int|null $tenantId Current tenant ID
     */
    public function __construct(?int $userId = null, ?int $tenantId = null)
    {
        $this->db = \core\Database::connection();
        $this->userId = $userId;
        $this->tenantId = $tenantId;
        $this->loadConfig();
    }

    /**
     * Load AI configuration
     */
    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../config/ai.php';

        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            $this->config = [
                'default_provider' => 'openai',
                'providers' => []
            ];
        }

        $this->provider = $this->config['default_provider'] ?? 'openai';
    }

    /**
     * Set the AI provider to use
     *
     * @param string $provider Provider name
     * @return self
     */
    public function setProvider(string $provider): self
    {
        if (isset($this->config['providers'][$provider])) {
            $this->provider = $provider;
        }
        return $this;
    }

    /**
     * Set override model for API calls
     *
     * @param string|null $model Model identifier (e.g., 'gpt-4.1-mini')
     * @return self
     */
    public function setModel(?string $model): self
    {
        $this->overrideModel = $model;
        return $this;
    }

    /**
     * Get available copy types
     *
     * @return array
     */
    public function getCopyTypes(): array
    {
        return self::COPY_TYPES;
    }

    /**
     * Get available tones
     *
     * @return array
     */
    public function getTones(): array
    {
        return self::TONES;
    }

    /**
     * Get available audiences
     *
     * @return array
     */
    public function getAudiences(): array
    {
        return self::AUDIENCES;
    }

    /**
     * Generate copy based on parameters
     *
     * @param array $params Generation parameters
     * @return array Result with generated copy and metadata
     */
    public function generate(array $params): array
    {
        $copyType = $params['copy_type'] ?? 'custom';
        $topic = $params['topic'] ?? '';
        $context = $params['context'] ?? '';
        $tone = $params['tone'] ?? 'professional';
        $audience = $params['audience'] ?? 'general';
        $keywords = $params['keywords'] ?? [];
        $maxLength = $params['max_length'] ?? (self::COPY_TYPES[$copyType]['max_length'] ?? 500);
        $variants = (int) ($params['variants'] ?? 1);
        $language = $params['language'] ?? 'en';
        $brandVoice = $params['brand_voice'] ?? '';
        $avoidWords = $params['avoid_words'] ?? [];
        $includeEmoji = $params['include_emoji'] ?? false;

        // Validate copy type
        if (!isset(self::COPY_TYPES[$copyType])) {
            return $this->errorResponse('Invalid copy type');
        }

        // Validate topic
        if (empty(trim($topic))) {
            return $this->errorResponse('Topic is required');
        }

        // Limit variants
        $variants = min(max($variants, 1), 5);

        // Build the prompt
        $prompt = $this->buildPrompt([
            'copy_type' => $copyType,
            'topic' => $topic,
            'context' => $context,
            'tone' => $tone,
            'audience' => $audience,
            'keywords' => $keywords,
            'max_length' => $maxLength,
            'variants' => $variants,
            'language' => $language,
            'brand_voice' => $brandVoice,
            'avoid_words' => $avoidWords,
            'include_emoji' => $includeEmoji
        ]);

        // Call AI provider
        $aiResponse = $this->callAIProvider($prompt, $maxLength * $variants);

        if (!$aiResponse['success']) {
            return $aiResponse;
        }

        // Parse and structure the response
        $copies = $this->parseResponse($aiResponse['content'], $variants);

        // Log the generation
        $this->logGeneration($copyType, $topic, $copies);

        return [
            'success' => true,
            'copies' => $copies,
            'metadata' => [
                'copy_type' => $copyType,
                'tone' => $tone,
                'audience' => $audience,
                'variants_generated' => count($copies),
                'provider' => $this->provider,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Improve existing copy
     *
     * @param string $originalCopy The original copy to improve
     * @param array $params Improvement parameters
     * @return array
     */
    public function improve(string $originalCopy, array $params = []): array
    {
        if (empty(trim($originalCopy))) {
            return $this->errorResponse('Original copy is required');
        }

        $improvementType = $params['improvement_type'] ?? 'general';
        $tone = $params['tone'] ?? null;
        $targetLength = $params['target_length'] ?? null;

        $improvementInstructions = match($improvementType) {
            'shorten' => 'Make this copy more concise while preserving the key message',
            'lengthen' => 'Expand this copy with more detail and persuasive elements',
            'simplify' => 'Simplify the language for easier reading',
            'formalize' => 'Make this copy more formal and professional',
            'casualize' => 'Make this copy more casual and conversational',
            'seo' => 'Optimize this copy for search engines while maintaining readability',
            'emotional' => 'Add more emotional appeal to this copy',
            'clarity' => 'Improve clarity and remove any ambiguity',
            'power_words' => 'Add power words to make this copy more compelling',
            default => 'Improve this copy for better engagement and clarity'
        };

        $prompt = "Original copy:\n\"{$originalCopy}\"\n\n";
        $prompt .= "Instructions: {$improvementInstructions}\n";

        if ($tone) {
            $toneDesc = self::TONES[$tone] ?? $tone;
            $prompt .= "Target tone: {$toneDesc}\n";
        }

        if ($targetLength) {
            $prompt .= "Target length: approximately {$targetLength} characters\n";
        }

        $prompt .= "\nProvide the improved version only, without explanations.";

        $aiResponse = $this->callAIProvider($prompt, 2000);

        if (!$aiResponse['success']) {
            return $aiResponse;
        }

        return [
            'success' => true,
            'original' => $originalCopy,
            'improved' => trim($aiResponse['content']),
            'improvement_type' => $improvementType,
            'metadata' => [
                'provider' => $this->provider,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Analyze copy for quality and suggestions
     *
     * @param string $copy The copy to analyze
     * @return array
     */
    public function analyze(string $copy): array
    {
        if (empty(trim($copy))) {
            return $this->errorResponse('Copy is required for analysis');
        }

        $prompt = "Analyze this copy and provide a JSON response with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= "  \"readability_score\": (1-10),\n";
        $prompt .= "  \"persuasion_score\": (1-10),\n";
        $prompt .= "  \"clarity_score\": (1-10),\n";
        $prompt .= "  \"emotional_impact\": (1-10),\n";
        $prompt .= "  \"detected_tone\": \"tone name\",\n";
        $prompt .= "  \"detected_audience\": \"audience type\",\n";
        $prompt .= "  \"strengths\": [\"strength 1\", \"strength 2\"],\n";
        $prompt .= "  \"weaknesses\": [\"weakness 1\", \"weakness 2\"],\n";
        $prompt .= "  \"suggestions\": [\"suggestion 1\", \"suggestion 2\"],\n";
        $prompt .= "  \"word_count\": number,\n";
        $prompt .= "  \"reading_time_seconds\": number\n";
        $prompt .= "}\n\n";
        $prompt .= "Copy to analyze:\n\"{$copy}\"\n\n";
        $prompt .= "Respond with valid JSON only.";

        $aiResponse = $this->callAIProvider($prompt, 1000);

        if (!$aiResponse['success']) {
            return $aiResponse;
        }

        // Parse JSON response
        $analysis = json_decode($aiResponse['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: basic analysis without AI parsing
            $wordCount = str_word_count($copy);
            $analysis = [
                'readability_score' => 0,
                'persuasion_score' => 0,
                'clarity_score' => 0,
                'emotional_impact' => 0,
                'detected_tone' => 'unknown',
                'detected_audience' => 'general',
                'strengths' => [],
                'weaknesses' => ['Could not fully analyze copy'],
                'suggestions' => ['Try regenerating the analysis'],
                'word_count' => $wordCount,
                'reading_time_seconds' => (int) ($wordCount / 3.5)
            ];
        }

        // Add calculated metrics
        $analysis['character_count'] = strlen($copy);
        $analysis['sentence_count'] = preg_match_all('/[.!?]+/', $copy, $matches);

        return [
            'success' => true,
            'analysis' => $analysis,
            'metadata' => [
                'provider' => $this->provider,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Generate A/B test variants
     *
     * @param string $originalCopy Base copy for variants
     * @param int $variantCount Number of variants to generate
     * @param array $params Additional parameters
     * @return array
     */
    public function generateABVariants(string $originalCopy, int $variantCount = 3, array $params = []): array
    {
        if (empty(trim($originalCopy))) {
            return $this->errorResponse('Original copy is required');
        }

        $variantCount = min(max($variantCount, 2), 5);
        $focusArea = $params['focus_area'] ?? 'general';

        $focusInstructions = match($focusArea) {
            'headline' => 'Focus on varying the headline approach (question, statement, statistic, etc.)',
            'cta' => 'Focus on varying the call-to-action phrasing and urgency',
            'emotion' => 'Focus on varying the emotional appeal (fear, joy, curiosity, etc.)',
            'length' => 'Create variants of different lengths (short, medium, long)',
            'format' => 'Vary the format (bullet points vs paragraph, with/without numbers)',
            default => 'Create meaningfully different variations while maintaining the core message'
        };

        $prompt = "Original copy:\n\"{$originalCopy}\"\n\n";
        $prompt .= "Create {$variantCount} distinct A/B test variants.\n";
        $prompt .= "Focus: {$focusInstructions}\n\n";
        $prompt .= "Format each variant as:\n";
        $prompt .= "VARIANT 1:\n[copy]\n\nVARIANT 2:\n[copy]\n\n";
        $prompt .= "Make each variant meaningfully different to enable proper A/B testing.";

        $aiResponse = $this->callAIProvider($prompt, 3000);

        if (!$aiResponse['success']) {
            return $aiResponse;
        }

        // Parse variants
        $variants = $this->parseVariants($aiResponse['content'], $variantCount);

        return [
            'success' => true,
            'original' => $originalCopy,
            'variants' => $variants,
            'focus_area' => $focusArea,
            'metadata' => [
                'variants_generated' => count($variants),
                'provider' => $this->provider,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Translate and localize copy
     *
     * @param string $copy Copy to translate
     * @param string $targetLanguage Target language code
     * @param array $params Additional parameters
     * @return array
     */
    public function translateAndLocalize(string $copy, string $targetLanguage, array $params = []): array
    {
        if (empty(trim($copy))) {
            return $this->errorResponse('Copy is required for translation');
        }

        $culturalAdaptation = $params['cultural_adaptation'] ?? true;
        $preserveTone = $params['preserve_tone'] ?? true;

        $languageNames = [
            'en' => 'English', 'es' => 'Spanish', 'fr' => 'French',
            'de' => 'German', 'it' => 'Italian', 'pt' => 'Portuguese',
            'nl' => 'Dutch', 'ru' => 'Russian', 'zh' => 'Chinese',
            'ja' => 'Japanese', 'ko' => 'Korean', 'ar' => 'Arabic'
        ];

        $targetLangName = $languageNames[$targetLanguage] ?? $targetLanguage;

        $prompt = "Translate and localize this marketing copy to {$targetLangName}:\n\n";
        $prompt .= "\"{$copy}\"\n\n";
        $prompt .= "Requirements:\n";

        if ($culturalAdaptation) {
            $prompt .= "- Adapt cultural references, idioms, and expressions for the target market\n";
        }

        if ($preserveTone) {
            $prompt .= "- Preserve the original tone and persuasive intent\n";
        }

        $prompt .= "- Maintain marketing effectiveness in the target language\n";
        $prompt .= "- Keep similar length where possible\n\n";
        $prompt .= "Provide only the translated copy without explanations.";

        $aiResponse = $this->callAIProvider($prompt, 2000);

        if (!$aiResponse['success']) {
            return $aiResponse;
        }

        return [
            'success' => true,
            'original' => $copy,
            'translated' => trim($aiResponse['content']),
            'target_language' => $targetLanguage,
            'metadata' => [
                'cultural_adaptation' => $culturalAdaptation,
                'preserve_tone' => $preserveTone,
                'provider' => $this->provider,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Get copy generation history for current user/tenant
     *
     * @param int $limit Number of records to retrieve
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getHistory(int $limit = 20, int $offset = 0): array
    {
        $limit = min(max($limit, 1), 100);
        $offset = max($offset, 0);

        $sql = "SELECT * FROM ai_copywriter_history WHERE 1=1";
        $params = [];

        if ($this->tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $this->tenantId;
        }

        if ($this->userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $this->userId;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $history = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'history' => $history,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ];
        } catch (\PDOException $e) {
            // Table may not exist yet
            return [
                'success' => true,
                'history' => [],
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ];
        }
    }

    /**
     * Save copy to favorites
     *
     * @param string $copy Copy to save
     * @param string $copyType Type of copy
     * @param string $name Optional name for the saved copy
     * @return array
     */
    public function saveFavorite(string $copy, string $copyType, string $name = ''): array
    {
        if (empty(trim($copy))) {
            return $this->errorResponse('Copy is required');
        }

        $sql = "INSERT INTO ai_copywriter_favorites
                (user_id, tenant_id, copy_type, name, content, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->userId,
                $this->tenantId,
                $copyType,
                $name ?: 'Untitled',
                $copy
            ]);

            return [
                'success' => true,
                'message' => 'Copy saved to favorites',
                'id' => $this->db->lastInsertId()
            ];
        } catch (\PDOException $e) {
            return $this->errorResponse('Failed to save favorite: ' . $e->getMessage());
        }
    }

    /**
     * Get saved favorites
     *
     * @param int $limit Number of records
     * @return array
     */
    public function getFavorites(int $limit = 50): array
    {
        $sql = "SELECT * FROM ai_copywriter_favorites WHERE 1=1";
        $params = [];

        if ($this->tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $this->tenantId;
        }

        if ($this->userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $this->userId;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'favorites' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ];
        } catch (\PDOException $e) {
            return [
                'success' => true,
                'favorites' => []
            ];
        }
    }

    /**
     * Delete a favorite
     *
     * @param int $favoriteId Favorite ID to delete
     * @return array
     */
    public function deleteFavorite(int $favoriteId): array
    {
        $sql = "DELETE FROM ai_copywriter_favorites WHERE id = ?";
        $params = [$favoriteId];

        if ($this->userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $this->userId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Favorite deleted'
            ];
        } catch (\PDOException $e) {
            return $this->errorResponse('Failed to delete favorite');
        }
    }

    /**
     * Build the AI prompt based on parameters
     *
     * @param array $params Generation parameters
     * @return string
     */
    private function buildPrompt(array $params): string
    {
        $copyTypeInfo = self::COPY_TYPES[$params['copy_type']];
        $toneDesc = self::TONES[$params['tone']] ?? $params['tone'];
        $audienceDesc = self::AUDIENCES[$params['audience']] ?? $params['audience'];

        $prompt = "You are an expert copywriter. Generate {$params['variants']} ";
        $prompt .= $params['variants'] > 1 ? "variants of " : "";
        $prompt .= "{$copyTypeInfo['label']} copy.\n\n";

        $prompt .= "Topic/Product: {$params['topic']}\n";

        if (!empty($params['context'])) {
            $prompt .= "Additional context: {$params['context']}\n";
        }

        $prompt .= "Tone: {$toneDesc}\n";
        $prompt .= "Target audience: {$audienceDesc}\n";
        $prompt .= "Maximum length: {$params['max_length']} characters per variant\n";

        if (!empty($params['keywords'])) {
            $keywordStr = is_array($params['keywords'])
                ? implode(', ', $params['keywords'])
                : $params['keywords'];
            $prompt .= "Include these keywords naturally: {$keywordStr}\n";
        }

        if (!empty($params['brand_voice'])) {
            $prompt .= "Brand voice guidelines: {$params['brand_voice']}\n";
        }

        if (!empty($params['avoid_words'])) {
            $avoidStr = is_array($params['avoid_words'])
                ? implode(', ', $params['avoid_words'])
                : $params['avoid_words'];
            $prompt .= "Avoid these words/phrases: {$avoidStr}\n";
        }

        if ($params['include_emoji']) {
            $prompt .= "Include relevant emojis where appropriate.\n";
        }

        if ($params['language'] !== 'en') {
            $prompt .= "Language: {$params['language']}\n";
        }

        if ($params['variants'] > 1) {
            $prompt .= "\nFormat each variant as:\nVARIANT 1:\n[copy]\n\nVARIANT 2:\n[copy]\n";
            $prompt .= "Make each variant distinctly different in approach.";
        } else {
            $prompt .= "\nProvide only the copy, without explanations or labels.";
        }

        return $prompt;
    }

    /**
     * Call the AI provider API
     *
     * @param string $prompt The prompt to send
     * @param int $maxTokens Maximum tokens for response
     * @return array
     */
    private function callAIProvider(string $prompt, int $maxTokens = 1000): array
    {
        $provider = $this->provider;
        $model = $this->overrideModel ?? '';

        // Use ai_universal_generate for all providers
        $result = ai_universal_generate($provider, $model, 'You are an expert copywriter who creates compelling, effective marketing copy.', $prompt, [
            'max_tokens' => min($maxTokens, 4000),
            'temperature' => 0.7,
        ]);

        if (!$result['ok']) {
            return $this->errorResponse($result['error'] ?? 'AI generation failed');
        }

        $text = trim($result['content'] ?? $result['text'] ?? '');
        if (empty($text)) {
            return $this->errorResponse('Empty response from AI');
        }

        return [
            'success' => true,
            'content' => $text,
            'usage' => $result['usage'] ?? null
        ];
    }

    /**
     * Parse AI response into structured copy variants
     *
     * @param string $response Raw AI response
     * @param int $expectedVariants Expected number of variants
     * @return array
     */
    private function parseResponse(string $response, int $expectedVariants): array
    {
        if ($expectedVariants === 1) {
            return [['content' => trim($response), 'variant' => 1]];
        }

        $copies = [];

        // Try to parse VARIANT N: format
        if (preg_match_all('/VARIANT\s*(\d+)\s*:\s*\n?(.*?)(?=VARIANT\s*\d+\s*:|$)/si', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $copies[] = [
                    'content' => trim($match[2]),
                    'variant' => (int) $match[1]
                ];
            }
        }

        // Fallback: split by numbered list
        if (empty($copies)) {
            if (preg_match_all('/^\s*(\d+)\.\s*(.+?)(?=^\s*\d+\.|$)/ms', $response, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $copies[] = [
                        'content' => trim($match[2]),
                        'variant' => (int) $match[1]
                    ];
                }
            }
        }

        // Fallback: split by double newlines
        if (empty($copies)) {
            $parts = preg_split('/\n\n+/', $response);
            $variantNum = 1;
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $copies[] = [
                        'content' => $part,
                        'variant' => $variantNum++
                    ];
                }
            }
        }

        // If still nothing, return the whole response as one variant
        if (empty($copies)) {
            $copies[] = ['content' => trim($response), 'variant' => 1];
        }

        return $copies;
    }

    /**
     * Parse A/B variants from response
     *
     * @param string $response Raw response
     * @param int $expectedCount Expected variant count
     * @return array
     */
    private function parseVariants(string $response, int $expectedCount): array
    {
        $variants = [];

        if (preg_match_all('/VARIANT\s*(\d+)\s*:\s*\n?(.*?)(?=VARIANT\s*\d+\s*:|$)/si', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $variants[] = [
                    'id' => 'variant_' . $match[1],
                    'content' => trim($match[2]),
                    'label' => 'Variant ' . $match[1]
                ];
            }
        }

        if (empty($variants)) {
            $parts = preg_split('/\n\n+/', $response);
            $num = 1;
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $variants[] = [
                        'id' => 'variant_' . $num,
                        'content' => $part,
                        'label' => 'Variant ' . $num
                    ];
                    $num++;
                }
            }
        }

        return $variants;
    }

    /**
     * Log generation to history
     *
     * @param string $copyType Type of copy
     * @param string $topic Topic/subject
     * @param array $copies Generated copies
     */
    private function logGeneration(string $copyType, string $topic, array $copies): void
    {
        $sql = "INSERT INTO ai_copywriter_history
                (user_id, tenant_id, copy_type, topic, copies_generated, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->userId,
                $this->tenantId,
                $copyType,
                $topic,
                json_encode($copies)
            ]);
        } catch (\PDOException $e) {
            // Silently fail - logging should not break generation
            error_log("AICopywriter log error: " . $e->getMessage());
        }
    }

    /**
     * Create error response
     *
     * @param string $message Error message
     * @return array
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message
        ];
    }

    /**
     * Get database migration SQL for required tables
     *
     * @return string
     */
    public static function getMigrationSQL(): string
    {
        return "
        CREATE TABLE IF NOT EXISTS ai_copywriter_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            tenant_id INT NULL,
            copy_type VARCHAR(50) NOT NULL,
            topic VARCHAR(500) NOT NULL,
            copies_generated JSON,
            created_at DATETIME NOT NULL,
            INDEX idx_user_tenant (user_id, tenant_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS ai_copywriter_favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            tenant_id INT NULL,
            copy_type VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL DEFAULT 'Untitled',
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_user_tenant (user_id, tenant_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
}
