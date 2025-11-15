<?php
/**
 * MediaAISearch - Provides semantic search for media files using AI
 */
class MediaAISearch {
    /**
     * Perform semantic search on media files
     * @param string $query Search query
     * @return array Array of matching files with scores
     */
    public static function semanticSearch(string $query): array {
        // Get all media metadata from registry
        $allMedia = MediaRegistry::getAll();
        
        // Prepare prompt for AI
        $prompt = "Find media files relevant to: '$query'\n\n";
        $prompt .= "Available files with metadata:\n";
        
        foreach ($allMedia as $file) {
            $prompt .= "- {$file['filename']}: ";
            $prompt .= "Title: {$file['ai_title']}, ";
            $prompt .= "Description: {$file['ai_description']}, ";
            $prompt .= "Tags: " . implode(', ', explode(',', $file['ai_tags'])) . "\n";
        }
        
        $prompt .= "\nReturn JSON array of relevant filenames with scores (0-1)";
        
        // Get AI response
        $response = AIClient::ask($prompt);
        
        // Parse and return results
        return json_decode($response, true) ?? [];
    }
}
