<?php
/**
 * ImageGenerator - Handles AI-based image generation
 */
class ImageGenerator {
    /**
     * Generate and save an image from text prompt
     * @param string $prompt The text prompt for image generation
     * @return string Path to the generated image file
     */
    public static function generateImage(string $prompt): string {
        // Get base64 image data from AI client
        $base64Image = AIClient::askImage($prompt);
        
        // Validate and decode the base64 string
        if (!self::isValidBase64Image($base64Image)) {
            throw new InvalidArgumentException('Invalid or malicious image data');
        }
        $imageData = base64_decode($base64Image);

        // Ensure directory exists
        $saveDir = __DIR__ . '/../../media/generated/';
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid('ai_') . '.png';
        $filepath = $saveDir . $filename;

        // Save the image
        if (file_put_contents($filepath, $imageData) === false) {
            throw new Exception('Failed to save generated image');
        }

        return $filepath;
    }

    /**
     * Validate base64 image data
     * @param string $data Base64 encoded image
     * @return bool True if valid and safe
     */
    private static function isValidBase64Image(string $data): bool {
        // Check basic base64 format
        if (!preg_match('/^[a-zA-Z0-9\/+]+={0,2}$/', $data)) {
            return false;
        }

        // Check reasonable size (max 10MB when decoded)
        $decodedLength = (int)(strlen($data) * 3 / 4);
        if ($decodedLength > 10 * 1024 * 1024) {
            return false;
        }

        // Verify it's actually an image
        $decoded = base64_decode($data);
        if ($decoded === false) {
            return false;
        }

        // Check image header magic bytes
        $magicBytes = substr($decoded, 0, 8);
        return strpos($magicBytes, "\x89PNG\x0D\x0A\x1A\x0A") === 0 ||  // PNG
               strpos($magicBytes, "\xFF\xD8\xFF") === 0;               // JPEG
    }
}
