<?php

class MediaAIAssistant {
    /**
     * Generates AI-powered description for a media file
     * @param string $filePath Path to the media file
     * @return array Contains 'title', 'description', and 'tags'
     */
    public static function generateDescription(string $filePath): array {
        $prompt = "Generate a title, description, and tags for this media file: $filePath. " .
                  "Return JSON with keys: title (string), description (string), tags (array).";
        
        $response = AIClient::ask($prompt);
        $data = json_decode($response, true);
        
        return [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'tags' => $data['tags'] ?? []
        ];
    }
}
