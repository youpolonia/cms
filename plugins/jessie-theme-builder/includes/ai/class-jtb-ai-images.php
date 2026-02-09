<?php
/**
 * JTB AI Images
 * AI-powered image generation using DALL-E, Stability AI, or HuggingFace
 *
 * API keys stored in database:
 * - openai_api_key (for DALL-E)
 * - stability_api_key (for Stability AI)
 * - huggingface_api_key (for HuggingFace)
 *
 * @package JessieThemeBuilder
 * @updated 2026-02-04 - Etap 2: Przeniesiono konfigurację do bazy danych
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Images
{
    // Cache for API keys
    private static array $apiKeys = [];

    // ========================================
    // Configuration - All from Database
    // ========================================

    /**
     * Get API key from database
     * @param string $provider Provider name (openai, stability, huggingface)
     * @return string|null API key or null
     */
    private static function getApiKey(string $provider): ?string
    {
        if (isset(self::$apiKeys[$provider])) {
            return self::$apiKeys[$provider] ?: null;
        }

        $keyMap = [
            'openai' => 'openai_api_key',
            'stability' => 'stability_api_key',
            'huggingface' => 'huggingface_api_key'
        ];

        $settingKey = $keyMap[$provider] ?? $provider . '_api_key';

        // Priority 1: Check database (CMS settings)
        if (class_exists('\\core\\Database')) {
            try {
                $db = \core\Database::connection();
                $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ? LIMIT 1");
                $stmt->execute([$settingKey]);
                $key = $stmt->fetchColumn();
                if (!empty($key)) {
                    self::$apiKeys[$provider] = $key;
                    return self::$apiKeys[$provider];
                }
            } catch (\Exception $e) {
                // Continue to fallback
            }
        }

        // Priority 2: Check central ai_settings.json (unified config)
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['providers'][$provider]['api_key'])) {
                self::$apiKeys[$provider] = $settings['providers'][$provider]['api_key'];
                return self::$apiKeys[$provider];
            }
            // Also check media section for image providers
            if (!empty($settings['media'][$provider]['api_key'])) {
                self::$apiKeys[$provider] = $settings['media'][$provider]['api_key'];
                return self::$apiKeys[$provider];
            }
        }

        self::$apiKeys[$provider] = '';
        return null;
    }

    /**
     * Get image generation configuration
     * Checks which provider is available
     * @return array Configuration with enabled flag and provider
     */
    private static function getImageConfig(): array
    {
        $config = [
            'enabled' => false,
            'provider' => null
        ];

        // Priority: OpenAI (DALL-E) > Stability > HuggingFace
        $providers = ['openai', 'stability', 'huggingface'];

        foreach ($providers as $provider) {
            $key = self::getApiKey($provider);
            if (!empty($key) && strlen($key) > 20) {
                $config['enabled'] = true;
                $config['provider'] = $provider;
                return $config;
            }
        }

        return $config;
    }

    /**
     * Check if image generation is available
     * @return bool True if any provider is configured
     */
    public static function isConfigured(): bool
    {
        $config = self::getImageConfig();
        return $config['enabled'];
    }

    /**
     * Get available provider name
     * @return string|null Provider name or null
     */
    public static function getProvider(): ?string
    {
        $config = self::getImageConfig();
        return $config['provider'];
    }

    // ========================================
    // Main Image Generation Methods
    // ========================================

    /**
     * Generate an image from prompt
     * @param string $prompt Image description prompt
     * @param array $options Generation options (width, height, style)
     * @return array Result with image URL or error
     */
    public static function generateImage(string $prompt, array $options = []): array
    {
        $config = self::getImageConfig();

        if (!$config['enabled']) {
            return [
                'ok' => false,
                'error' => 'Image generation not configured. Add openai_api_key, stability_api_key, or huggingface_api_key to settings table.',
                'url' => null
            ];
        }

        // Apply defaults
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;
        $style = $options['style'] ?? 'photorealistic';

        // Build enhanced prompt
        $enhancedPrompt = self::enhancePrompt($prompt, $style);

        // Call appropriate provider
        $result = match($config['provider']) {
            'openai' => self::generateWithOpenAI($enhancedPrompt, $width, $height),
            'stability' => self::generateWithStability($enhancedPrompt, $width, $height),
            'huggingface' => self::generateWithHuggingFace($enhancedPrompt, $width, $height),
            default => ['ok' => false, 'error' => 'Unknown image provider']
        };

        // Upload to media library if successful
        if ($result['ok'] && !empty($result['url'])) {
            $uploaded = self::uploadToMediaLibrary($result['url'], [
                'prompt' => $prompt,
                'style' => $style,
                'width' => $width,
                'height' => $height,
                'source' => 'ai_generated',
                'provider' => $config['provider']
            ]);

            if ($uploaded['ok']) {
                $result['media_id'] = $uploaded['media_id'];
                $result['local_url'] = $uploaded['url'];
            }
        }

        return $result;
    }

    /**
     * Generate hero image for landing pages
     * @param array $context Site/page context
     * @return array Result with image URL
     */
    public static function generateHeroImage(array $context): array
    {
        $industry = $context['industry'] ?? 'business';
        $style = $context['style'] ?? 'professional';

        $prompts = [
            'technology' => 'Modern tech office space with developers working at computers, large monitors showing code and data visualizations, blue ambient lighting, professional atmosphere, high-tech environment',
            'healthcare' => 'Bright modern medical facility lobby, friendly doctor in white coat consulting with patient, clean white interior with natural lighting, warm and welcoming healthcare environment',
            'ecommerce' => 'Elegant product showcase photography setup, minimalist white background, professional lighting, lifestyle e-commerce setting with quality products',
            'agency' => 'Creative studio workspace, diverse design team collaborating around modern furniture, colorful accent walls, natural lighting through large windows',
            'education' => 'Modern learning environment with students engaged in collaborative discussion, bright open classroom space, educational technology visible',
            'restaurant' => 'Elegant fine dining atmosphere, beautifully plated gourmet food, warm ambient lighting, sophisticated interior design with attention to detail',
            'real_estate' => 'Stunning modern home interior with open floor plan, natural lighting streaming through large windows, elegant contemporary furnishings',
            'fitness' => 'Dynamic modern gym environment, athlete training with professional equipment, energetic atmosphere with motivational lighting',
            'legal' => 'Professional law office with wood-paneled walls, leather furniture, bookshelves with legal volumes, confident attorney at desk',
            'general' => 'Professional modern business environment, diverse team collaborating in open office space, natural lighting, success and productivity atmosphere'
        ];

        $prompt = $prompts[$industry] ?? $prompts['general'];

        return self::generateImage($prompt, [
            'width' => 1920,
            'height' => 1080,
            'style' => $style
        ]);
    }

    /**
     * Generate background image
     * @param array $context Context data
     * @return array Result with image URL
     */
    public static function generateBackgroundImage(array $context): array
    {
        $type = $context['background_type'] ?? 'abstract';

        $prompts = [
            'abstract' => 'Abstract geometric shapes flowing smoothly, soft gradients in blue and purple tones, minimalist design, subtle texture, suitable for website background',
            'gradient' => 'Smooth color gradient transition, professional blue to purple tones, subtle texture overlay, modern clean design, website background',
            'pattern' => 'Elegant seamless geometric pattern, subtle shadows and depth, professional aesthetic, light colors, website background texture',
            'texture' => 'Subtle paper texture background, organic patterns, soft neutral colors, minimalist clean appearance',
            'nature' => 'Blurred nature background with soft bokeh effect, gentle natural light, calming green and blue tones, peaceful atmosphere'
        ];

        $prompt = $prompts[$type] ?? $prompts['abstract'];

        return self::generateImage($prompt, [
            'width' => 1920,
            'height' => 1080,
            'style' => 'artistic'
        ]);
    }

    /**
     * Generate feature icon/illustration
     * @param array $context Context with feature description
     * @return array Result with image URL
     */
    public static function generateFeatureIcon(array $context): array
    {
        $feature = $context['feature'] ?? 'generic feature';
        $style = $context['icon_style'] ?? 'flat';

        $prompt = "Professional icon illustration representing {$feature}, {$style} design style, clean vector lines, single accent color on white background, suitable for web use, no text";

        return self::generateImage($prompt, [
            'width' => 512,
            'height' => 512,
            'style' => 'illustration'
        ]);
    }

    /**
     * Generate team member photo
     * @param array $context Context with member details
     * @return array Result with image URL
     */
    public static function generateTeamPhoto(array $context): array
    {
        $gender = $context['gender'] ?? 'person';
        $role = $context['role'] ?? 'professional';

        $prompt = "Professional corporate headshot portrait, {$gender} business {$role}, confident friendly smile, neutral gray background, professional studio lighting, sharp focus, high quality";

        return self::generateImage($prompt, [
            'width' => 512,
            'height' => 512,
            'style' => 'photorealistic'
        ]);
    }

    /**
     * Generate product image
     * @param array $context Context with product details
     * @return array Result with image URL
     */
    public static function generateProductImage(array $context): array
    {
        $product = $context['product'] ?? 'product';
        $style = $context['product_style'] ?? 'professional';

        $prompt = "Professional product photography of {$product}, clean white background, soft shadows, commercial quality lighting, e-commerce style, high resolution detail";

        return self::generateImage($prompt, [
            'width' => 1024,
            'height' => 1024,
            'style' => $style
        ]);
    }

    /**
     * Generate testimonial avatar
     * @param array $context Context with person details
     * @return array Result with image URL
     */
    public static function generateTestimonialAvatar(array $context): array
    {
        $gender = $context['gender'] ?? 'person';
        $age = $context['age'] ?? 'middle-aged';

        $prompt = "Professional portrait photo of {$age} {$gender}, friendly approachable expression, soft blurred background, natural lighting, trustworthy appearance, high quality headshot";

        return self::generateImage($prompt, [
            'width' => 256,
            'height' => 256,
            'style' => 'photorealistic'
        ]);
    }

    // ========================================
    // Provider-Specific Methods
    // ========================================

    /**
     * Generate image with OpenAI DALL-E 3
     */
    private static function generateWithOpenAI(string $prompt, int $width, int $height): array
    {
        $apiKey = self::getApiKey('openai');

        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'OpenAI API key not configured'];
        }

        // DALL-E 3 size options: 1024x1024, 1024x1792, 1792x1024
        $size = self::mapToDALLESize($width, $height);

        $requestBody = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'size' => $size,
            'quality' => 'standard',
            'n' => 1
        ];

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['ok' => false, 'error' => 'Connection error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $error = $errorData['error']['message'] ?? "HTTP {$httpCode}";
            return ['ok' => false, 'error' => 'OpenAI error: ' . $error];
        }

        $data = json_decode($response, true);
        $imageUrl = $data['data'][0]['url'] ?? null;

        if ($imageUrl) {
            return [
                'ok' => true,
                'url' => $imageUrl,
                'source' => 'openai',
                'revised_prompt' => $data['data'][0]['revised_prompt'] ?? null
            ];
        }

        return ['ok' => false, 'error' => 'No image URL in OpenAI response'];
    }

    /**
     * Generate image with Stability AI
     */
    private static function generateWithStability(string $prompt, int $width, int $height): array
    {
        $apiKey = self::getApiKey('stability');

        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'Stability AI API key not configured'];
        }

        $requestBody = [
            'text_prompts' => [
                ['text' => $prompt, 'weight' => 1]
            ],
            'cfg_scale' => 7,
            'height' => self::roundToMultiple($height, 64),
            'width' => self::roundToMultiple($width, 64),
            'samples' => 1,
            'steps' => 30
        ];

        $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['ok' => false, 'error' => 'Connection error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $error = $errorData['message'] ?? "HTTP {$httpCode}";
            return ['ok' => false, 'error' => 'Stability error: ' . $error];
        }

        $data = json_decode($response, true);
        $base64 = $data['artifacts'][0]['base64'] ?? null;

        if ($base64) {
            $imageData = base64_decode($base64);
            $savedPath = self::saveImageData($imageData, 'image/png');

            if ($savedPath) {
                return [
                    'ok' => true,
                    'url' => $savedPath,
                    'source' => 'stability'
                ];
            }
        }

        return ['ok' => false, 'error' => 'Failed to process Stability AI response'];
    }

    /**
     * Generate image with HuggingFace
     */
    private static function generateWithHuggingFace(string $prompt, int $width, int $height): array
    {
        $apiKey = self::getApiKey('huggingface');

        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'HuggingFace API key not configured'];
        }

        // Use Stable Diffusion XL
        $model = 'stabilityai/stable-diffusion-xl-base-1.0';

        $requestBody = [
            'inputs' => $prompt,
            'parameters' => [
                'width' => min($width, 1024),
                'height' => min($height, 1024),
                'num_inference_steps' => 30
            ]
        ];

        $url = "https://api-inference.huggingface.co/models/{$model}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['ok' => false, 'error' => 'Connection error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => "HuggingFace error: HTTP {$httpCode}"];
        }

        // Check if response is image binary
        if (strpos($contentType, 'image/') === 0) {
            $savedPath = self::saveImageData($response, $contentType);
            if ($savedPath) {
                return [
                    'ok' => true,
                    'url' => $savedPath,
                    'source' => 'huggingface'
                ];
            }
        }

        return ['ok' => false, 'error' => 'Invalid response from HuggingFace'];
    }

    // ========================================
    // Media Library Integration
    // ========================================

    /**
     * Upload image to media library
     * @param string $imageUrl URL or path of image
     * @param array $metadata Image metadata
     * @return array Result with media ID
     */
    public static function uploadToMediaLibrary(string $imageUrl, array $metadata = []): array
    {
        try {
            // Check if it's a remote URL or local path
            $isRemote = filter_var($imageUrl, FILTER_VALIDATE_URL);

            if ($isRemote) {
                $imageData = @file_get_contents($imageUrl);
                if ($imageData === false) {
                    return ['ok' => false, 'error' => 'Failed to download image'];
                }
            } else {
                $fullPath = strpos($imageUrl, '/') === 0 ? CMS_ROOT . $imageUrl : $imageUrl;
                if (!file_exists($fullPath)) {
                    return ['ok' => false, 'error' => 'Image file not found'];
                }
                $imageData = file_get_contents($fullPath);
            }

            // Determine extension
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            $extension = 'jpg';
            if (strpos($mimeType, 'png') !== false) {
                $extension = 'png';
            } elseif (strpos($mimeType, 'webp') !== false) {
                $extension = 'webp';
            }

            // Generate filename
            $filename = 'ai_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            // Create directory
            $mediaPath = CMS_ROOT . '/media/ai-generated';
            if (!is_dir($mediaPath)) {
                mkdir($mediaPath, 0755, true);
            }

            $filePath = $mediaPath . '/' . $filename;
            $relativeUrl = '/media/ai-generated/' . $filename;

            // Save file
            if (file_put_contents($filePath, $imageData) === false) {
                return ['ok' => false, 'error' => 'Failed to save image'];
            }

            // Get dimensions
            $imageInfo = @getimagesize($filePath);
            $width = $imageInfo[0] ?? 0;
            $height = $imageInfo[1] ?? 0;

            // Insert into media library database if table exists
            try {
                $db = \core\Database::connection();

                // Check if media table exists
                $stmt = $db->query("SHOW TABLES LIKE 'media'");
                if ($stmt->fetch()) {
                    $stmt = $db->prepare("
                        INSERT INTO media (filename, filepath, mime_type, filesize, width, height, alt_text, caption, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $filename,
                        $relativeUrl,
                        $mimeType,
                        filesize($filePath),
                        $width,
                        $height,
                        $metadata['prompt'] ?? 'AI Generated Image',
                        'Generated by JTB AI (' . ($metadata['provider'] ?? 'unknown') . ')'
                    ]);

                    $mediaId = (int)$db->lastInsertId();

                    return [
                        'ok' => true,
                        'media_id' => $mediaId,
                        'url' => $relativeUrl,
                        'filename' => $filename,
                        'width' => $width,
                        'height' => $height
                    ];
                }
            } catch (\Exception $e) {
                // Media table doesn't exist, continue without DB insert
            }

            return [
                'ok' => true,
                'media_id' => null,
                'url' => $relativeUrl,
                'filename' => $filename,
                'width' => $width,
                'height' => $height
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Images::uploadToMediaLibrary error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get media URL by ID
     * @param int $mediaId Media ID
     * @return string Media URL or empty string
     */
    public static function getMediaUrl(int $mediaId): string
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT filepath FROM media WHERE id = ?");
            $stmt->execute([$mediaId]);
            return $stmt->fetchColumn() ?: '';
        } catch (\Exception $e) {
            return '';
        }
    }

    // ========================================
    // Batch Operations
    // ========================================

    /**
     * Generate all images needed for a layout
     * @param array $layout Layout structure
     * @param array $context Generation context
     * @return array Results for each image
     */
    public static function generateImagesForLayout(array $layout, array $context = []): array
    {
        $results = [];
        $imagesToGenerate = [];

        // Walk through layout and find image placeholders
        self::findImagePlaceholders($layout, $imagesToGenerate);

        // Generate each image
        foreach ($imagesToGenerate as $imageConfig) {
            $result = self::generateImage(
                $imageConfig['prompt'],
                array_merge($imageConfig['options'] ?? [], $context)
            );

            $results[$imageConfig['id']] = $result;

            // Rate limiting - pause between requests
            if ($result['ok']) {
                usleep(500000); // 0.5 second delay
            }
        }

        return $results;
    }

    /**
     * Find image placeholders in layout
     */
    private static function findImagePlaceholders(array $content, array &$images): void
    {
        foreach ($content as $item) {
            // Check for image modules
            if (in_array($item['type'] ?? '', ['image', 'fullwidth_image', 'gallery', 'slider_item'])) {
                $attrs = $item['attrs'] ?? [];

                // Check if using placeholder or empty
                $src = $attrs['src'] ?? '';
                if (empty($src) || strpos($src, 'placeholder') !== false || strpos($src, 'example.com') !== false) {
                    $images[] = [
                        'id' => $item['id'] ?? uniqid('img_'),
                        'type' => $item['type'],
                        'prompt' => $attrs['ai_prompt'] ?? self::generateImagePromptFromContext($item),
                        'options' => [
                            'width' => $attrs['width'] ?? 1024,
                            'height' => $attrs['height'] ?? 768
                        ]
                    ];
                }
            }

            // Check for background images
            if (!empty($item['attrs']['background_image'])) {
                $bg = $item['attrs']['background_image'];
                if (strpos($bg, 'placeholder') !== false || strpos($bg, 'example.com') !== false) {
                    $images[] = [
                        'id' => ($item['id'] ?? uniqid('bg_')) . '_bg',
                        'type' => 'background',
                        'prompt' => $item['attrs']['ai_bg_prompt'] ?? 'Abstract professional background, subtle pattern',
                        'options' => ['width' => 1920, 'height' => 1080]
                    ];
                }
            }

            // Recurse into children
            if (!empty($item['children'])) {
                self::findImagePlaceholders($item['children'], $images);
            }
        }
    }

    // ========================================
    // Prompt Enhancement
    // ========================================

    /**
     * Enhance prompt with quality and style modifiers
     */
    private static function enhancePrompt(string $prompt, string $style): string
    {
        $styleModifiers = [
            'photorealistic' => ', professional photography, high resolution, natural lighting, sharp focus, 8K, ultra realistic',
            'artistic' => ', artistic style, creative composition, vibrant colors, expressive, museum quality',
            'illustration' => ', digital illustration, clean lines, flat design, vector style, professional graphic design',
            'minimal' => ', minimalist design, clean composition, negative space, elegant, refined simplicity',
            'professional' => ', professional quality, corporate style, polished, modern, premium aesthetic'
        ];

        $modifier = $styleModifiers[$style] ?? $styleModifiers['professional'];

        // Add quality enhancers and negative prompts
        $enhanced = $prompt . $modifier;
        $enhanced .= ', no text, no watermarks, no logos, no signatures, no artifacts';

        return $enhanced;
    }

    /**
     * Generate image prompt from context
     */
    private static function generateImagePromptFromContext(array $item): string
    {
        $type = $item['type'] ?? 'image';
        $attrs = $item['attrs'] ?? [];

        switch ($type) {
            case 'image':
                $alt = $attrs['alt'] ?? '';
                return !empty($alt) ? "Professional photo: {$alt}" : 'Professional image for website';

            case 'fullwidth_image':
                return 'Wide professional landscape image, high quality, suitable for hero section';

            case 'slider_item':
                $heading = $attrs['heading'] ?? '';
                return !empty($heading) ? "Professional image representing: {$heading}" : 'Professional slide image';

            default:
                return 'Professional website image, high quality';
        }
    }

    // ========================================
    // Utility Methods
    // ========================================

    /**
     * Save image data to file
     */
    private static function saveImageData(string $data, string $contentType): ?string
    {
        $extension = 'jpg';
        if (strpos($contentType, 'png') !== false) {
            $extension = 'png';
        } elseif (strpos($contentType, 'webp') !== false) {
            $extension = 'webp';
        }

        $filename = 'ai_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

        $mediaPath = CMS_ROOT . '/media/ai-generated';
        if (!is_dir($mediaPath)) {
            mkdir($mediaPath, 0755, true);
        }

        $filePath = $mediaPath . '/' . $filename;

        if (file_put_contents($filePath, $data)) {
            return '/media/ai-generated/' . $filename;
        }

        return null;
    }

    /**
     * Map dimensions to DALL-E size
     */
    private static function mapToDALLESize(int $width, int $height): string
    {
        // DALL-E 3 sizes: 1024x1024, 1024x1792, 1792x1024
        $ratio = $width / $height;

        if ($ratio > 1.5) {
            return '1792x1024'; // Landscape
        } elseif ($ratio < 0.67) {
            return '1024x1792'; // Portrait
        }

        return '1024x1024'; // Square
    }

    /**
     * Round to multiple (for Stability AI)
     */
    private static function roundToMultiple(int $value, int $multiple): int
    {
        return (int)(round($value / $multiple) * $multiple);
    }

    /**
     * Build image prompt from module context
     * @param string $type Image type (hero, background, etc.)
     * @param array $context Context data
     * @return string Image prompt
     */
    public static function buildImagePrompt(string $type, array $context): string
    {
        $industry = $context['industry'] ?? 'business';
        $style = $context['style'] ?? 'professional';

        $prompts = [
            'hero' => "Professional hero image for {$industry} website, {$style} style, high quality, engaging atmosphere",
            'background' => "Abstract background pattern, {$style} style, suitable for {$industry} website, subtle texture",
            'feature' => "Feature illustration for {$industry}, clean design, {$style} style, professional",
            'team' => "Professional business portrait headshot, natural lighting, neutral background",
            'product' => "Product photography, clean white background, {$style} lighting, commercial quality",
            'testimonial' => "Portrait photo, friendly expression, professional soft background"
        ];

        return $prompts[$type] ?? $prompts['hero'];
    }
}
