<?php
declare(strict_types=1);

/**
 * ShopAIImages — AI-powered image processing for e-commerce
 * Uses Hugging Face Inference API for:
 * - Background removal (briaai/RMBG-2.0)
 * - Auto ALT text generation (vision model)
 * - Image enhancement / upscaling (image-to-image)
 * - AI product image generation (text-to-image)
 */

if (!defined('CMS_ROOT')) {
    http_response_code(403);
    exit;
}

require_once CMS_ROOT . '/core/ai_hf.php';

class ShopAIImages
{
    /** Output directory for processed images */
    private const OUTPUT_DIR = '/uploads/shop-ai-images';

    /** Recommended models per task */
    private const MODELS = [
        'background_removal' => 'briaai/RMBG-2.0',
        'vision'             => 'Salesforce/blip-image-captioning-large',
        'image_gen'          => 'stabilityai/stable-diffusion-xl-base-1.0',
        'upscale'            => 'stabilityai/stable-diffusion-xl-refiner-1.0',
    ];

    // ─── BACKGROUND REMOVAL ───

    /**
     * Remove background from a product image.
     *
     * @param string $imagePath Absolute path or URL to image
     * @param int|null $productId Optional product ID for auto-save
     * @return array ['ok' => bool, 'path' => string (web path), 'absolute_path' => string, 'error' => string]
     */
    public static function removeBackground(string $imagePath, ?int $productId = null): array
    {
        $imageData = self::loadImageData($imagePath);
        if ($imageData === null) {
            return ['ok' => false, 'error' => 'Failed to load image: ' . $imagePath];
        }

        $settings = ai_hf_load_settings();
        if (!$settings['enabled'] || empty($settings['api_token'])) {
            return ['ok' => false, 'error' => 'Hugging Face is not enabled or configured.'];
        }

        $model = self::MODELS['background_removal'];
        $url = 'https://router.huggingface.co/hf-inference/models/' . $model;

        $result = self::httpPostBinary($url, $imageData, $settings['api_token'], 60);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'Background removal failed'];
        }

        // Response is raw PNG image data
        $responseData = $result['body'];
        if (empty($responseData) || strlen($responseData) < 100) {
            return ['ok' => false, 'error' => 'Empty or invalid response from background removal model'];
        }

        // Verify it's actually image data (PNG starts with \x89PNG)
        if (substr($responseData, 0, 4) !== "\x89PNG" && substr($responseData, 0, 2) !== "\xff\xd8") {
            // Might be JSON error
            $jsonCheck = @json_decode($responseData, true);
            if (is_array($jsonCheck) && isset($jsonCheck['error'])) {
                return ['ok' => false, 'error' => 'HF API error: ' . ($jsonCheck['error'] ?? 'Unknown')];
            }
            return ['ok' => false, 'error' => 'Response is not valid image data'];
        }

        // Save the result
        $ext = (substr($responseData, 0, 4) === "\x89PNG") ? 'png' : 'jpg';
        $filename = 'nobg_' . ($productId ?? 'img') . '_' . time() . '.' . $ext;
        $savePath = self::ensureOutputDir() . '/' . $filename;
        $webPath = self::OUTPUT_DIR . '/' . $filename;

        if (file_put_contents($savePath, $responseData) === false) {
            return ['ok' => false, 'error' => 'Failed to save processed image'];
        }

        return [
            'ok'            => true,
            'path'          => $webPath,
            'absolute_path' => $savePath,
            'filename'      => $filename,
            'size'          => strlen($responseData),
        ];
    }

    // ─── AUTO ALT TEXT ───

    /**
     * Generate SEO-optimized ALT text for a product image.
     *
     * @param string $imagePath Absolute path or URL to image
     * @param string $productName Optional product name for context
     * @param string $language Language for the alt text
     * @return array ['ok' => bool, 'alt' => string, 'error' => string]
     */
    public static function generateAltText(string $imagePath, string $productName = '', string $language = 'en'): array
    {
        $imageData = self::loadImageData($imagePath);
        if ($imageData === null) {
            return ['ok' => false, 'error' => 'Failed to load image'];
        }

        $settings = ai_hf_load_settings();
        if (!$settings['enabled'] || empty($settings['api_token'])) {
            return ['ok' => false, 'error' => 'Hugging Face is not enabled or configured.'];
        }

        // Use vision model for captioning
        $hfConfig = ai_hf_config_load();
        $visionModel = '';
        if (isset($hfConfig['models']['vision']) && $hfConfig['models']['vision'] !== '') {
            $visionModel = $hfConfig['models']['vision'];
        } else {
            $visionModel = self::MODELS['vision'];
        }

        $url = 'https://router.huggingface.co/hf-inference/models/' . $visionModel;

        $result = self::httpPostBinary($url, $imageData, $settings['api_token'], 30);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'ALT text generation failed'];
        }

        $responseData = @json_decode($result['body'], true);
        $caption = null;

        if (is_array($responseData)) {
            // Format: [{"generated_text": "..."}]
            if (isset($responseData[0]['generated_text'])) {
                $caption = $responseData[0]['generated_text'];
            } elseif (isset($responseData['generated_text'])) {
                $caption = $responseData['generated_text'];
            } elseif (isset($responseData[0]['caption'])) {
                $caption = $responseData[0]['caption'];
            }
        }

        if ($caption === null || trim($caption) === '') {
            return ['ok' => false, 'error' => 'No caption generated by the vision model'];
        }

        // Clean up and optimize for SEO
        $alt = trim($caption);
        $alt = str_replace(["\r\n", "\r", "\n"], ' ', $alt);
        $alt = preg_replace('/\s+/', ' ', $alt);

        // Enhance with product name if available
        if ($productName !== '' && stripos($alt, $productName) === false) {
            $alt = $productName . ' - ' . $alt;
        }

        // Cap length for SEO best practices
        if (mb_strlen($alt) > 125) {
            $alt = mb_substr($alt, 0, 122) . '...';
        }

        return [
            'ok'  => true,
            'alt'  => $alt,
            'raw_caption' => trim($caption),
        ];
    }

    /**
     * Bulk generate ALT text for all products missing image alt.
     *
     * @return array ['ok' => bool, 'generated' => int, 'failed' => int, 'results' => array]
     */
    public static function bulkGenerateAltText(): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $pdo = db();

        // Get products with images but no meta description (we'll store alt in a separate approach)
        $stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != '' AND status = 'active' ORDER BY id ASC LIMIT 50");
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        $generated = 0;
        $failed = 0;

        foreach ($products as $p) {
            $result = self::generateAltText($p['image'], $p['name']);
            if ($result['ok']) {
                $generated++;
                $results[] = [
                    'id'   => (int)$p['id'],
                    'name' => $p['name'],
                    'alt'  => $result['alt'],
                    'status' => 'ok',
                ];
            } else {
                $failed++;
                $results[] = [
                    'id'     => (int)$p['id'],
                    'name'   => $p['name'],
                    'error'  => $result['error'] ?? 'Unknown',
                    'status' => 'error',
                ];
            }
            usleep(500000); // 0.5s rate limit
        }

        return [
            'ok'        => true,
            'generated' => $generated,
            'failed'    => $failed,
            'results'   => $results,
        ];
    }

    // ─── IMAGE ENHANCEMENT ───

    /**
     * Enhance/refine a product image using image-to-image model.
     *
     * @param string $imagePath Absolute path or URL
     * @param string $prompt Enhancement prompt (e.g. "high quality product photo, white background, professional lighting")
     * @param int|null $productId Optional product ID
     * @return array ['ok' => bool, 'path' => string, 'error' => string]
     */
    public static function enhanceImage(string $imagePath, string $prompt = '', ?int $productId = null): array
    {
        $imageData = self::loadImageData($imagePath);
        if ($imageData === null) {
            return ['ok' => false, 'error' => 'Failed to load image'];
        }

        $settings = ai_hf_load_settings();
        if (!$settings['enabled'] || empty($settings['api_token'])) {
            return ['ok' => false, 'error' => 'Hugging Face is not enabled or configured.'];
        }

        if ($prompt === '') {
            $prompt = 'high quality product photography, professional studio lighting, clean white background, sharp focus, commercial quality';
        }

        $model = self::MODELS['upscale'];

        // Image-to-image uses multipart form
        $boundary = 'HF' . md5(uniqid());
        $body = '';

        // Add image part
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"inputs\"; filename=\"image.jpg\"\r\n";
        $body .= "Content-Type: application/octet-stream\r\n\r\n";
        $body .= $imageData . "\r\n";

        // Add parameters as JSON part
        $params = json_encode([
            'prompt'   => $prompt,
            'strength' => 0.35,  // Low strength = keep most of original
            'guidance_scale' => 7.5,
        ]);
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"parameters\"\r\n";
        $body .= "Content-Type: application/json\r\n\r\n";
        $body .= $params . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $url = 'https://router.huggingface.co/hf-inference/models/' . $model;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $settings['api_token'],
            'Content-Type: multipart/form-data; boundary=' . $boundary,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpStatus < 200 || $httpStatus >= 300) {
            $errMsg = 'Image enhancement failed (HTTP ' . $httpStatus . ')';
            if ($response) {
                $errJson = @json_decode($response, true);
                if (is_array($errJson) && isset($errJson['error'])) {
                    $errMsg .= ': ' . $errJson['error'];
                }
            }
            return ['ok' => false, 'error' => $errMsg];
        }

        // Verify response is image data
        if (strlen($response) < 100) {
            return ['ok' => false, 'error' => 'Response too small to be valid image'];
        }

        $ext = (substr($response, 0, 4) === "\x89PNG") ? 'png' : 'jpg';
        $filename = 'enhanced_' . ($productId ?? 'img') . '_' . time() . '.' . $ext;
        $savePath = self::ensureOutputDir() . '/' . $filename;
        $webPath = self::OUTPUT_DIR . '/' . $filename;

        if (file_put_contents($savePath, $response) === false) {
            return ['ok' => false, 'error' => 'Failed to save enhanced image'];
        }

        return [
            'ok'            => true,
            'path'          => $webPath,
            'absolute_path' => $savePath,
            'filename'      => $filename,
            'size'          => strlen($response),
        ];
    }

    // ─── AI PRODUCT IMAGE GENERATION ───

    /**
     * Generate a product image from text prompt.
     *
     * @param string $prompt Description of desired image
     * @param int|null $productId Optional product ID
     * @return array ['ok' => bool, 'path' => string, 'error' => string]
     */
    public static function generateProductImage(string $prompt, ?int $productId = null): array
    {
        $settings = ai_hf_load_settings();
        if (!$settings['enabled'] || empty($settings['api_token'])) {
            return ['ok' => false, 'error' => 'Hugging Face is not enabled or configured.'];
        }

        $hfConfig = ai_hf_config_load();
        $imageModel = '';
        if (isset($hfConfig['models']['image']) && $hfConfig['models']['image'] !== '') {
            $imageModel = $hfConfig['models']['image'];
        } else {
            $imageModel = self::MODELS['image_gen'];
        }

        // Enhance prompt for product photography
        $enhancedPrompt = $prompt . ', product photography, professional studio lighting, high resolution, commercial quality, clean background';

        $payload = json_encode([
            'inputs' => $enhancedPrompt,
            'parameters' => [
                'negative_prompt' => 'blurry, low quality, distorted, deformed, watermark, text overlay, amateur',
                'width'           => 1024,
                'height'          => 1024,
                'num_inference_steps' => 30,
                'guidance_scale'  => 7.5,
            ],
        ]);

        $url = 'https://router.huggingface.co/hf-inference/models/' . $imageModel;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $settings['api_token'],
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpStatus < 200 || $httpStatus >= 300) {
            $errMsg = 'Image generation failed (HTTP ' . $httpStatus . ')';
            if ($response) {
                $errJson = @json_decode($response, true);
                if (is_array($errJson) && isset($errJson['error'])) {
                    $errMsg .= ': ' . $errJson['error'];
                }
            }
            return ['ok' => false, 'error' => $errMsg];
        }

        if (strlen($response) < 100) {
            return ['ok' => false, 'error' => 'Response too small to be valid image'];
        }

        $ext = (substr($response, 0, 4) === "\x89PNG") ? 'png' : 'jpg';
        $filename = 'aigen_' . ($productId ?? 'img') . '_' . time() . '.' . $ext;
        $savePath = self::ensureOutputDir() . '/' . $filename;
        $webPath = self::OUTPUT_DIR . '/' . $filename;

        if (file_put_contents($savePath, $response) === false) {
            return ['ok' => false, 'error' => 'Failed to save generated image'];
        }

        return [
            'ok'            => true,
            'path'          => $webPath,
            'absolute_path' => $savePath,
            'filename'      => $filename,
            'size'          => strlen($response),
        ];
    }

    // ─── PRODUCT INTEGRATION ───

    /**
     * Process all image AI tasks for a product at once.
     * Useful for "optimize all" button.
     *
     * @param int $productId Product ID
     * @param array $tasks Tasks to run: ['remove_bg', 'alt_text', 'enhance']
     * @return array Results keyed by task name
     */
    public static function processProduct(int $productId, array $tasks = ['alt_text']): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct($productId);
        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        $imageUrl = $product['image'] ?? '';
        if ($imageUrl === '') {
            return ['ok' => false, 'error' => 'Product has no image'];
        }

        $results = ['ok' => true, 'tasks' => []];

        if (in_array('remove_bg', $tasks)) {
            $results['tasks']['remove_bg'] = self::removeBackground($imageUrl, $productId);
        }

        if (in_array('alt_text', $tasks)) {
            $results['tasks']['alt_text'] = self::generateAltText($imageUrl, $product['name'] ?? '');
        }

        if (in_array('enhance', $tasks)) {
            $results['tasks']['enhance'] = self::enhanceImage($imageUrl, '', $productId);
        }

        return $results;
    }

    // ─── PRIVATE HELPERS ───

    /**
     * Load image data from URL or file path.
     *
     * @param string $source URL or absolute/relative file path
     * @return string|null Binary image data or null on failure
     */
    private static function loadImageData(string $source): ?string
    {
        $source = trim($source);
        if ($source === '') {
            return null;
        }

        // URL
        if (strpos($source, 'http://') === 0 || strpos($source, 'https://') === 0) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'JessieCMS/1.0',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $data = @file_get_contents($source, false, $ctx);
            return ($data !== false && strlen($data) > 0) ? $data : null;
        }

        // Relative web path → absolute
        if (strpos($source, '/') === 0 && strpos($source, CMS_ROOT) !== 0) {
            $source = CMS_ROOT . $source;
        }

        // Absolute file path
        if (file_exists($source) && is_readable($source)) {
            $data = @file_get_contents($source);
            return ($data !== false && strlen($data) > 0) ? $data : null;
        }

        return null;
    }

    /**
     * Send binary data to HF API endpoint.
     *
     * @param string $url API endpoint URL
     * @param string $data Binary data
     * @param string $token API token
     * @param int $timeout Timeout in seconds
     * @return array ['ok' => bool, 'body' => string|null, 'status' => int, 'error' => string|null]
     */
    private static function httpPostBinary(string $url, string $data, string $token, int $timeout = 60): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'body' => null, 'status' => 0, 'error' => 'Failed to init cURL'];
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/octet-stream',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $body = curl_exec($ch);
        $httpStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return ['ok' => false, 'body' => null, 'status' => $httpStatus, 'error' => $curlError ?: 'cURL failed'];
        }

        $ok = ($httpStatus >= 200 && $httpStatus < 300);
        $error = null;

        if (!$ok) {
            $error = 'HTTP ' . $httpStatus;
            $jsonCheck = @json_decode($body, true);
            if (is_array($jsonCheck)) {
                if (isset($jsonCheck['error'])) {
                    $error .= ': ' . (is_string($jsonCheck['error']) ? $jsonCheck['error'] : json_encode($jsonCheck['error']));
                }
                if (isset($jsonCheck['estimated_time'])) {
                    $error .= ' (model loading, estimated: ' . round((float)$jsonCheck['estimated_time']) . 's — try again in a moment)';
                }
            }
        }

        return ['ok' => $ok, 'body' => $body, 'status' => $httpStatus, 'error' => $error];
    }

    /**
     * Ensure output directory exists and return absolute path.
     *
     * @return string Absolute path to output directory
     */
    private static function ensureOutputDir(): string
    {
        $dir = CMS_ROOT . self::OUTPUT_DIR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
