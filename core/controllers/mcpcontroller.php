<?php
class MCPController {
    public function generateImage() {
        try {
            // Create a simple image
            $width = 200;
            $height = 100;
            $image = imagecreatetruecolor($width, $height);
            
            // Set colors
            $bgColor = imagecolorallocate($image, 255, 255, 255);
            $textColor = imagecolorallocate($image, 0, 0, 0);
            
            // Fill background
            imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
            
            // Add text
            imagestring($image, 5, 50, 40, 'MCP Image', $textColor);
            
            // Capture output
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            
            // Clean up
            imagedestroy($image);
            
            return $imageData;
            
        } catch (Exception $e) {
            error_log('MCP Image Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate image: ' . $e->getMessage());
        }
    }
}
