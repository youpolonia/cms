<?php
/**
 * Handles AI-powered media processing for the gallery
 */

namespace AI\MediaGallery;

use Exception;
use AI\AIManager;
use Database\DatabaseConnection;

class MediaProcessor {
    private $db;
    private $aiManager;

    public function __construct(DatabaseConnection $db, AIManager $aiManager) {
        $this->db = $db;
        $this->aiManager = $aiManager;
    }

    /**
     * Processes a media file through AI pipelines
     */
    public function processMedia(int $mediaId, string $filePath): array {
        try {
            // Get file info
            $fileInfo = $this->analyzeFile($filePath);
            
            // Process through AI pipelines
            $metadata = [
                'ai_tags' => $this->generateAITags($filePath),
                'color_palette' => $this->extractColorPalette($filePath),
                'object_detection' => $this->detectObjects($filePath)
            ];

            // Save metadata
            $this->saveMetadata($mediaId, $metadata);

            return [
                'success' => true,
                'metadata' => $metadata
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function analyzeFile(string $filePath): array {
        // Basic file analysis (size, dimensions, etc.)
        $size = filesize($filePath);
        $type = mime_content_type($filePath);
        
        // For images, get dimensions
        $dimensions = [];
        if (strpos($type, 'image/') === 0) {
            list($width, $height) = getimagesize($filePath);
            $dimensions = ['width' => $width, 'height' => $height];
        }

        return [
            'size' => $size,
            'type' => $type,
            'dimensions' => $dimensions
        ];
    }

    private function generateAITags(string $filePath): array {
        // Use AI to generate descriptive tags
        $prompt = "Describe this image in detail and provide relevant tags";
        $response = $this->aiManager->analyzeImage($filePath, $prompt);
        
        return $response['tags'] ?? [];
    }

    private function extractColorPalette(string $filePath): array {
        // Use AI to extract dominant colors
        $response = $this->aiManager->analyzeImage($filePath, "Extract dominant colors in hex format");
        
        return $response['colors'] ?? [];
    }

    private function detectObjects(string $filePath): array {
        // Use AI to detect objects in the image
        $response = $this->aiManager->analyzeImage($filePath, "Detect and label all objects");
        
        return $response['objects'] ?? [];
    }

    private function saveMetadata(int $mediaId, array $metadata): bool {
        $stmt = $this->db->prepare("
            INSERT INTO media_metadata 
            (media_id, ai_tags, color_palette, object_detection) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            ai_tags = VALUES(ai_tags),
            color_palette = VALUES(color_palette),
            object_detection = VALUES(object_detection)
        ");

        return $stmt->execute([
            $mediaId,
            json_encode($metadata['ai_tags']),
            json_encode($metadata['color_palette']),
            json_encode($metadata['object_detection'])
        ]);
    }
}
