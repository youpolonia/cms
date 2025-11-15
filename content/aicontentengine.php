<?php
class AIContentEngine
{
    /**
     * Generate structured content for a given topic
     * @param string $topic The topic to generate content about
     * @return array Structured content with keys: title, summary, body, tags
     * @throws Exception If content generation fails
     */
    public static function generateContent(string $topic): array
    {
        $prompt = "Generate structured content about: $topic\n\n" .
                  "Return JSON with these fields:\n" .
                  "- title (string)\n" .
                  "- summary (short lead paragraph, string)\n" .
                  "- body (HTML formatted content, string)\n" .
                  "- tags (array of relevant strings)\n\n" .
                  "Ensure the content is well-researched and accurate.";

        $jsonResponse = AIClient::askStructured($prompt);
        $result = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode AI response: " . json_last_error_msg());
        }

        // Validate required fields
        $required = ['title', 'summary', 'body', 'tags'];
        foreach ($required as $field) {
            if (!isset($result[$field])) {
                throw new Exception("AI response missing required field: $field");
            }
        }

        return $result;
    }

    /**
     * Save generated content to disk as JSON
     * @param array $data Content data (must require_once title, summary, body, tags)
     * @return string Filename of saved content
     * @throws Exception If validation fails or file cannot be written
     */
    public static function saveContent(array $data): string
    {
        // Validate required fields
        $required = ['title', 'summary', 'body', 'tags'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Create content directory if needed
        $dir = __DIR__ . '/generated';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception("Failed to create content directory");
        }

        // Generate safe filename
        $filename = preg_replace('/[^a-z0-9\-]/', '-', strtolower($data['title']));
        $filename = substr($filename, 0, 50) . '-' . time() . '.json';
        $path = "$dir/$filename";

        // Save as JSON
        $json = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents($path, $json) === false) {
            throw new Exception("Failed to save content file");
        }

        return $filename;
    }
}
