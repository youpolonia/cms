<?php
/**
 * SEO Toolkit Core Module
 * Provides basic SEO analysis and recommendations
 */

require_once CMS_ROOT . '/core/ai_hf.php';

class SEOToolkit {
    private $pageContent;
    private $metaData;
    private $aiHandler;

    public function __construct($content, $meta = []) {
        $this->pageContent = $content;
        $this->metaData = $meta;
        $this->aiHandler = new SEOApiHandler();
    }

    public function analyzeContent() {
        // Hook: before_seo_analysis
        if (function_exists('apply_hooks')) {
            $this->pageContent = apply_hooks('before_seo_analysis', $this->pageContent);
        }

        $analysis = [
            'word_count' => str_word_count($this->pageContent),
            'keyword_density' => $this->calculateKeywordDensity(),
            'meta_tags' => $this->checkMetaTags()
        ];

        // Integrate with AI components
        if (class_exists('SEORecommendationEngine')) {
            $analysis['recommendations'] = SEORecommendationEngine::generate($this->pageContent);
        }

        // Hook: after_seo_analysis
        if (function_exists('apply_hooks')) {
            $analysis = apply_hooks('after_seo_analysis', $analysis);
        }

        return $analysis;
    }

    private function calculateKeywordDensity() {
        // Basic keyword analysis implementation
        $words = str_word_count(strtolower($this->pageContent), 1);
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        return array_slice($wordFreq, 0, 5);
    }

    private function checkMetaTags() {
        $required = ['title', 'description'];
        $missing = array_diff($required, array_keys($this->metaData));
        return [
            'has_title' => !empty($this->metaData['title']),
            'has_description' => !empty($this->metaData['description']),
            'missing' => $missing
        ];
    }
    /**
     * Generate SEO meta tags with plugin support
     * @return array Generated meta tags
     */
    public function generateMetaTags() {
        $meta = [
            'title' => $this->metaData['title'] ?? '',
            'description' => $this->metaData['description'] ?? '',
            'keywords' => $this->calculateKeywordDensity()
        ];

        // Try Hugging Face AI generation if configured
        $hfConfig = ai_hf_config_load();
        if (ai_hf_is_configured($hfConfig)) {
            $generatedMeta = $this->generateMetaWithHuggingFace($hfConfig);
            if ($generatedMeta !== null) {
                // Merge AI-generated meta with existing, preferring AI-generated values
                $meta = array_merge($meta, $generatedMeta);
            }
            // If HF fails or returns null, fall through to use existing meta
        }

        // Hook: seo_meta_generation
        if (function_exists('apply_hooks')) {
            $meta = apply_hooks('seo_meta_generation', $meta);
        }

        return $meta;
    }

    /**
     * Generate meta tags using Hugging Face AI
     * @param array $hfConfig Hugging Face configuration
     * @return array|null Generated meta tags or null on failure
     */
    private function generateMetaWithHuggingFace(array $hfConfig) {
        // Build prompt for SEO meta generation
        $contentPreview = mb_substr($this->pageContent, 0, 500);
        $promptText = "Generate SEO meta tags for the following content:\n\n";
        $promptText .= $contentPreview . "\n\n";
        $promptText .= "Provide:\n";
        $promptText .= "1. A compelling meta title (50-60 characters)\n";
        $promptText .= "2. A descriptive meta description (150-160 characters)\n";
        $promptText .= "3. Relevant keywords (comma-separated)\n";

        // Configure inference options
        $options = [
            'max_new_tokens' => 256,
            'temperature' => 0.7,
            'top_p' => 0.9
        ];

        // Call Hugging Face API
        $result = ai_hf_infer($hfConfig, $promptText, $options);

        // Handle response
        if (!$result['ok']) {
            // Log error silently and return null to fallback
            if (function_exists('error_log')) {
                error_log('SEOToolkit HF error: ' . ($result['error'] ?? 'Unknown error'));
            }
            return null;
        }

        // Extract generated text from response
        $generatedText = null;
        if (is_array($result['json']) && !empty($result['json'])) {
            // Try to extract from common HF response formats
            if (isset($result['json'][0]['generated_text'])) {
                $generatedText = $result['json'][0]['generated_text'];
            } elseif (isset($result['json']['generated_text'])) {
                $generatedText = $result['json']['generated_text'];
            }
        }

        // Fallback to raw body if JSON extraction failed
        if (empty($generatedText) && !empty($result['body'])) {
            $generatedText = $result['body'];
        }

        // Normalize and validate
        $generatedText = trim($generatedText ?? '');
        if (empty($generatedText)) {
            return null;
        }

        // Parse the generated text to extract meta fields
        return $this->parseGeneratedMetaTags($generatedText);
    }

    /**
     * Parse AI-generated text into structured meta tags
     * @param string $text Generated text from AI
     * @return array Parsed meta tags
     */
    private function parseGeneratedMetaTags(string $text) {
        $meta = [];

        // Try to extract title (look for common patterns)
        if (preg_match('/title[:\s]+([^\n]+)/i', $text, $matches)) {
            $meta['title'] = trim($matches[1], ' "\'');
        }

        // Try to extract description
        if (preg_match('/description[:\s]+([^\n]+)/i', $text, $matches)) {
            $meta['description'] = trim($matches[1], ' "\'');
        }

        // Try to extract keywords
        if (preg_match('/keywords?[:\s]+([^\n]+)/i', $text, $matches)) {
            $keywordsStr = trim($matches[1], ' "\'');
            $meta['keywords'] = array_map('trim', explode(',', $keywordsStr));
        }

        // If structured parsing failed, try to use the text intelligently
        if (empty($meta['title']) || empty($meta['description'])) {
            $lines = array_filter(array_map('trim', explode("\n", $text)));

            if (!empty($lines[0]) && empty($meta['title'])) {
                // First line might be the title
                $meta['title'] = mb_substr($lines[0], 0, 60);
            }

            if (!empty($lines[1]) && empty($meta['description'])) {
                // Second line might be the description
                $meta['description'] = mb_substr($lines[1], 0, 160);
            }
        }

        return $meta;
    }
}
