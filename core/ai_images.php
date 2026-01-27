<?php
/**
 * AI Image Generator using OpenAI DALL-E
 * Pure PHP implementation, NO classes, NO CLI
 */

if (!defined('CMS_ROOT')) {
    http_response_code(403);
    exit;
}

if (!defined('AI_IMAGES_OUTPUT_DIR')) {
    define('AI_IMAGES_OUTPUT_DIR', CMS_ROOT . '/uploads/ai-images');
}

if (!defined('AI_IMAGES_WEB_PREFIX')) {
    define('AI_IMAGES_WEB_PREFIX', '/uploads/ai-images');
}

/**
 * Load OpenAI settings from central config
 */
function ai_images_load_openai_settings(): array
{
    $configPath = CMS_ROOT . '/config/ai_settings.json';

    if (!file_exists($configPath)) {
        return ['enabled' => false, 'api_key' => ''];
    }

    $json = @file_get_contents($configPath);
    if ($json === false) {
        return ['enabled' => false, 'api_key' => ''];
    }

    $settings = @json_decode($json, true);
    if (!is_array($settings)) {
        return ['enabled' => false, 'api_key' => ''];
    }

    $openai = $settings['providers']['openai'] ?? [];

    return [
        'enabled' => (bool)($openai['enabled'] ?? false),
        'api_key' => trim((string)($openai['api_key'] ?? '')),
    ];
}

/**
 * Check if DALL-E image generation is configured
 */
function ai_images_is_configured(): bool
{
    $settings = ai_images_load_openai_settings();
    return $settings['enabled'] && !empty($settings['api_key']);
}

/**
 * Guess image extension from binary data
 */
function ai_images_guess_extension(string $data): string
{
    if (strlen($data) < 16) {
        return '.bin';
    }

    $header = substr($data, 0, 16);

    if (strpos($header, "\x89PNG") === 0) {
        return '.png';
    }
    if (strpos($header, "\xFF\xD8") === 0) {
        return '.jpg';
    }
    if (strpos($header, 'GIF8') === 0) {
        return '.gif';
    }
    if (strpos($header, 'RIFF') === 0 && strpos($header, 'WEBP') !== false) {
        return '.webp';
    }

    return '.png';
}

/**
 * Generate SEO-friendly filename slug from text
 * Converts text to lowercase, removes special chars, replaces spaces with hyphens
 *
 * @param string $text Input text (prompt, title, etc.)
 * @param int $maxLength Maximum length of slug (default 60)
 * @return string SEO-friendly slug
 */
function ai_images_generate_seo_slug(string $text, int $maxLength = 60): string
{
    // Convert to lowercase
    $slug = mb_strtolower($text, 'UTF-8');
    
    // Replace Polish characters
    $polish = ['ą','ć','ę','ł','ń','ó','ś','ź','ż','Ą','Ć','Ę','Ł','Ń','Ó','Ś','Ź','Ż'];
    $ascii = ['a','c','e','l','n','o','s','z','z','a','c','e','l','n','o','s','z','z'];
    $slug = str_replace($polish, $ascii, $slug);
    
    // Replace special chars and spaces with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    
    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');
    
    // Truncate to max length (at word boundary if possible)
    if (strlen($slug) > $maxLength) {
        $slug = substr($slug, 0, $maxLength);
        $lastHyphen = strrpos($slug, '-');
        if ($lastHyphen > $maxLength * 0.6) {
            $slug = substr($slug, 0, $lastHyphen);
        }
    }
    
    // Remove trailing hyphen after truncation
    $slug = rtrim($slug, '-');
    
    // Fallback if empty
    if (empty($slug)) {
        $slug = 'ai-image';
    }
    
    return $slug;
}

/**
 * Create image variant (thumbnail, icon, hero)
 */
function ai_images_create_variant(string $sourcePath, string $targetPath, int $maxWidth, int $maxHeight, int $jpegQuality = 85): bool
{
    if (!function_exists('imagecreatetruecolor')) {
        error_log('ai_images_create_variant: GD library not available');
        return false;
    }

    if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
        return false;
    }

    $imageInfo = @getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }

    $mimeType = $imageInfo['mime'];
    $source = null;

    if ($mimeType === 'image/jpeg') {
        $source = @imagecreatefromjpeg($sourcePath);
    } elseif ($mimeType === 'image/png') {
        $source = @imagecreatefrompng($sourcePath);
    } elseif ($mimeType === 'image/gif') {
        $source = @imagecreatefromgif($sourcePath);
    } elseif ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $source = @imagecreatefromwebp($sourcePath);
    }

    if (!$source) {
        return false;
    }

    $origWidth = imagesx($source);
    $origHeight = imagesy($source);
    $aspectRatio = $origWidth / $origHeight;
    $targetAspect = $maxWidth / $maxHeight;

    if ($aspectRatio > $targetAspect) {
        $newWidth = min($maxWidth, $origWidth);
        $newHeight = (int)($newWidth / $aspectRatio);
    } else {
        $newHeight = min($maxHeight, $origHeight);
        $newWidth = (int)($newHeight * $aspectRatio);
    }

    if ($newWidth < 1) $newWidth = 1;
    if ($newHeight < 1) $newHeight = 1;

    $resized = imagecreatetruecolor($newWidth, $newHeight);
    if (!$resized) {
        imagedestroy($source);
        return false;
    }

    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    $saved = @imagejpeg($resized, $targetPath, $jpegQuality);

    imagedestroy($resized);
    imagedestroy($source);

    if ($saved) {
        @chmod($targetPath, 0644);
    }

    return $saved;
}

/**
 * Map aspect ratio to DALL-E size
 */
function ai_images_get_dalle_size(string $aspect): string
{
    switch ($aspect) {
        case '16:9':
            return '1792x1024'; // Landscape
        case '9:16':
            return '1024x1792'; // Portrait
        case '21:9':
            return '1792x1024'; // Wide (closest match)
        case '1:1':
        default:
            return '1024x1024'; // Square
    }
}

/**
 * Generate image using OpenAI DALL-E API
 */
function ai_images_generate(array $spec): array
{
    try {
        $prompt = isset($spec['prompt']) ? trim((string)$spec['prompt']) : '';
        $style = isset($spec['style']) ? trim((string)$spec['style']) : '';
        $aspect = isset($spec['aspect']) ? trim((string)$spec['aspect']) : '1:1';
        $quality = isset($spec['quality']) ? trim((string)$spec['quality']) : 'standard';
        $notes = isset($spec['notes']) ? trim((string)$spec['notes']) : '';

        if ($prompt === '') {
            return ['ok' => false, 'error' => 'Image prompt is required.'];
        }

        $settings = ai_images_load_openai_settings();
        if (!$settings['enabled'] || empty($settings['api_key'])) {
            return ['ok' => false, 'error' => 'OpenAI is not enabled or configured.'];
        }

        // Build enhanced prompt
        $parts = [$prompt];
        if ($style !== '') {
            $styleMap = [
                'photorealistic' => 'photorealistic, high detail, professional photography',
                'illustration' => 'digital illustration, artistic style',
                '3d' => '3D render, CGI, realistic lighting',
                'anime' => 'anime style, Japanese animation',
                'watercolor' => 'watercolor painting, artistic',
                'minimalist' => 'minimalist design, clean, simple',
            ];
            $parts[] = $styleMap[$style] ?? $style;
        }
        if ($notes !== '') {
            $parts[] = $notes;
        }
        $finalPrompt = implode('. ', $parts);

        // Determine size from aspect ratio
        $size = ai_images_get_dalle_size($aspect);

        // Determine quality (standard or hd)
        $dalleQuality = ($quality === 'hd' || $quality === 'pro') ? 'hd' : 'standard';

        // Prepare API request
        $payload = [
            'model' => 'dall-e-3',
            'prompt' => $finalPrompt,
            'n' => 1,
            'size' => $size,
            'quality' => $dalleQuality,
            'response_format' => 'url',
        ];

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $settings['api_key'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 120, // DALL-E can take up to 60-90 seconds
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('ai_images_generate: cURL error - ' . $curlError);
            return ['ok' => false, 'error' => 'Failed to connect to OpenAI API.'];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? 'Unknown error';
            error_log('ai_images_generate: OpenAI error - ' . $errorMsg);
            return ['ok' => false, 'error' => 'DALL-E error: ' . substr($errorMsg, 0, 200)];
        }

        if (empty($data['data'][0]['url'])) {
            return ['ok' => false, 'error' => 'No image URL in response.'];
        }

        $imageUrl = $data['data'][0]['url'];
        $revisedPrompt = $data['data'][0]['revised_prompt'] ?? $finalPrompt;

        // Download image from URL
        $imageData = @file_get_contents($imageUrl);
        if ($imageData === false) {
            return ['ok' => false, 'error' => 'Failed to download generated image.'];
        }

        // Create output directory if needed
        if (!is_dir(AI_IMAGES_OUTPUT_DIR)) {
            if (!@mkdir(AI_IMAGES_OUTPUT_DIR, 0775, true)) {
                return ['ok' => false, 'error' => 'Failed to create AI images directory.'];
            }
        }

        // Save image
        $ext = ai_images_guess_extension($imageData);
        $timestamp = date('Ymd-His');
        $random = bin2hex(random_bytes(2)); // Shorter random for uniqueness
        
        // Use SEO name if provided, otherwise generate from prompt
        $seoName = isset($spec['seo_name']) ? trim((string)$spec['seo_name']) : '';
        if ($seoName === '') {
            // Extract first ~50 chars of prompt for SEO name
            $seoName = ai_images_generate_seo_slug($prompt, 50);
        } else {
            $seoName = ai_images_generate_seo_slug($seoName, 50);
        }
        
        $filename = $seoName . '-' . $timestamp . '-' . $random . $ext;
        $filePath = AI_IMAGES_OUTPUT_DIR . '/' . $filename;
        $webPath = AI_IMAGES_WEB_PREFIX . '/' . $filename;

        $written = @file_put_contents($filePath, $imageData, LOCK_EX);
        if ($written === false) {
            return ['ok' => false, 'error' => 'Failed to save image file.'];
        }
        @chmod($filePath, 0644);

        // Create variants
        $variants = [];
        $baseNameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        // Thumbnail
        $thumbFilename = $baseNameWithoutExt . '_thumb.jpg';
        $thumbPath = AI_IMAGES_OUTPUT_DIR . '/' . $thumbFilename;
        if (ai_images_create_variant($filePath, $thumbPath, 320, 320, 85)) {
            $variants[] = [
                'type' => 'thumbnail',
                'url' => AI_IMAGES_WEB_PREFIX . '/' . $thumbFilename,
            ];
        }

        return [
            'ok' => true,
            'path' => $webPath,
            'file' => $filePath,
            'prompt' => $finalPrompt,
            'revised_prompt' => $revisedPrompt,
            'model' => 'dall-e-3',
            'size' => $size,
            'quality' => $dalleQuality,
            'variants' => $variants,
        ];

    } catch (Throwable $e) {
        error_log('ai_images_generate error: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Unexpected error while generating image.'];
    }
}

/**
 * Save AI-generated image to Media Library
 * Copies image from ai-images folder to media folder and adds to database
 *
 * @param string $aiImagePath Web path like /uploads/ai-images/ai-xxx.png
 * @param string $title Optional title for the media
 * @param string $altText Optional alt text
 * @return array Result with ok, id, url, error keys
 */
function ai_images_save_to_gallery(string $aiImagePath, string $title = '', string $altText = ''): array
{
    // Validate path
    $filename = basename($aiImagePath);
    $sourcePath = CMS_ROOT . '/uploads/ai-images/' . $filename;

    if (!file_exists($sourcePath)) {
        return ['ok' => false, 'error' => 'Source image not found.'];
    }

    // Get file info
    $mimeType = @mime_content_type($sourcePath);
    if (!$mimeType || strpos($mimeType, 'image/') !== 0) {
        return ['ok' => false, 'error' => 'Invalid image file.'];
    }

    $fileSize = filesize($sourcePath);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    // Use SEO title if provided, otherwise extract from original filename
    $seoSlug = '';
    if (!empty($title)) {
        $seoSlug = ai_images_generate_seo_slug($title, 50);
    } else {
        // Try to extract meaningful part from AI filename (before timestamp)
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        if (preg_match('/^(.+)-\d{8}-\d{6}/', $baseName, $matches)) {
            $seoSlug = $matches[1];
        }
    }
    
    if (empty($seoSlug)) {
        $seoSlug = 'ai-image';
    }
    
    $random = bin2hex(random_bytes(2));
    $newFilename = $seoSlug . '-' . date('Ymd') . '-' . $random . '.' . $ext;

    // Target paths
    $mediaDir = CMS_ROOT . '/uploads/media/';
    $thumbDir = CMS_ROOT . '/uploads/media/thumbs/';
    $targetPath = $mediaDir . $newFilename;

    // Create directories if needed
    if (!is_dir($mediaDir)) {
        @mkdir($mediaDir, 0775, true);
    }
    if (!is_dir($thumbDir)) {
        @mkdir($thumbDir, 0775, true);
    }

    // Copy file to media folder
    if (!copy($sourcePath, $targetPath)) {
        return ['ok' => false, 'error' => 'Failed to copy image to media library.'];
    }
    @chmod($targetPath, 0644);

    // Generate thumbnail
    $thumbFilename = null;
    $imageInfo = @getimagesize($targetPath);
    if ($imageInfo) {
        $thumbFilename = pathinfo($newFilename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbPath = $thumbDir . $thumbFilename;

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];

        $maxWidth = 300;
        $maxHeight = 200;
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $sourceImage = null;
        if ($type === IMAGETYPE_JPEG) {
            $sourceImage = @imagecreatefromjpeg($targetPath);
        } elseif ($type === IMAGETYPE_PNG) {
            $sourceImage = @imagecreatefrompng($targetPath);
        } elseif ($type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $sourceImage = @imagecreatefromwebp($targetPath);
        }

        if ($sourceImage) {
            $thumbImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($thumbImage, $thumbPath, 85);
            imagedestroy($sourceImage);
            imagedestroy($thumbImage);
        }
    }

    // Add to database
    require_once CMS_ROOT . '/db.php';
    require_once CMS_ROOT . '/core/models/mediamodel.php';

    try {
        $db = db();
        $model = new MediaModel($db);

        $mediaId = $model->create([
            'filename' => $newFilename,
            'original_name' => $filename,
            'mime_type' => $mimeType,
            'size' => $fileSize,
            'path' => 'uploads/media/' . $newFilename,
            'title' => $title ?: 'AI Generated Image',
            'alt_text' => $altText ?: '',
            'description' => 'Generated by AI Image Generator (DALL-E)',
            'folder' => 'ai-generated'
        ]);

        return [
            'ok' => true,
            'id' => $mediaId,
            'filename' => $newFilename,
            'url' => '/uploads/media/' . $newFilename,
            'thumb' => $thumbFilename ? '/uploads/media/thumbs/' . $thumbFilename : null
        ];

    } catch (Throwable $e) {
        // Clean up on error
        @unlink($targetPath);
        error_log('ai_images_save_to_gallery error: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Database error while saving to gallery.'];
    }
}
